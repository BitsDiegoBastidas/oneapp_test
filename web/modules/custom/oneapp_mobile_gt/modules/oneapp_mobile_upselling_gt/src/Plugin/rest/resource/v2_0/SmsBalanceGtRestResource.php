<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\rest\resource\v2_0;

use Drupal\rest\ResourceResponse;
use Drupal\oneapp_mobile_upselling\Plugin\rest\resource\v2_0\SmsBalanceRestResource;

class SmsBalanceGtRestResource extends SmsBalanceRestResource {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->init();

    $service = \Drupal::service('oneapp_mobile_upselling.v2_0.sms_balance_rest_logic');
    $service->setConfig($this->configBlock);
    $data = $service->get($this->accountId);

    // Build meta, config and data.
    $this->apiResponse->getData()->setAll($data);
    $this->responseConfig($data);

    // Build response with data.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function responseConfig($data = NULL) {

    $service = \Drupal::service('oneapp_mobile_upselling.v2_0.sms_balance_rest_logic');
    $utils = \Drupal::service('oneapp.utils');
    $configs = $this->configBlock['actions']['fields'];
    $typeAccount = $service->getBalance($this->accountId)->typeClient;

    foreach ($configs as $index => $config) {
      $configs[$index]['show'] = $utils->formatBoolean($config["show"]);
    }
    if (isset($data['smsBalance']) && $typeAccount === "CREDITO" || $typeAccount === "STAFF DE COMCEL" || $typeAccount === "FACTURA FIJA") {
      $configs['purchase']['show'] = $utils->formatBoolean($this->configBlock['postpaid']['showBtnBuy']);
    }

    $this->apiResponse
      ->getConfig()
      ->set('actions', $configs);

    if (isset($data['noData'])) {
      $this->apiResponse
        ->getConfig()
        ->set('message', $this->configBlock['messages']['empty'], true);
    }
  }

}
