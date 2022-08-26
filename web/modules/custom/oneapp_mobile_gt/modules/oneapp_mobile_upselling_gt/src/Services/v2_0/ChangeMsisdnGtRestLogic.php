<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\ChangeMsisdnRestLogic;

/**
 * Class ChangeMsisdnGtRestLogic.
 */
class ChangeMsisdnGtRestLogic extends ChangeMsisdnRestLogic {
  const NUMBER_ERROR = 'number_error';

  /**
   * Responds to post requests.
   *
   * @param string $msisdn
   *   Client Number (Msisdn).
   * @param string $targetMsisdn
   *   Target Msisdn.
   * @param string $type
   *   Type.
   *
   * @return mixed
   *   mixed
   *
   * @throws \ReflectionException
   */
  public function getStatus($msisdn, $targetMsisdn, $type) {
    $errorType = ($type === 'recharge') ? 'recharge_error' : 'packages_error';
    $successType = ($type === 'recharge') ? 'recharge_success' : 'packages_success';

    // Get balance msisdn.
    $balanceMsisdn = $this->getBalance($msisdn);
    // If it is not postpaid and transaction successfully.

    if ($balanceMsisdn->responseMessage === 'Transaccion exitosa') {
      // Get balance to target msisdn.
      $balanceTarget = $this->getBalance($targetMsisdn);
      if ($balanceTarget->responseMessage === 'Transaccion exitosa') {
        switch ($type) {
          case 'packets':
            if ($balanceTarget->typeClient === 'PREPAGO' || $balanceTarget->typeClient === 'KIT') {
              return $this->successResult($targetMsisdn, $successType);
            }
            return $this->errorResult($errorType);

          case 'recharge':
            if ($balanceTarget->typeClient === 'PREPAGO' || $balanceTarget->typeClient === 'KIT' || $balanceTarget->typeClient === 'FACTURA FIJA') {
              return $this->successResult($targetMsisdn, $successType);
            }
            return $this->errorResult($errorType);
        }
      }
    }
    return $this->errorResult(self::NUMBER_ERROR);
  }

  /**
   * Format array validate msisdn.
   *
   * @param string $type
   *   Message type.
   *
   * @return array
   *   Formatted Array.
   */
  private function errorResult($type) {
    return [
      'result' => [
        'value' => FALSE,
        'formattedValue' => $this->configBlock['messages'][$type]['label'],
        'show' => (bool) $this->configBlock['messages'][$type]['show'],
      ],
    ];
  }

  /**
   * Format array validate msisdn.
   *
   * @param string $msisdn
   *   Msisdn.
   * @param string $type
   *   Message type.
   *
   * @return array
   *   Formatted Array.
   */
  private function successResult($msisdn, $type) {
    return [
      'result' => [
        'value' => TRUE,
        'formattedValue' => $this->configBlock['messages'][$type]['label'] . ' ' . $msisdn,
        'show' => (bool) $this->configBlock['messages'][$type]['show'],
      ],
    ];
  }

  /**
   * Implements getCurrentPlan.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return mixed
   *   Msisdn value.
   *
   * @throws \ReflectionException
   */
  private function getBalance($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      $this->errorResult(self::NUMBER_ERROR);
    }
  }

}
