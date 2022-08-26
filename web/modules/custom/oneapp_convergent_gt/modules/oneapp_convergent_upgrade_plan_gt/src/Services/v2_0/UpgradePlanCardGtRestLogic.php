<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0;

use Drupal\oneapp_home_gt\Services\UtilsGtService;
use Drupal\oneapp_convergent_upgrade_plan\Services\UtilService;
use Drupal\oneapp_convergent_upgrade_plan\Services\v2_0\UpgradePlanCardRestLogic;
use Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradeServiceGt;

/**
 * Class UpgradePlanCardGtRestLogic.
 */
class UpgradePlanCardGtRestLogic extends UpgradePlanCardRestLogic {

  /**
   * @var UpgradeServiceGt
   */
  protected $service;
  /**
   * @var UtilsGtService
   */
  protected $utils;
  /**
   * @var UtilService
   */
  protected $upgradeUtils;

  /**
   * @param $billing_account_id
   * @return array
   */
  public function get($billing_account_id) {

    $ids = $this->utils->getInfoTokenByBillingAccountId($billing_account_id);
    $subscriber_id = $ids['subscriberId'] ?? '';

    $data = $this->service->getRecommendProductsData($subscriber_id, TRUE);
    $this->service->setConfig($this->configBlock);

    $data_card = [];

    if (!empty($data)) {

      $upgrade_plan_config = $this->configBlock['upgradePlan']['fields'];

      $fid = (!empty($upgrade_plan_config['banner']['url'][0])) ? $upgrade_plan_config['banner']['url'][0] : 0;

      $data_card['banner'] = [
        'url' => $this->upgradeUtils->getImageUrl($fid),

        'show' => (!empty($upgrade_plan_config['banner']['show'])) ? TRUE : FALSE,
      ];

      $title = (!empty($upgrade_plan_config['title']['value'])) ? $upgrade_plan_config['title']['value'] : '';

      $data_card['title'] = [
        'value' => $title,
        'show' => (!empty($upgrade_plan_config['title']['show'])) ? TRUE : FALSE,
      ];

      $data_card['description'] = (!empty($upgrade_plan_config['description']['value'])) ?
        $upgrade_plan_config['description']['value'] : '';
    }
    else {
      return [
        'noData' => [
          'value' => 'hide',
        ],
      ];
    }

    return [
      'planUpgrade' => $data_card,
    ];
  }
}
