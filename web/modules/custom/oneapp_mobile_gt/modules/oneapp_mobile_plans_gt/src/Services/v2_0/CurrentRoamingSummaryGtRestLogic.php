<?php


namespace Drupal\oneapp_mobile_plans_gt\Services\v2_0;


use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\oneapp_mobile_plans\Services\v2_0\CurrentRoamingSummaryRestLogic;

class CurrentRoamingSummaryGtRestLogic extends CurrentRoamingSummaryRestLogic {

  /**
   * @param $data
   * @return array
   */
  public function getDataConfig($data = []) {

    $title_value = (!empty($this->configBlock['label'])) ? $this->configBlock['label'] : '';
    $title_show = (!empty($this->configBlock['label_display'])) ? $this->configBlock['label_display'] : '';
    $title_class = (!empty($this->configBlock['data']['titleClass'])) ? $this->configBlock['data']['titleClass']['value'] : '';

    $data_config['title'] = [
      'value' => $title_value,
      'class' => $title_class,
      'show' => ($title_show === 'visible') ? TRUE : FALSE,
    ];

    $data_config['actions'] = $this->configBlock['actions'];
    foreach ($data_config['actions'] as $key => $action) {
      $data_config['actions'][$key]['show'] = !empty($data_config['actions'][$key]['show']);
      if ($key == 'purchase') {
        $url = $data_config['actions'][$key]['url'];
        $data_config['actions'][$key]['url'] = $url;
      }
    }

    if (!empty($data['noData'])) {
      if ($data['noData']['value'] === 'hide' && !empty($this->configBlock['message']['hide']['show'])) {
        $data_config['message'] = $this->configBlock['message']['hide']['label'];
      }
      elseif ($data['noData']['value'] === 'empty' && !empty($this->configBlock['message']['empty']['show'])) {
        $data_config['message'] = $this->configBlock['message']['empty']['label'];
      }
      unset($data_config['actions']['scope']);
    }
    else {
      $data_config['imagePath'] = [
        'url' => PublicStream::baseUrl() . '/' . $this->currentPlanService::DIRECTORY_IMAGES
      ];
    }

    return $data_config;
  }
}
