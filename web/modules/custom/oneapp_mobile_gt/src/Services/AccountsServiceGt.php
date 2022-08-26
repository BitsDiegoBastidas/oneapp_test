<?php

namespace Drupal\oneapp_mobile_gt\Services;

use Drupal\oneapp_mobile\Services\AccountsService;
use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\UnauthorizedHttpException;

/**
 * Class AccountsService.
 *
 * @package Drupal\oneapp_mobile_gt\Services;
 */
class AccountsServiceGt extends AccountsService {

  /**
   * {@inheritdoc}
   */
  public function getClientAccountGeneralInfo($clientId, $searchType = 'MSISDN', $documentType = 1, $informationToRetrieve = "") {
    if (!isset($clientId)) {
      $error = new ErrorBase();
      $error->getError()->set('message', 'The property msisdn does not exist in the provided request.');
      throw new UnauthorizedHttpException($error);
    }

    $queryParams = [
      'searchType' => $searchType,
      'documentType' => $documentType,
    ];
    try {
      $result = $this->manager
        ->load('oneapp_mobile_v2_0_client_account_general_info_endpoint')
        ->setParams(['id' => $clientId])
        ->setHeaders([])
        ->setQuery($queryParams)
        ->sendRequest(TRUE);
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
   * Override retrieveAccountInfo for GT.
   *
   * {@inheritdoc}
   */
  public function retrieveAccountInfo($clientId, $searchType = 'MSISDN', $documentType = 1, $informationToRetrieve = "") {
    try {
      $result = $this->getClientAccountGeneralInfo($clientId, $searchType, $documentType, $informationToRetrieve);
      if (isset($result->contracts->ContractType)) {
        $mobileUtils = \Drupal::service('oneapp.mobile.utils');
        $contractType = $result->contracts->ContractType;
        $contractTypeArray = !is_array($contractType) ? [$contractType] : $contractType;
        foreach ($contractTypeArray as $contract) {
          if (isset($contract->accounts->AssetType)) {
            $accounts = $contract->accounts->AssetType;
            if (!is_array($contract->accounts->AssetType)) {
              $accounts = [$contract->accounts->AssetType];
            }
            foreach ($accounts as &$value) {
              if (isset($value->msisdn) && trim($value->msisdn) == $mobileUtils->getFormattedMsisdn($clientId)) {
                $value->billingAccountId = isset($contract->profiles->ContractProfileType->billingId) ? $contract->profiles->ContractProfileType->billingId : '';
                if (isset($value->plans->PlanType->planType)) {
                  $value->billingType = $mobileUtils->getBillingType($value->plans->PlanType->planType);
                }
                return $value;
              }
            }
          }
        }
      }
    }
    catch (\Exception $e) {
      return [];
    }
    return [];
  }

  /**
   * GetPlanInfo.
   *
   * @param string $msisdn
   *   Número de linea a consultar.
   *
   * @return bool|mixed|null
   *   PlanInfo
   */
  public function getPlanInfo($msisdn) {
    $result = $this->getClientAccountGeneralInfo($msisdn, 'MSISDN', 1, 17);
    if (isset($result->contracts->ContractType)) {
      $contractTypeArray = $result->contracts->ContractType;
      $contractTypeArray = !is_array($contractTypeArray) ? [$contractTypeArray] : $contractTypeArray;
      foreach ($contractTypeArray as $contractType) {
        $accounts = $contractType->accounts->AssetType;
        if (!is_array($accounts)) {
          $accounts = [$accounts];
        }
        foreach ($accounts as $value) {
          if (isset($value->msisdn) && trim($value->msisdn) == $msisdn) {
            return @$value;
          }
        }
      }
    }
    return FALSE;
  }

  /**
   * GetAccountState.
   *
   * @return string
   *   AccountState
   */
  public function getAccountState($planInfo) {
    $mobileUtils = \Drupal::service('oneapp.mobile.utils');
    $config = $mobileUtils->getConfig('general', 'states', '');
    $estados = explode(PHP_EOL, $config);
    $estado = [];
    foreach ($estados as $item) {
      $parts = explode('|', $item);
      $estado[$parts[0]] = trim($parts[1]);
    }

    if (!empty($planInfo->accountState)) {
      $state = strtoupper($planInfo->accountState);
      if (isset($estado[$state])) {
        $state = $estado[$state];
      }
      else {
        $state = ucfirst(strtolower($state));
      }
    }
    else {
      $state = t('No disponible');
    }
    return $state;
  }

}
