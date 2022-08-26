<?php

namespace Drupal\oneapp_home_services_gt\Services;

use Drupal\oneapp_home_services\Services\ServicesService;

/**
 * Class SchedulingService.
 */
class ServicesServiceGt extends ServicesService {
  /**
   * Retorna la información de la cuenta.
   *
   * @return object
   */
  public function getClientAccountGeneralInfo($id) {
    try {
      $query_params = [
        'searchType' => 'MSISDN',
        'documentType' => 1,
      ];

      $result = $this->manager
        ->load('oneapp_mobile_v2_0_client_account_general_info_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery($query_params)
        ->sendRequest();
      if (isset($result->TigoApiResponse->response)) {
        return $result->TigoApiResponse->response;
      }
      if (isset($result->TigoApiResponse->status) && $result->TigoApiResponse->status == 'ERROR') {
        return NULL;
      }
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Retorna la Información del paquete.
   *
   * @return object
   */
  public function getHomeBundleInfo($subscriber_id) {
    try {
      $result = $this->manager
        ->load('oneapp_home_services_v2_0_home_bundle_info_endpoint')
        ->setParams(['subscriberId' => $subscriber_id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();

      return $result;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Retorna Servicios complementarios.
   *
   * @return object
   */
  public function getHomeSupplementaryServices($msisdn) {
    try {
      $result = $this->manager
        ->load('oneapp_home_services_v2_0_home_supplementary_services_endpoint')
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();

      if (isset($result->message)) {
        return NULL;
      }

      return $result;

    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Retorna Servicios complementarios API.
   *
   * @return array
   */
  public function getPlanHomeDetailApi($plan_code) {
    return $this->manager
      ->load('oneapp_home_services_v2_0_plan_home_detail_endpoint')
      ->setParams(['planCode' => $plan_code])
      ->setHeaders([])
      ->setQuery([])
      ->sendRequest();
  }

}
