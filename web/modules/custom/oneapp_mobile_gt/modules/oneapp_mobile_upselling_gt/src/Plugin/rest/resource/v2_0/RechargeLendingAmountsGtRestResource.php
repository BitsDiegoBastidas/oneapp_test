<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_mobile_upselling\Plugin\rest\resource\v2_0\RechargeLendingAmountsRestResource;

class RechargeLendingAmountsGtRestResource extends RechargeLendingAmountsRestResource {

  /**
   * {@inheritdoc}
   */
  public function responseConfig($data) {
    $imageManagerConfig = \Drupal::config('oneapp.image_manager.config')->get('image_manager_fields');
    $url = $imageManagerConfig['public_url'];
    $message = $this->configBlock['message'];
    $config = $data['config'];
    $data = $data['data'];
    if (isset($data['noData'])) {
      $response = [
        'label' => $message[$data['noData']['value']]['label'],
        'show' => (bool) $message[$data['noData']['value']]['show'],
      ];
      $this->apiResponse
        ->getConfig()
        ->set('message', $response);
    }
    else {
      if (isset($config['action'])) {
        $this->apiResponse
          ->getConfig()
          ->set('actions', $config['action']);
      }
      if (isset($config['loanLabel'])) {
        $this->apiResponse
          ->getConfig()
          ->set('loanLabel', $config['loanLabel']);
      }
      if (!empty($url)) {
        $this->apiResponse
          ->getConfig()
          ->set('imagePath', $url);
      }
    }
  }

}
