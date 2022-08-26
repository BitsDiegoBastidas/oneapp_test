<?php
namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0;

use Drupal\oneapp_convergent_upgrade_plan\Services\v2_0\UpgradePlanMobileCardRestLogic;

class UpgradePlanMobileCardGtRestLogic extends UpgradePlanMobileCardRestLogic
{

  /**
   * Get all data plan card for api.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function get($id) {

    $lifecycle_status = NULL;

    $profiling_data  = NULL;

    $this->service->setConfig($this->configBlock);

    $data_dar = $this->utils->getInfoTokenByAccountId($id);

    if (isset($data_dar['msisdn'])) {
      $profiling_data = $this->service->getDetailsProfilingPlan($data_dar["msisdn"]);
    }

    if (isset($data_dar["lifecycle_status"])) {
      $lifecycle_status = $data_dar["lifecycle_status"];
    }

    if ($lifecycle_status == "active" && !empty($profiling_data->offers)) {
      $upgrade_plan_config = $this->configBlock['upgradePlanMobile']['fields'];

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
      return $this->utils->getEmptyState(TRUE);
    }

    return [
      'planUpgrade' => $data_card,
    ];
  }
}
