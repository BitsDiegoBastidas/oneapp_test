<?php

namespace Drupal\oneapp_mobile_premium_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_mobile_premium\Plugin\rest\resource\v2_0\PremiumProductDetailRestResource;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * {@inheritdoc}
 */
class PremiumProductDetailRestResourceGt extends PremiumProductDetailRestResource {

  /**
   * Returns config data. (Optional)
   *
   * @param array $data
   *   Additional data.
   */
  public function responseConfig($data = []) {

    $premium_utils_service = \Drupal::service('oneapp_mobile_premium.utils');
    $base_path = Url::fromUri('internal:/sites/default/files', ['absolute' => TRUE, 'https' => TRUE])->toString();
    $image_path_url = $base_path . '/' . $premium_utils_service::DIRECTORY_IMAGES;
    $this->apiResponse
      ->getConfig()
      ->set('imagePath', ['url' => $image_path_url]);

    if (isset($data['noData'])) {

      $action = $this->configBlock['actions']['empty']['buttons']['empty'];
      $action['show'] = (isset($action['show']) && $action["show"]) ? TRUE : FALSE;

      $image_empty = '';
      $config_image_url = $this->configBlock["configurations"]["imageEmpty"]["url"];

      if (isset($config_image_url[0])) {
        $image_empty = rawurlencode(File::load($config_image_url[0])->get('filename')->value);
      }

      $this->apiResponse
        ->getConfig()
        ->set('actions', ['empty' => $action])
        ->set('message', $this->configBlock["message"]["empty"]["label"])
        ->set('imageEmpty', $image_empty);
    }
    else {

      if ($data['status']['value']) {
        $actions = $this->configBlock['actions']['detail_active']['buttons'];
      }
      else {
        $actions = $this->configBlock['actions']['detail']['buttons'];
        if (!is_null($data['activate_label'])) {
          $actions['continue']['label'] = $data['activate_label'];
        }
      }
      $is_office = strtolower($data["productId"]["value"]) == 'office365';
      $pending_unsubscription = $data['pendingUnsubscription']['value'];
      foreach ($actions as $key => &$action) {
        $action['show'] = (isset($action['show']) && $action["show"]) ? TRUE : FALSE;
        if ($key == 'visit') {
          $visit_link = $this->service->getVisitLink($data['productId']['value']);
          $action['url'] = $visit_link;
          $action['show'] = $action['show'] && !empty($visit_link);
        }
        if ($key == 'disable' && $data['status']['value'] && ($pending_unsubscription || $is_office)) {
          $action['show'] = FALSE;
        }
        if ($key == 'send_license' && $is_office) {
          $action['show'] = $data['status']['value'];
        }
      }

      $this->apiResponse
        ->getConfig()
        ->set('actions', $actions)
        ->set('modals', $this->service->getModals());
    }
  }
}
