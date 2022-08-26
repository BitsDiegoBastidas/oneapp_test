<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_mobile_upselling\Plugin\rest\resource\v2_0\DataBalanceDetailRestResource;
use Drupal\rest\ResourceResponse;

class DataBalanceDetailGtRestResource extends DataBalanceDetailRestResource {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->init();
    $serviceRestLogic = \Drupal::service('oneapp_mobile_upselling.v2_0.data_balance_detail_rest_logic');

    $serviceRestLogic->setConfig($this->configBlock);
    $tempData = $serviceRestLogic->get($this->accountId);
    $data = isset($tempData['bucketsList']) ? $tempData : $tempData['data'];
    $config = isset($tempData['config']) ? $tempData['config'] : null;

    // Build meta, config and data.
    if ($this->configBlock['detail']['general']['showHideResponse']['value'] == 1 && isset($data['noData'])) {
      $data['noData']['value'] = 'hide';
    }
    $this->apiResponse->getData()->setAll($data);
    $this->responseConfig($config);

    $apiResponse = $this->apiResponse->toArray();
    $apiResponse['config']['title']['value'] = $this->configBlock['detail']['general']['title']['value'];

    // Build response with data.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function responseConfig($config = NULL) {
    $configs = $this->formattedData();
    $this->apiResponse
      ->getConfig()
      ->set('actions', $configs)
      ->set('imagePath', $this->configBlock['detail']['general']['urlImageLocation']['value']);
    if (isset($config['message_empty'])) {
      $this->apiResponse
        ->getConfig()
        ->set('message', $config['message_empty'], true);
    }
  }

}
