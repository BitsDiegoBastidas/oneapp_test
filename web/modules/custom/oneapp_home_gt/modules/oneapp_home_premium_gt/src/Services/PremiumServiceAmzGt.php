<?php

namespace Drupal\oneapp_home_premium_gt\Services;

use Drupal\oneapp_home_premium\Services\PremiumServiceAmz;
use Drupal\oneapp_home_premium\Services\UtilService;

/**
 * Class PremiumServiceAmz.
 *
 * @package Drupal\oneapp_home_premium\Services;
 */
class PremiumServiceAmzGt extends PremiumServiceAmz {

  /**
   * Get amazon data.
   */
  public function getData($id, $product, $available_offers, $active_offers) {

    $id_product = $product->get('id_service')->value;
    $config_service = $product->get('service_config')->value;
    $config_service = $config_service != NULL && $config_service != '' ? json_decode($config_service, TRUE) : NULL;

    $prefix = UtilService::PREFIX_CONFIG;
    $telco_code = $config_service[$prefix . 'telco_code'];

    $type = $this->getType($product, $available_offers, $active_offers);
    $key_service = "config_id_" . strtolower(str_replace('-', '_', $type));
    $offering_id = (!empty($config_service[$key_service])) ? $config_service[$key_service] : '';
    $status = "";

    //Tomar status para la activacion de amazon home GT.
    if (!empty($available_offers)) {
      foreach ($available_offers as $offer) {
        if ($offer["offeringId"] == $offering_id) {
          $status = $offer["offer"]["status"];
          break;
        }
      }
    }
    if (!empty($active_offers) && empty($status)) {
      foreach ($active_offers as $offer) {
        if ($offer["offeringId"] == $offering_id) {
          $status = $offer["offer"]["currentState"];
          break;
        }
      }
    }

    $params['productName'] = $id_product;
    $params['msisdn'] = NULL;
    $params['alias'] = $status;
    $params['telcoCode'] = $telco_code;
    $params['requestId'] = $id . time();

    $response = $this->getDocomoData($params);
    $data_entity = $this->getDataEntity($product, $available_offers, $active_offers);
    $type_service = $this->getType($product, $available_offers, $active_offers);

    if (array_key_exists('has_subscriptions', $response) && $response['has_subscriptions'] == TRUE) {
      $data_entity['isActive'] = TRUE;
      $data_entity['pendingUnsubscription'] = $response['pending_unsubscription'];
      $data_entity['unsubscriptionDate'] = $response['unsubscription_date'];
      $data_entity['unsubscriptionDateRequested'] = $response['unsubscription_date_requested'];
      $data_entity['dateActivated'] = $response['date_activated'];
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
   * Subscribe to amazon.
   */
  public function getDocomoSubscribe($id, $product, $available_offers, $active_offers) {

    $id_product = $product->get('id_service')->value;
    $config_service = $product->get('service_config')->value;
    $config_service = $config_service != NULL && $config_service != '' ? json_decode($config_service, TRUE) : NULL;

    $prefix = UtilService::PREFIX_CONFIG;
    $telco_code = $config_service[$prefix . 'telco_code'];

    $type = $this->getType($product, $available_offers, $active_offers);
    $offering_id = $config_service["config_id_".strtolower(str_replace('-', '_', $type))];
    $status = "";

    //Tomar status para la activacion de amazon home GT.
    if (!empty($available_offers)) {
      foreach ($available_offers as $offer) {
        if ($offer["offeringId"] == $offering_id) {
          $status = $offer["offer"]["status"];
          break;
        }
      }
    }

    if (!is_null($type)) {
      $params['productName'] = $id_product;
      $params['msisdn'] = NULL;
      $params['alias'] = $status;
      $params['telcoCode'] = $telco_code;
      $params['offeringType'] = ($type == "ADDON-FULL") ? "ADDON" : $type;
      $params['profile'] = ['lineType' => 'home'];
      $params['requestId'] = $id . time();

      $response = $this->callDocomoSubscribeApi($params);

      $response = json_decode(json_encode($response), TRUE);
      $confirmation_id = NULL;

      if (isset($response) && isset($response['nextAction']) && strtolower($response['nextAction']) == 'user_confirmation') {
        $url_elements = explode("/", $response['resumeUrl']);
        $element = array_slice($url_elements, -2, 1, TRUE);
        $confirmation_id = array_values($element);
      }

      return $confirmation_id[0];
    }

    return NULL;
  }


  /**
   * Unsubscribe to amazon.
   */
  public function unsubscribe($id, $product, $payload, $available_offers, $active_offers) {

    try {
      $config_service = $product->get('service_config')->value;
      $config_service = $config_service != NULL && $config_service != '' ? json_decode($config_service, TRUE) : NULL;

      // Tomar Tipo Addon y Addon_full
      $type = $this->getType($product, $available_offers, $active_offers);
      // Tomar Identificador Addon y Addon_full
      $config_ids = $config_service["config_id_".strtolower(str_replace('-', '_', $type))];
      $offering_ids = explode(",", $config_ids);
      $offering_id = "";
      $status = "";

      //Tomar status para la desactivar de amazon home GT.
      if (!empty($active_offers)) {
        foreach ($active_offers as $offer) {
          if (in_array($offer['offeringId'], $offering_ids)) {
            $offering_id = $offer['offeringId'];
            $status = $offer['offer']['currentState'];
            break;
          }
        }
      }
      $body = [
        'offeringId' => $offering_id,
      ];

      $response = $this->callUnSetUpsellingTVApi($status, $offering_id, $body);
      $data_entity = $this->getDataEntity($product, $available_offers, $active_offers);

      if (isset($response) && isset($response->dateUnsubscriptionScheduled)) {
        $time_zone = date_default_timezone_get();
        $scheduled_time = strtotime($response->dateUnsubscriptionScheduled);
        $unsubscription_date = \Drupal::service('date.formatter')->format($scheduled_time, 'custom', 'd/m/Y', $time_zone);
        $data_entity['pendingUnsubscription'] = $unsubscription_date;
      }

      $data_entity['bodyText'] = $product->get('text_card_confirm_deactivation')->value;
      return $data_entity;
    }
    catch (\Exception $e) {
      return NULL;
    }

  }

  /**
  * Call api UnSetUpsellingTV home amazon Premium.
  */
  protected function callUnSetUpsellingTVApi($id, $offering_id, $body) {

    return $this->manager
      ->load('oneapp_home_premium_v2_0_unset_uppselling_tv_endpoint')
      ->setParams(['id' => $id, 'offeringId' => $offering_id ])
      ->setHeaders([])
      ->setBody($body)
      ->sendRequest();
  }

}
