<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\rest\resource\v2_0;

use Drupal\rest\ResourceResponse;
use Drupal\oneapp_mobile_upselling\Plugin\rest\resource\v2_0\DataBalanceRestResource;

class DataBalanceGtRestResource extends DataBalanceRestResource {

  /**
   * {@inheritdoc}
   */
  public function get($idType, $id) {
    $this->init();
    \Drupal::service('page_cache_kill_switch')->trigger();

    $msisdn = $this->accountId;
    $serviceRestLogic = \Drupal::service('oneapp_mobile_upselling.v2_0.data_balance_rest_logic');
    $serviceRestLogic->setConfig($this->configBlock);
    $result = $serviceRestLogic->get($msisdn);

    // Build meta, config and data.
    $this->apiResponse->getData()->setAll($result['data']);
    $this->responseConfig($result['config']);

    // Build response with data.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function responseConfig($config = NULL) {
    if (isset($config) && !empty($config) && isset($config['description'])) {
      $this->apiResponse
        ->getConfig()
        ->set('description', $config['description'], true);
    }
    $configs = $this->formattedData();
    $this->apiResponse
      ->getConfig()
      ->set('actions', $configs);
    if (isset($config['message_empty'])) {
      $this->apiResponse
        ->getConfig()
        ->set('message', $config['message_empty'], true);
    }
  }

}
