<?php

namespace Drupal\oneapp_mobile_plans_gt\Services\v2_0;

use Drupal\Core\StreamWrapper\PublicStream;

class IcloudPromoRestLogic {

  /**
   * @var \Drupal\oneapp\Services\UtilsService
   */
  protected $utils;

  /**
   * @var \Drupal\oneapp_mobile\Services\UtilsService
   */
  protected $mobileUtils;

  /**
   * @var \Drupal\adf_simple_auth\Services\AdfCommonService
   */
  protected $adfCommonService;

  /**
   * @var \Drupal\oneapp_mobile_plans\Services\CurrentPlanServices
   */
  protected $currentPlanServices;

  protected $configBlock;

  public function __construct($utils, $mobile_utils, $adf_common_service, $current_plan_services) {
    $this->utils = $utils;
    $this->mobileUtils = $mobile_utils;
    $this->adfCommonService = $adf_common_service;
    $this->currentPlanServices = $current_plan_services;
  }

  public function setConfig($config) {
    $this->configBlock = $config;
  }

  /**
   * @param $id_type
   * @param $id
   * @return array
   * @throws \Exception
   */
  public function getData($id_type, $id) {
    $data = [
      'title' => [
        'value' => $this->configBlock['label'],
        'show' => ($this->configBlock['label_display'] === 'visible'),
      ],
      'subtitle' => [
        'value' => $this->configBlock['data']['subtitle']['value'],
        'show' => !empty($this->configBlock['data']['subtitle']['show']),
      ],
      'image' => [
        'url' => !empty($this->configBlock['data']['image']['url'][0])
          ? $this->currentPlanServices->getImageUrl($this->configBlock['data']['image']['url'][0])
          : '',
        'show' => !empty($this->configBlock['data']['image']['show']),
      ],
      'description' => [
        'value' => $this->configBlock['description'],
        'show' => !empty(strip_tags(trim($this->configBlock['description']))),
      ],
      'footer' => [
        'value' => $this->configBlock['footer'],
        'show' => !empty(strip_tags(trim($this->configBlock['footer']))),
      ],
    ];

    return $data;
  }

  /**
   * @param $data
   * @return array
   */
  public function getDataConfig($data) {

    $data_config = [];

    if (!empty($data['noData'])) {
      if ($data['noData']['value'] === 'hide' && !empty($this->configBlock['message']['hide']['show'])) {
        $data_config['message'] = $this->configBlock['message']['hide']['label'];
      }
      elseif ($data['noData']['value'] === 'empty' && !empty($this->configBlock['message']['empty']['show'])) {
        $data_config['message'] = $this->configBlock['message']['empty']['label'];
      }
    }
    else {
      $data_config['actions'] = $this->configBlock['actions'];
      foreach ($data_config['actions'] as $key => $action) {
        $data_config['actions'][$key]['show'] = !empty($data_config['actions'][$key]['show']);
      }
      $data_config['imagePath'] = [
        'url' => PublicStream::baseUrl() . '/' . $this->currentPlanServices::DIRECTORY_IMAGES,
      ];
    }

    return $data_config;
  }
}
