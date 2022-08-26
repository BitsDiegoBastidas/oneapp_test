<?php

namespace Drupal\oneapp_mobile_premium_gt\Services;

use Drupal\oneapp_mobile_premium\Services\PremiumServiceAmz;

/**
 * Class BillingService.
 *
 * @package Drupal\oneapp_mobile_premium\Services;
 */
class PremiumServiceAmzGt extends PremiumServiceAmz {

  /**
   * Unsubscribe to amazon.
   */
  public function unsubscribe($id, $product, $payload, $available_offers, $active_offers) {

    $id_product = $product->get('id_service')->value;
    $product_name = $product->get('id_service_api')->value;
    $config_service = $product->get('service_config')->value;
    $config_service = $config_service != NULL && $config_service != '' ? json_decode($config_service, TRUE) : NULL;

    $prefix = $this->utils::PREFIX_CONFIG;
    $telco_code = $config_service[$prefix . 'telco_code'];

    $id = $this->getIdWithPrefix($id);

    $params['productName'] = $product_name;
    $params['msisdn'] = $id;
    $params['alias'] = NULL;
    $params['telcoCode'] = $telco_code;
    $params['profile'] = ['lineType' => 'mobile'];
    $params['requestId'] = $id . time();
    $params['reasonDetail'] = 'Customer has terminated the paid add-on.';
    $params['reason'] = 'OPT_OUT';

    $response = $this->callDocomoUnSubscribeApi($params);
    if ($response) {
      $offering = array_column($active_offers, 'offeringId');
      $addon = $config_service["config_id_addon"];
      $addon_full = $config_service["config_id_addon_full"];
      $offering_id = '';
      if (!empty($addon) && in_array($addon, $offering)) {
        $offering_id = $addon;
      }
      elseif (!empty($addon_full) && in_array($addon_full, $offering)) {
        $offering_id = $addon_full;
      }
      $response_vas = $this->callDeleteSubscribeVasApi($id, $offering_id);
    }

    $data_entity = $this->getDataEntity($product, $available_offers, $active_offers);

    if (isset($response) && isset($response->dateUnsubscriptionScheduled)) {
      $time_zone = date_default_timezone_get();
      $time_scheduled = strtotime($response->dateUnsubscriptionScheduled);
      $unsubscription_date = \Drupal::service('date.formatter')->format($time_scheduled, 'custom', 'd/m/Y', $time_zone);
      $data_entity['pendingUnsubscription'] = $unsubscription_date;
    }

    $data_entity['bodyText'] = $product->get('text_card_confirm_deactivation')->value;

    return $data_entity;
  }

  /**
   * Call Api delete subscribe Vas Amazon.
   */
  protected function callDeleteSubscribeVasApi($id, $offering_id) {
    return $this->manager
      ->load('oneapp_mobile_premium_v2_0_delete_subscribe_vas_endpoint')
      ->setParams(['id' => $id, 'offeringId' => $offering_id])
      ->setHeaders(['Content-Type' => 'application/json'])
      ->setBody([])
      ->sendRequest();
  }

}
