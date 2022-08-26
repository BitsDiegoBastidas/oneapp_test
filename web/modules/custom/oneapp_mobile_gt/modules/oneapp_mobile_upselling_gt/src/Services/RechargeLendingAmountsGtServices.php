<?php


namespace Drupal\oneapp_mobile_upselling_gt\Services;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\RechargeLendingAmountsServices;

class RechargeLendingAmountsGtServices extends RechargeLendingAmountsServices {


    /**
   * {@inheritdoc}
   */
  public function getLoan($msisdn) {
    return $this->manager
    ->load('oneapp_mobile_upselling_v2_0_available_offers_endpoint')
    ->setHeaders([])
    ->setQuery([])
    ->setParams(['msisdn' => $msisdn])
    ->sendRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance($msisdn) {
    return $this->manager
    ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
    ->setHeaders([])
    ->setQuery([])
    ->setParams(['msisdn' => $msisdn])
    ->sendRequest();
  }

}
