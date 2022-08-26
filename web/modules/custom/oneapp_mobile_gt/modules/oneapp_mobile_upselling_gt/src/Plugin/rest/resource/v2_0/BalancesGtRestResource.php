<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_mobile_upselling\Plugin\rest\resource\v2_0\BalancesRestResource;
use Drupal\rest\ResourceResponse;

class BalancesGtRestResource extends BalancesRestResource {

  protected $isEmptyBucketList;

  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->init();
    \Drupal::service('page_cache_kill_switch')->trigger();

    $serviceRestLogic = \Drupal::service('oneapp_mobile_upselling.v2_0.balances_rest_logic');
    $this->utils = \Drupal::service('oneapp.utils');
    $serviceRestLogic->setConfig($this->configBlock);
    $data = $serviceRestLogic->get($this->accountId);

    // Build meta, config and data.
    $this->apiResponse->getData()->setAll($data);
    $this->isEmptyBucketList = count($data['BucketsBalanceList']) > 0 ? 1 : 0;
    $this->responseConfig();


    // Build response with data.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function responseConfig() {
    $configs = $this->formattedGeneralData($this->configBlock['general']['subtitle']['properties']);
    $details = $this->formattedLabelDetails($this->configBlock['general']['label_details']);
    $details['show'] = $this->isEmptyBucketList ? $details['show'] : false;
    $this->apiResponse
      ->getConfig()
      ->set('subtitle', $configs['subtitle'])
      ->set('details', $details)
      ->set('update', $this->formattedLabelUpdate($this->configBlock['buttons']['updateButton']))
      ->set('actions', $this->formattedButtonsData($this->configBlock['buttons']))
    ->set('BucketsBalanceList', [
      'title' => [
        'value' => $this->configBlock['headerList']['fields']['name']['label'],
        'show' => true,
      ],
    ]);
  }

}
