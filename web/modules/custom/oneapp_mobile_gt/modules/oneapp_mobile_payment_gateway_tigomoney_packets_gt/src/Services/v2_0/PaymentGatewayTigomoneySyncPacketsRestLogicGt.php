<?php

namespace Drupal\oneapp_mobile_payment_gateway_tigomoney_packets_gt\Services\v2_0;

use Drupal\oneapp_mobile_payment_gateway_tigomoney_packets\Services\v2_0\PaymentGatewayTigomoneySyncPacketsRestLogic;

class PaymentGatewayTigomoneySyncPacketsRestLogicGt extends PaymentGatewayTigomoneySyncPacketsRestLogic {

  /**
   * Genera la orden.
   */
  public function generateOrderId($business_unit, $product_type, $id_type, $id, $purchaseorder_id) {
    $is_blocked_payment_he = (bool) $this->config['he_otp']['disable']['value'];
    if ($is_blocked_payment_he && $this->tokenAuthorization->isHe()) {
      throw new \Exception($this->config['he_otp']['disable']['message'], Response::HTTP_BAD_REQUEST);
    }
    $this->params['uuid'] = $this->tokenAuthorization->getUserIdPayment();
    $this->params['tokenUuId'] = $this->tokenAuthorization->getTokenUuid();
    if (!$this->tokenAuthorization->isHe()) {
      $this->params['email'] = $this->tokenAuthorization->getEmail();
      $this->params['customerNameToken'] = $this->tokenAuthorization->getGivenNameUser() . " " .
        $this->tokenAuthorization->getFirstNameUser();
    }
    $document_number = (isset($this->params['billingData']['nit']) && (strlen($this->params['billingData']['nit']) > 0)) ?
      strtoupper(trim($this->params['billingData']['nit'])) : "CF";
    if (isset($this->params['billingData'])) {
      $this->params['billingData']['nit'] = $document_number;
      if (isset($this->params['billingData']['address']) && (strlen($this->params['billingData']['address']) > 0)) {
        $this->params['street'] = trim($this->params['billingData']['address']);
      }
      $this->params['email'] = (isset($this->params['billingData']['email']) && (strlen($this->params['billingData']['email']) > 0)) ?
        trim($this->params['billingData']['email']) : trim($this->params['email']);
      if (isset($this->params['billingData']['fullname']) && (strlen($this->params['billingData']['fullname']) > 0)) {
        if (isset($this->params['customerNameToken']) && isset($this->params['tokenizedCardId'])) {
          $this->params['customerNameToken'] = ucwords(strtolower(trim($this->params['billingData']['fullname'])));
        }
        else {
          $this->params['customerName'] = ucwords(strtolower(trim($this->params['billingData']['fullname'])));
        }
      }
      $type = $this->utilsPayment->saveBillingData($this->params['billingData']);
    }
    // Validacion de sobrescritura  oneapp_mobile_payment_gateway_config.
    $add_data = [];
    $config_fac = \Drupal::config("oneapp.payment_gateway_tigomoney.mobile_packets.config")->getRawData();
    if ($config_fac['billing_form']['overwrite_data']) {
      $this->validateDataOverWrite($add_data, $config_fac);
    }

    $decrypt_purchaseorder_id = $this->transactions->decryptId($purchaseorder_id);
    $config_app = $this->utilsPayment->getConfigPayment($product_type, 'configuration_app', $business_unit);
    $this->params['apiHost'] = $config_app->setting_app_payment['api_path'];
    $this->params['aws_service'] = isset($config_app->setting_app_payment["aws_service"]) ?
      $config_app->setting_app_payment["aws_service"] : 'payment';
    $data_transaction = $this->transactions->getTransactionById($decrypt_purchaseorder_id, []);

    $this->params['packageId'] = $data_transaction->numberReference;
    $offer = (array) unserialize($data_transaction->additionalData);
    $id = $this->modifyMsisdn($id);
    $add_data += [
      'documentNumber' => $document_number,
      'documentType' => "NIT",
      'productReference' => isset($offer['creditPackageCategory']) ? $offer['creditPackageCategory'] : $offer['productReference'],
      "purchaseDetails" => [
        [
          "name" => "CO0434",
          "quantity" => (string) $offer['creditPackagePrice'],
          "amount" => '1',
        ],
      ],
    ];
    $body = $this->utilsPayment
      ->getBodyPayment($business_unit, $product_type, $id_type, $id, $purchaseorder_id, $this->params, $add_data);
    $order = $this->utilsPayment
      ->generateOrderId($body, $business_unit, $product_type, $this->params);
    $this->transactions->updateDataTransactionOrderInProgress($decrypt_purchaseorder_id, $order);
    $body_logs = $body;
    $body_logs['creditCardDetails'] = [];
    $fields_log = [
      'purchaseOrderId' => $decrypt_purchaseorder_id,
      'message' => "Order in progress",
      'codeStatus' => 200,
      'operation' => $this->transactions::CREATED_ORDER,
      'description' => "Back office response: \n" . json_encode($order->body, JSON_PRETTY_PRINT) .
        "\nBody: \n" . json_encode($body_logs, JSON_PRETTY_PRINT),
      'type' => $product_type,
    ];
    $this->transactions->addLog($fields_log);
    return $order->body;
  }

  /**
   * PaymentGatewayRestLogic start (Inicializa el proceso de pagos)
   */
  public function start($id, $business_unit, $product_type, $params, $request, $target_msisdn) {
    $is_blocked_payment_he = (bool) $this->config['he_otp']['disable']['value'];
    if ($is_blocked_payment_he && $this->tokenAuthorization->isHe()) {
      return [
        'message' => $this->config['he_otp']['disable']['message'],
        'actions' => [
          'label' => $this->config['he_otp']['disable']['button'],
          'show' => TRUE,
          'type' => "button",
        ],
      ];
    }
    $id = $this->modifyMsisdn($id);
    if (isset($target_msisdn)) {
      $target_msisdn = $this->modifyMsisdn($target_msisdn);
    }
    $params['offerId'] = $this->parseProductReference($params['offerId']);
    $offer = $this->getOffer($id, $params["offerId"]);
    $general_config_tigomoney = \Drupal::config('oneapp_convergent_payment_gateway.config')->get('tigoMoney');
    if ($general_config_tigomoney['validateAccountTigoMoney']['value'] == "yes") {
      $this->validTigomoneyAccount = \Drupal::service('oneapp_convergent_payment_gateway_tigomoney.v2_0.validityTigomoneyAccount_service')
        ->get($id);
      $offer->isValidTigoMoneyAccount = $this->validTigomoneyAccount;
    }
    else {
      $this->validTigomoneyAccount = [
        'value' => TRUE,
        'url' => '',
      ];
      $offer->isValidTigoMoneyAccount = $this->validTigomoneyAccount;
    }
    $params['offerId'] = $this->parseProductReferencePaymentGateway($params['offerId']);
    $params['query']['accountNumber'] = (isset($target_msisdn)) ? $target_msisdn : $id;
    $fields = [
      'uuid' => $this->tokenAuthorization->getUserIdPayment(),
      'accountId' => $id,
      'accountNumber' => (isset($target_msisdn)) ? $target_msisdn : $id,
      'accountType' => $business_unit,
      'productType' => $product_type,
      'amount' => $offer->creditPackagePrice,
      'isPartialPayment' => 0,
      'numberReference' => $params['offerId'],
      'additionalData' => serialize($offer),
      'accessType' => $this->tokenAuthorization->getAccessType(),
    ];
    $transaction_id = $this->transactions->initTransaction($fields, $product_type);
    $purchaseorder_id = $this->transactions->encryptId($transaction_id);
    $data_offer = $this->formatOffers($offer, $fields, $purchaseorder_id);
    $response = [
      'isValidTigoMoneyAccount' => $this->validTigomoneyAccount['value'],
      'offer' => $data_offer,
    ];
    if ($this->tokenAuthorization->isHe()) {
      $response['message'] = $this->config['he_otp']['flow']['message'];
      $response['actions']['messageButton'] = [
        'label' => $this->config['he_otp']['flow']['button'],
        'show' => TRUE,
        'type' => 'button',
      ];
    }
    return $response;
  }

  /**
   * Obtiene la oferta.
   */
  public function getOffer($id, $offer_id) {
    $offer = new \stdClass();
    $config_app = \Drupal::config("oneapp.payment_gateway_tigomoney.mobile_packets.config")->get('configuration_app');
    if (\Drupal::hasService('oneapp_mobile_upselling.v2_0.offer_details_rest_logic')) {
      $mobile_utils_service = \Drupal::service('oneapp.mobile.utils');
      $new_msisdn = $mobile_utils_service->modifyMsisdnCountryCode($id, FALSE);
      $offer_details = \Drupal::service('oneapp_mobile_upselling.v2_0.offer_details_rest_logic')->get($new_msisdn, $offer_id);
      if (isset($offer_details['error'])) {
        throw new \Exception('La oferta no es correcta', Response::HTTP_BAD_REQUEST);
      }

      if (isset($offer_details['additionalData']['acquisitionMethods'])) {
        foreach ($offer_details['additionalData']['acquisitionMethods'] as $methods) {
          if ($methods['paymentMethodName'] == "TIGOMONEY") {
            $currency = $config_app['setting_app_payment']['currency'];
            if (is_array($methods['cost'])) {
              foreach ($methods['cost'] as $cost) {
                $cost = (array) $cost;
                if ($currency == $cost['currencyId']) {
                  $offer->cost = $cost['amount'];
                  break;
                }
              }
            }
            else {
              if ($methods['currencyId'] == $currency) {
                $offer->cost = $methods['cost'];
              }
              break;
            }
          }
        }
      }
      if (!isset($offer->cost)) {
        if (is_array($offer_details['cost'])) {
          $currency = $config_app['setting_app_payment']['currency'];
          foreach ($offer_details['cost'] as $cost) {
            $cost = (array) $cost;
            if ($currency == $cost['currencyId']) {
              $offer->cost = $cost['amount'];
              break;
            }
          }
        }
      }
      if (isset($offer_details['additionalData']['validityTmoney'])) {
        $offer->validityTmoney = $offer_details['additionalData']['validityTmoney'];
      }
      if (isset($offer_details['additionalData']['validityNumberTmoney'])) {
        $offer->validityNumberTmoney = $offer_details['additionalData']['validityNumberTmoney'];
      }
      if (isset($offer_details['additionalData']['validityTypeTmoney'])) {
        $offer->validityTypeTmoney = $offer_details['additionalData']['validityTypeTmoney'];
      }
      $offer->offerId = $offer_details['offerId'];
      $offer->category = $offer_details['category'];
      $offer->creditPackageCategory = $offer_details['creditPackageCategory'];
      $offer->creditPackagePrice = $offer_details['additionalData']['creditPackagePrice'];
      $offer->validity = isset($offer_details['validity']) ? $offer_details['validity'] : '';
      $offer->validityNumber = $offer_details['validityNumber'];
      $offer->validityType = $offer_details['validityType'];
      $offer->name = $offer_details['name'];
      $offer->description = $offer_details['description'];
      $this->translateValidityType($offer->validityType);
    }
    return $offer;
  }

  /**
   * Translate function for validityType to spanish.
   */
  private function translateValidityType(&$validity_type) {
    $validity_type = str_replace([
      'dia',
      'dias',
      'day',
      'days',
      'month',
      'months',
      'week',
      'weeks',
      'hour',
      'hours',
    ], [
      'día',
      'días',
      'día',
      'días',
      'mes',
      'meses',
      'semana',
      'semanas',
      'hora',
      'horas',
    ], strtolower($validity_type));
  }

  /**
   * Dar formato a las ofertas.
   */
  public function formatOffers($offer, $fields, $purchaseorder_id = FALSE) {
    $row = [];
    $oneapp_utils = \Drupal::service('oneapp.utils');
    $row['offerId'] = [
      'label' => $this->config["fields"]["offerId"]["label"],
      'show' => $this->config["fields"]["offerId"]["show"] ? TRUE : FALSE,
      'value' => $offer->offerId,
      'formattedValue' => (string) $offer->offerId,
    ];
    $row['accountNumber'] = [
      'label' => $this->config["fields"]["accountNumber"]["label"],
      'show' => $this->config["fields"]["accountNumber"]["show"] ? TRUE : FALSE,
      'value' => $fields['accountNumber'],
      'formattedValue' => $this->mobileUtils->modifyMsisdnCountryCode($fields['accountNumber'], FALSE),
    ];
    $row['offerName'] = [
      'label' => $this->config["fields"]["offerName"]["label"],
      'show' => $this->config["fields"]["offerName"]["show"] ? TRUE : FALSE,
      'value' => $offer->name,
      'formattedValue' => (string) $offer->name,
    ];
    $row['description'] = [
      'label' => $this->config["fields"]["description"]["label"],
      'show' => $this->config["fields"]["description"]["show"] ? TRUE : FALSE,
      'value' => $offer->description,
      'formattedValue' => (string) $offer->description,
    ];
    $row['categoryName'] = [
      'label' => $this->config["fields"]["categoryName"]["label"],
      'show' => $this->config["fields"]["categoryName"]["show"] ? TRUE : FALSE,
      'value' => $offer->category,
      'formattedValue' => $offer->category,
    ];
    $validity_offer = $this->getValidity($offer);
    $row['validity'] = [
      'label' => $this->config["fields"]["validity"]["label"],
      'show' => $this->config["fields"]["validity"]["show"] ? TRUE : FALSE,
      'value' => $validity_offer['value'],
      'formattedValue' => $validity_offer['formattedValue'],
    ];
    $config_app = \Drupal::config("oneapp.payment_gateway_tigomoney.mobile_packets.config")->get('configuration_app');
    $currency = $config_app['setting_app_payment']['currency'];
    $currency = ($currency === 'USD') ? FALSE : TRUE;
    $row['amount'] = [
      'label' => $this->config["fields"]["amount"]["label"],
      'show' => $this->config["fields"]["amount"]["show"] ? TRUE : FALSE,
      'value' => $offer->creditPackagePrice,
      'formattedValue' => $oneapp_utils->formatCurrency($offer->creditPackagePrice, $currency),
    ];
    if ($purchaseorder_id != FALSE) {
      $row['purchaseOrderId'] = [
        'label' => $this->config["fields"]["purchaseOrderId"]["label"],
        'show' => $this->config["fields"]["purchaseOrderId"]["show"] ? TRUE : FALSE,
        'value' => $purchaseorder_id,
        'formattedValue' => $purchaseorder_id,
      ];
      $row['productType'] = [
        'label' => $this->config["fields"]["productType"]["label"],
        'show' => $this->config["fields"]["productType"]["show"] ? TRUE : FALSE,
        'value' => (string) $offer->description,
        'formattedValue' => (string) $offer->description,
      ];
      $row['paymentMethod'] = [
        'label' => $this->config["fields"]["paymentMethod"]["label"],
        'show' => $this->config["fields"]["paymentMethod"]["show"] ? TRUE : FALSE,
        'value' => $this->config["fields"]["paymentMethod"]["value"],
        'formattedValue' => $this->config["fields"]["paymentMethod"]["value"],
      ];
      $row['wallet'] = [
        'label' => $this->config["fields"]["wallet"]["label"],
        'show' => $this->config["fields"]["wallet"]["show"] ? TRUE : FALSE,
        'value' => $this->config["fields"]["wallet"]["value"],
        'formattedValue' => $this->config["fields"]["wallet"]["value"],
      ];
      $row['pinless'] = [
        'value' => $this->isPinless('packets', 'mobile', $offer->cost),
      ];
      if (isset($offer->isValidTigoMoneyAccount) && !$offer->isValidTigoMoneyAccount['value']) {
        $general_config_tigomoney = \Drupal::config('oneapp_convergent_payment_gateway.config')->get('tigoMoney');
        $form_packets_config = \Drupal::config('oneapp.payment_gateway_tigomoney.mobile_packets.config')->get('redirectUrl');
        $row['redirectUrl'] = [
          'label' => $form_packets_config['redirectUrl']['label'],
          'value' => $general_config_tigomoney['tigoMoneySyncAccountRegistrationForm']['actions']['register']['url'],
          'formattedValue' => $general_config_tigomoney['tigoMoneySyncAccountRegistrationForm']['actions']['register']['url'],
          'show' => (bool) $form_packets_config['show'],
        ];
      }
      else {
        $row['redirectUrl'] = [
          'label' => '',
          'value' => '',
          'formattedValue' => '',
          'show' => FALSE,
        ];
      }
    }
    return $row;
  }

}
