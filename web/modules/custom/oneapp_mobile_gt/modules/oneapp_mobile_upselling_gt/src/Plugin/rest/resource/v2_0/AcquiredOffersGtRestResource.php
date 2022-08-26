<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_mobile_upselling\Plugin\rest\resource\v2_0\AcquiredOffersRestResource;
use Drupal\rest\ResourceResponse;

class AcquiredOffersGtRestResource extends AcquiredOffersRestResource {

  protected $isFavorite = FALSE;

  /**
   * {@inheritdoc}
   */
  public function post($idType, $id, $data) {
    $this->init();

    $service = \Drupal::service('oneapp_mobile_upselling.v2_0.acquired_offers_rest_logic');
    $service->setConfig($this->configBlock);

    $response = $service->post($id, $data);
    if ($response['success']) {
      $this->apiResponse->getData()->setAll($response['data']);
      $this->isFavorite = $response['isFavorite'];
      $this->responseConfigSuccess();
    }
    else {
      $this->apiResponse->getData()->setAll($response['data']);
      $this->responseConfigError();
    }

    // Build response with data.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function responseConfigSuccess() {
    $utils = \Drupal::service('oneapp.utils');
    $actions = [];
    $configHome = $this->configBlock['config']['response']['postSuccess']['home'];
    $config = [
      'label' => $this->isFavorite ? $configHome['labelForFavorite'] : $configHome['label'],
      'type' => $configHome['type'],
      'url' => $this->isFavorite ? $configHome['urlForFavorite'] : $configHome['url'],
      'show' => $configHome['show'],
      'setupFavorite' => $this->isFavorite,
    ];
    $actions['home'] = $config ;
    $actions['details'] = $this->configBlock['config']['response']['postSuccess']['details'];
    $actions['home']['show'] = $utils->formatBoolean($actions['home']['show']);
    $actions['details']['show'] = $utils->formatBoolean($actions['details']['show']);
    $this->apiResponse
      ->getConfig()
      ->set('actions', $actions);
  }


}
