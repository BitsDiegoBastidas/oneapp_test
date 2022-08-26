<?php

namespace Drupal\oneapp_mobile_premium_gt\Services;

use Drupal\oneapp_mobile_premium\Services\PremiumServiceSuperMarket;

/**
 * Class PremiumServiceSuperMarketGt.
 *
 * @package Drupal\oneapp_mobile_premium_gt\Services;
 */
class PremiumServiceSuperMarketGt extends PremiumServiceSuperMarket {

  /**
   * Subscribe to amazon.
   */
  public function subscribe($id, $product, $payload, $available_offers, $active_offers) {

    try {
      $id_product = $product->get('id_service')->value;
      if (strtolower($id_product) == 'office365') {
        if (empty($payload["confirmationId"])) {
          $response = $this->subscribeSupermarket($id, $product, $payload, $available_offers, $active_offers);
          $payload["confirmationId"] = $response->subscriptionCode;
          try {
            $response_license = $this->sendLicenseOffice($id, $product, $payload, $available_offers, $active_offers);
          }
          catch (\Exception $err) {
            $response_license = '';
          }
        }
        else {
          $response = $this->sendLicenseOffice($id, $product, $payload, $available_offers, $active_offers);
        }
      }
      else {
        $response = $this->subscribeSupermarket($id, $product, $payload, $available_offers, $active_offers);
      }

      $response = json_decode(json_encode($response), TRUE);

      if (isset($response) && isset($response['nextAction']) && strtolower($response['nextAction']) == 'redirect') {
        $redirect_url = $response['nextActionParams']['redirectionUrl'];
      }

      $response['redirect'] = $redirect_url;

      return $response;
    }
    catch (\Exception $e) {
      return NULL;
    }

  }

  /**
   * Send license to Office365.
   */
  protected function callDocomoOfficeSendLicense($params, $body) {

    return $this->manager
      ->load('oneapp_mobile_premium_v2_0_docomo_office_send_license_endpoint')
      ->setParams($params)
      ->setHeaders([])
      ->setBody($body)
      ->sendRequest();
  }

  /**
   * Subscribe Office.
   */
  protected function sendLicenseOffice($id, $product, $payload, $available_offers, $active_offers) {
    $id_product = $product->get('id_service')->value;
    $config_service = $product->get('service_config')->value;
    $config_service = $config_service != NULL && $config_service != '' ? json_decode($config_service, TRUE) : NULL;

    $prefix = $this->utils::PREFIX_CONFIG;
    $telco_code = $config_service[$prefix . 'telco_code'];
    $service_id = $config_service[$prefix . 'service_id'];
    $type_service = $this->getType($product, $available_offers, $active_offers);
    $type_service = strtolower(str_replace("-", "_", $type_service));
    $id = $this->getIdWithPrefix($id);

    $params['confirmationId'] = $payload['confirmationId'];
    $params['serviceId'] = $service_id;

    $body['productName'] = $id_product;
    $body['msisdn'] = $id;
    $body['alias'] = NULL;
    $body['telcoCode'] = $telco_code;
    $body['requestId'] = $id . time();

    return $this->callDocomoOfficeSendLicense($params, $body);
  }

  /**
   * Subscribe Supermarket.
   */
  protected function subscribeSupermarket($id, $product, $payload, $available_offers, $active_offers) {
    $id_product = $product->get('id_service')->value;
    $config_service = $product->get('service_config')->value;
    $config_service = $config_service != NULL && $config_service != '' ? json_decode($config_service, TRUE) : NULL;

    $prefix = $this->utils::PREFIX_CONFIG;
    $telco_code = $config_service[$prefix . 'telco_code'];
    $service_id = $config_service[$prefix . 'service_id'];
    $type_service = $this->getType($product, $available_offers, $active_offers);
    $type_service = strtolower(str_replace("-", "_", $type_service));
    $promo_id = $config_service[$prefix . 'id_' . $type_service];
    $id = $this->getIdWithPrefix($id);

    $params['productName'] = $id_product;
    $params['msisdn'] = $id;
    $params['alias'] = NULL;
    $params['telcoCode'] = $telco_code;
    $params['profile'] = ['lineType' => 'mobile'];
    $params['requestId'] = $id . time();
    $params['promoId'] = [$promo_id];

    $response = $this->callDocomoSupermarketSubscribeApi($service_id, $params);
    $response->textCardConfirmActivation = $config_service["config_text_card_confirm_activation_" . $type_service]['value'];
    return $response;
  }

  /**
   * Get amazon data.
   */
  public function getData($id, $product, $available_offers, $active_offers) {

    $id_product = $product->get('id_service')->value;
    $config_service = $product->get('service_config')->value;
    $config_service = $config_service != NULL && $config_service != '' ? json_decode($config_service, TRUE) : NULL;

    $prefix = $this->utils::PREFIX_CONFIG;
    $telco_code = $config_service[$prefix . 'telco_code'];
    $service_id = $config_service[$prefix . 'service_id'];

    $id = $this->getIdWithPrefix($id);
    $params['productName'] = $id_product;
    $params['msisdn'] = $id;
    $params['alias'] = NULL;
    $params['telcoCode'] = $telco_code;
    $params['requestId'] = $id . time();

    $response = $this->getDocomoData($service_id, $params);
    $data_entity = $this->getDataEntity($product, $available_offers, $active_offers);
    $type_service = $this->getType($product, $available_offers, $active_offers);

    if (array_key_exists('has_subscriptions', $response) && $response['has_subscriptions']) {
      $data_entity['isActive'] = TRUE;
      $data_entity['pendingUnsubscription'] = $response['pending_unsubscription'];
      $data_entity['unsubscriptionDate'] = $response['unsubscription_date'];
      $data_entity['unsubscriptionDateRequested'] = $response['unsubscription_date_requested'];
      $data_entity['dateActivated'] = $response['date_activated'];
      $data_entity['subscriptionCode'] = $response['subscriptionCode'];
    }
    else {
      $data_entity['isActive'] = FALSE;
      if (is_null($type_service)) {
        return NULL;
      }
    }
    return $data_entity;
  }

  /**
   * Get docomo data.
   */
  protected function getDocomoData($service_id, $body) {

    try {
      $response = $this->callDocomoSupermarketGetSubscriptionsApi($service_id, $body);

      if (isset($response) && isset($response->status) && strtolower($response->status) == "identified" && !empty($response->subscriptions)
        && isset($response->subscriptions[0]->status) && strtolower($response->subscriptions[0]->status) != 'inactive') {
        $pending_unsubscription = FALSE;
        if (isset($response->subscriptions[0]->status) && strtolower($response->subscriptions[0]->status) == "pending_unsubscription") {
          $pending_unsubscription = TRUE;
          $time_zone = date_default_timezone_get();
          if (isset($response->subscriptions[0]->dateUnsubscriptionScheduled)) {
            $time_scheduled = strtotime($response->subscriptions[0]->dateUnsubscriptionScheduled);
            $unsubscription_date = \Drupal::service('date.formatter')->format($time_scheduled, 'custom', 'd/m/Y', $time_zone);
          }
          if (isset($response->subscriptions[0]->dateUnsubscriptionRequested)) {
            $time_requested = strtotime($response->subscriptions[0]->dateUnsubscriptionRequested);
            $unsubscription_date_requested = \Drupal::service('date.formatter')->format($time_requested, 'custom', 'd/m/Y', $time_zone);
          }
          if (isset($response->subscriptions[0]->dateActivated)) {
            $time_activated = strtotime($response->subscriptions[0]->dateActivated);
            $date_activated = \Drupal::service('date.formatter')->format($time_activated, 'custom', 'd/m/Y', $time_zone);
          }
        }
        return [
          'has_subscriptions' => TRUE,
          'pending_unsubscription' => $pending_unsubscription,
          'unsubscription_date' => isset($unsubscription_date) ? $unsubscription_date : "",
          'unsubscription_date_requested' => isset($unsubscription_date_requested) ? $unsubscription_date_requested : "",
          'date_activated' => isset($date_activated) ? $date_activated : "",
          'subscriptionCode' => $response->subscriptions[0]->subscriptionCode ?? '',
        ];
      }

    }
    catch (\Exception $e) {
      return ['has_subscriptions' => FALSE];
    }

    return ['has_subscriptions' => FALSE];
  }

}
