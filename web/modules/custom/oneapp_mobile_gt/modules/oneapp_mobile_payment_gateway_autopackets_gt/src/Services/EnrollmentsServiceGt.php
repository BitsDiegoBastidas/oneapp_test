<?php

namespace Drupal\oneapp_mobile_payment_gateway_autopackets_gt\Services;

use Drupal\oneapp_mobile_payment_gateway_autopackets\Services\EnrollmentsService;
use Drupal\oneapp\Exception\UnauthorizedHttpException;

/**
 * Class EnrollmentsServiceGt.
 */
class EnrollmentsServiceGt extends EnrollmentsService {

  /**
   * get Offer for Id
   */
  public function getOffer($id, $offer_id) {
    $available_service = \Drupal::service('oneapp_mobile_upselling.v2_0.available_offers_rest_logic');
    $offers = $available_service->getOffers($id);
    if (count($offers->products) > 0) {
      foreach ($offers->products as $offer) {
        if ($offer->packageId == $offer_id) {
          return $offer;
        }
      }
    }
    return [];
  }

  /**
   * @param $type
   * @param $value
   * @return array
   */
  public function getDataForType($type, $value) {
    try {
      $data_user = [];
      $query = \Drupal::database()
        ->select('oneapp_payment_gateway_autopackets')
        ->fields('oneapp_payment_gateway_autopackets')
        ->condition($type, $value)
        ->range(0, 1)
        ->orderBy('created', 'DESC');
      $data = $query->execute();
      $results = $data->fetchAll(\PDO::FETCH_OBJ);
      if (count($results) > 0) {
        $data_user = $results[0];
        unset($data_user->id);
        unset($data_user->additionalData);
      }
      else {

        $billing_form_autopackets = (object) \Drupal::config("oneapp.payment_gateway.mobile_autopackets.config")->get("billing_form");
        $data_user["accountNumber"] = "";
        $data_user["accountId"] = "";
        $data_user["nit"] = !empty($billing_form_autopackets->nit["value"]) ?
          $billing_form_autopackets->nit["value"] : "CF";
        $data_user["address"]= !empty($billing_form_autopackets->address["value"]) ?
          $billing_form_autopackets->address["value"] : "Ciudad";
        $data_user["email"] = $value;
        $data_user["enrollmentId"] = "";
        $data_user["offerId"] = "";
        $data_user["offerName"] = "";
        $data_user["offerAmount"] = "";
        $data_user["offerDuration"] = "";
        $data_user["name"] = !empty($billing_form_autopackets->fullname["value"]) ?
          $billing_form_autopackets->fullname["value"] : "Consumidor Final";

      }

      return $data_user;
    }
    catch (\Exception $e) {
      return [];
    }
  }

  /**
   * get Subscription Data
   */
  public function getSubscriptionData($id) {
    $offer = $this->getOffer($id, $this->params["offerId"]);
    if (strtolower($offer->recurrentPeriod) == "horas") {
      $hours = $offer->validityNumber;
    }
    else {
      $hours = $offer->validityType = "Dias" ? $offer->validityNumber * 24 : $offer->validityNumber;
    }
    $this->params['subscription'] = [
      'name' => isset($offer->name) ? $offer->name : '',
      'amount' => isset($offer->creditPackageCode) ? (string) $offer->creditPackageCode : '',
      'duration' => isset($hours) && $hours > 0 ? $this->getDurationRenewOffer($hours) : '',
      'productReference' => isset($offer->creditPackageCategory) ?
        (string) $offer->creditPackageCategory : (string) $this->params["offerId"],
      'lastOrderTimeStamp' => str_replace(" ", "T", date_format(date_create(), 'Y-m-d H:i:sP')),
    ];
  }

  /**
   * get Subscription Data of additional data
   */
  public function getSubscriptionDataOfAdditionalData($id, $additional_data) {
    if (strtolower($additional_data["validityType"]) == "horas") {
      $hours = $additional_data["validityNumber"];
    }
    else {
      $hours = $additional_data["validityType"] ? $additional_data["validityNumber"] * 24 : $additional_data["validityNumber"];
    }
    $this->params['subscription'] = [
      'name' => $additional_data["name"],
      'amount' => (string) $additional_data["cost"],
      'duration' => $this->getDurationRenewOffer($hours),
      'productReference' => isset($additional_data["creditPackageCategory"]) ?
        (string) $additional_data["creditPackageCategory"] : (string) $additional_data["packageId"],
      'lastOrderTimeStamp' => str_replace(" ", "T", date_format(date_create(), 'Y-m-d H:i:sP')),
    ];
    $this->params['billingData'] = $additional_data["billingData"];
  }

  /**
   * get Token card of database
   */
  public function getTokenizedCardOfDatabase($additional_data) {
    if (empty($this->params["tokenizedCardId"])) {
      if (isset($additional_data["paymentTokenId"])) {
        $this->params['tokenizedCardId'] = isset($this->params['tokenizedCardId']) ?
          $this->params['tokenizedCardId'] : $additional_data["paymentTokenId"];
      }
      if (isset($additional_data["numberCard"])) {
        $id = $this->tokenAuthorization->getId();
        $this->params['tokenizedCardId'] = isset($this->params['tokenizedCardId']) ?
          $this->params['tokenizedCardId'] : $this->getPaymentTokenId($id, $additional_data["numberCard"]);
      }
    }
    if (isset($additional_data["packageId"])) {
      $this->params['offerId'] = $additional_data["packageId"];
    }
  }

  /**
   * get Offer for Id with format
   */
  public function getOfferWithFormat($id, $offer_id) {
    $oneapp_utils = \Drupal::service('oneapp.utils');
    $offer = $this->getOffer($id, $offer_id);
    $validity['value'] = isset($offer->validityNumber) ? $offer->validityNumber : $offer->durationTime;
    $validity['formattedValue'] = isset($offer->validityNumber) ? $offer->validityNumber . ' ' . $offer->validityType :  '';
    $date_formatter = \Drupal::service('date.formatter');
    $date = new \DateTime();
    $expiration_date = $oneapp_utils->formatDateRegressiveWithDuration($date->format('Y-m-d H:i:s'), $validity['value'], false);
    $data = [
      'offerId' => isset($offer->packageId) ? $offer->packageId: '',
      'offerName' => isset($offer->name) ? $offer->name : '',
      'description' => isset($offer->description) ? $offer->description : '',
      'categoryName' => $offer->category,
      'validity' => $validity['formattedValue'],
      'amount' => isset($offer->acquisitionMethods[0]->priceList[0]->ammount) ?
        $oneapp_utils->formatCurrency($offer->acquisitionMethods[0]->priceList[0]->ammount, TRUE) :
        $oneapp_utils->formatCurrency($offer->cost, TRUE),
      'nextPayment' => $date_formatter->format(strtotime($expiration_date["value"]), $this->config["configs"]["dates"]["expirationDate"]),
      'frequency' => $validity['formattedValue'],
    ];
    return $data;
  }

    /**
   * Get the payment enrollments by params.
   */
  public function getEnrollmentsByParams($id) {

    $account_number = $this->utilsPayment->getAccountNumberForPaymentGatewayFromToken('mobile', $id);
    $params['id'] = $id;
    if (!empty($account_number)) {
      $params['id'] = $account_number;
    }

    $config = $this->tokenAuthorization->getApplicationSettingsAutoPackets("configuration_app");
    $params['billingSystem'] = $config["setting_app_payment"]["billingSystemName"];
    $params['apiHost'] = $config["setting_app_payment"]["api_path"];
    $aws_service = isset($config["setting_app_payment"]["aws_service"]) ? $config["setting_app_payment"]["aws_service"] : 'payment';
    if (empty($id)) {
      $error = new ErrorBase();
      $error->getError()->set('message', 'The account Id does not exist in the current request.');
      throw new UnauthorizedHttpException($error);
    }
    $load_accounts_endpoint = $this->oneappEndpointManager
      ->load('oneapp_convergent_payment_gateway_v2_0_enrollments_endpoint')
      ->setParams($params);
    $config_endpoint = [
      'url' => $load_accounts_endpoint->getReplacedUrlEndpoint(),
      'method' => $load_accounts_endpoint->getMethod(),
    ];
    $headers_aws = $this->awsManager->headersAwsTreatment($config_endpoint, $aws_service, '', []);
    $response = $load_accounts_endpoint
      ->setHeaders($headers_aws)
      ->setQuery([])
      ->setBody([])
      ->sendRequest(FALSE);

    $autpacket = $this->getDataForType('uuid', $this->tokenAuthorization->getUserIdPayment());
    if (!empty($autpacket->offerId) && isset($response->body)) {
      $response->body->subscription->productReference = $autpacket->offerId;
    }
    return $response;
  }

  /**
   * get frequency
   */
  public function getFrequency($offer_data, $validity) {
    $validity_type = isset($offer_data->validityType) ? strtolower($offer_data->validityType) : '';
    return $validity_type == "dias" || $validity_type == "dia" ? $validity * 24 : $validity;
  }

  /**
   * get params of subscribers
   */
  public function getParamsSubscribers($id) {
    $this->params['uuid'] = $this->tokenAuthorization->getUserIdPayment();
    $this->params['tokenUuId'] = $this->tokenAuthorization->getTokenUuid();
    $addtional_data = isset($this->transaction->additionalData) ? unserialize($this->transaction->additionalData) : [];
    $email_transaction = isset($addtional_data->billingData["email"]) ? $addtional_data->billingData["email"] : '';
    $email_transaction = (empty($email_transaction) && isset($addtional_data->email)) ? $addtional_data->email : $email_transaction;
    $this->params['email'] = empty($email_transaction) ?
      $this->tokenAuthorization->getEmail() : $email_transaction;

    if (!isset($this->params['accountNumber'])) {
      $account_number = $this->utilsPayment->getAccountNumberForPaymentGatewayFromToken('mobile', $id);
      $this->params['accountNumber'] = $account_number;
    }

    $this->getTokenizedCardOfDatabase($addtional_data);

    if (!empty($addtional_data)) {
      $this->getSubscriptionDataOfAdditionalData($id, $addtional_data);
    }
    elseif (!empty($this->params["offerId"])) {
      $this->getSubscriptionData($id);
    }
  }

  /**
   * get validity
   */
  public function getValidityTime($offer_data) {
    $offer_data->duration = $offer_data->durationTime ?? $offer_data->duration;
    return isset($offer_data->validityNumber) ? $offer_data->validityNumber : $offer_data->duration;
  }

  /**
   * Get the payment enrollments by params.
   */
  public function getEnrollment($id) {
    $get_enrollments = $this->config["enrollments"]["showCreditCard"] ? $this->getEnrollmentsByParams($id) : $this->getEmptyEnrollment();
    $subscribers = $this->config["enrollments"]["showInvoiceCharge"] ? $this->callSubscribersApi($id) : $this->getEmptyEnrollment();
    if (is_array($subscribers) && $subscribers["state"]== "empty" && !isset($get_enrollments->body)) {
      return $subscribers;
    }
    $lists = [];
    $date_formatter = \Drupal::service('date.formatter');
    $lists = $this->getSubscribersFormat($subscribers);
    if (isset($get_enrollments->body)) {
      $get_enrollments->body->subscription->lastOrderTimeStamp = $this->converDateWithTimeZone($get_enrollments->body->subscription->lastOrderTimeStamp);
      $card = isset($get_enrollments->body->paymentToken) ?
        $this->formattedCard($get_enrollments->body->paymentToken, $date_formatter) : [];
      $offer = isset($get_enrollments->body->subscription) ? $get_enrollments->body->subscription : [];
      $offer_data = $this->getOffer($id, $offer->productReference);
      $offer_data = empty($offer_data) ? $offer : $offer_data;
      $validity = $this->getValidityTime($offer_data);
      $frequency = $this->getFrequency($offer_data, $validity);
      $frequency_days = intval($frequency / 24);
      $frecuency_info = ($frequency_days == 1) ? "cada @day día" : "cada @day días";
      $expiration_date = isset($offer->lastOrderTimeStamp) && $offer->lastOrderTimeStamp == -1 ?
        t("Ilimitado") : $this->utils->formatDateRegressiveWithDuration($offer->lastOrderTimeStamp, $offer->duration, FALSE);
      $lists['enrollments'][] = [
        'id' => isset($get_enrollments->body->id) ? $get_enrollments->body->id : '',
        'offerId' => isset($offer->productReference) ? $offer->productReference : '',
        'offerName' => is_array($offer_data) && isset($offer_data["offerName"]) ? $offer_data["offerName"] : $offer->name,
        'paymentTokenId' => $card->id,
        'cardType' => $card->cardType,
        'maskedCreditCardNumber' => $card->maskedCreditCardNumber,
        'cardExpirationDate' => $card->cardExpirationDate,
        'msisdn' => $id,
        'email' =>  isset($get_enrollments->body->trace->email) ? $get_enrollments->body->trace->email : $this->tokenAuthorization->getMail(),
        'amount' => isset($offer->amount) ?
          ['value'=> $offer->amount, 'formattedValue' => $this->utils->formatCurrency($offer->amount, TRUE)] : '',
        'frequency' => [
          'value' => $frequency,
          'formattedValue' => !empty($frequency_days) ? t($frecuency_info, ['@day' => $frequency_days]) : t('cada 0 días'),
        ],
        'expirationDate' => $expiration_date,
        'nextPayment' => [
          'value' =>  isset($offer->lastOrderTimeStamp) ? $offer->lastOrderTimeStamp : '',
          'formattedValue' => isset($expiration_date['value']) ?
            $date_formatter->format(strtotime($expiration_date['value']), $this->config["configs"]["dates"]["expirationDate"]) : '',
        ],
        'paymentMethod' => [
          'value' =>  'creditCard',
          'formattedValue' => t('Tarjeta de crédito'),
        ],
      ];
    }
    return $lists;
  }

}
