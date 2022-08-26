<?php

namespace Drupal\oneapp_mobile_gt\Services;

use Drupal\oneapp_mobile\Services\UtilsService;
use Drupal\oneapp\Exception\HttpException;

/**
 * Class UtilsServiceGt.
 */
class UtilsServiceGt extends UtilsService {

  /**
   * Validate if Msisdn is prepaid.
   *
   * @param mixed $msisdn
   *   Msisdn to validate.
   *
   * @return bool
   *   Return true or false.
   */
  public function isPrepaid($msisdn) {
    try {
      $manager = \Drupal::service('oneapp_endpoint.manager');
      $accounts = $manager
        ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
      return ($accounts->typeClient == 'PREPAGO' || $accounts->typeClient == 'KIT') ? TRUE : FALSE;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Return if account type is primarySubscriberId.
   * @param string $msisdn
   * @return boolean
   */
  public function isPrimarySubscriberId($msisdn) {
    $global_settings = \Drupal::config('oneapp_endpoints.settings')->getRawData();
    $mobile_settings = \Drupal::config('oneapp_mobile.config')->get('general');
    if (strlen($msisdn) > $mobile_settings['msisdn_lenght'] &&
      strpos($msisdn, $global_settings['prefix_country']) === 0) {
      return TRUE;
    }
    return FALSE;
  }

}
