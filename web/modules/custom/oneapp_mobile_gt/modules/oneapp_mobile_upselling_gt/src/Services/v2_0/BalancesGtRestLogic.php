<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;

/**
 * Class BalancesRestLogic.
 */
class BalancesGtRestLogic {

  /**
   * Property to store configurations.
   *
   * @var mixed
   */
  protected $configBlock;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $utils;

  /**
   * Default mobile configuration.
   *
   * @var mixed
   */
  protected $mobileUtils;

  /**
   * Default API.
   *
   * @var mixed
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  public function __construct($manager, $utils, $mobileUtils) {
    $this->manager = $manager;
    $this->utils = $utils;
    $this->mobileUtils = $mobileUtils;
  }

  /**
   * Responds to setConfig.
   *
   * @param mixed $configBlock
   *   Config card or default.
   */
  public function setConfig($configBlock) {
    $this->configBlock = $configBlock;
  }

  /**
   * Responds to GET requests.
   *
   * @param string $msisdn
   *   Msisdn.
   * @param string $purchaseBalance
   *   Purchase balance.
   *
   * @return array
   *   bucketBalanceList
   *
   * @throws \ReflectionException
   */
  public function get($msisdn) {
    $balances = $this->getSanitizedBalances($msisdn);
    $response = [
      'coreBalance' => $balances['coreBalance'],
      'BucketsBalanceList' => $balances['BucketsBalanceList'],
    ];
    return $response;
  }


  public function getSanitizedBalances($msisdn) {
    $balances = $this->getBalances($msisdn)->balances;
    $result = [];
    $result['BucketsBalanceList'] = [];
    if (count($balances) > 0) {
      foreach ($balances as $balance) {
        if (!empty($balance->wallet)) {
          if ($balance->wallet == 'CORE BALANCE') {
            $result['coreBalance'] = [
              'value' => $balance->balanceAmount,
              'formattedValue' => $this->formatCurrency($balance->balanceAmount),
              'show' => true,
            ];
          }
          if (strpos($balance->wallet, 'PROMOTIONAL BALANCE') !== FALSE && !$this->expireDate($balance->expirationDate)) {
            $result['BucketsBalanceList'][] = [
              'name' => $this->getPropertyItem('name', $balance),
              'remainingAmount' => $this->getPropertyItem('remainingAmount', $balance),
              'endDateTime' => $this->getPropertyItem('endDateTime', $balance)
            ];
          }
        }
      }
    }
    return $result;
  }

  public function expireDate($date) {
    $expire_time = strtotime($date);
    $today_time = time();
    if ($expire_time < $today_time){
      return true;
    }
    return false;
  }

  public function getPropertyItem($type, $info) {
    $row = [];
    $fields = $this->configBlock['headerList']['fields'];
    switch ($type) {
      case 'name':
        $row = [
          'value' => 'Recarga',
          'formattedValue' => $info->description,
          'show' => (bool)$fields[$type]['show'],
          'label' => $fields[$type]['label'],
        ];
        break;
      case 'remainingAmount' :
        $row = [
          'value' =>  $info->balanceAmount,
          'formattedValue' => $this->formatCurrency($info->balanceAmount),
          'show' => (bool)$fields[$type]['show'],
          'label' => $fields[$type]['label'],
        ];
        break;
      case 'endDateTime' :
        $row = [
          'value' =>  $info->expirationDate,
          'formattedValue' => $this->getDateDiffLabel($info->expirationDate),
          'isDeliquent' => false,
          'show' => (bool)$fields[$type]['show'],
          'label' => $fields[$type]['label'],
        ];
        break;
    }
    return $row;
  }

  /**
   * Give format to value.
   *
   * @param string $value
   *   Value to be formatted.
   *
   * @return string
   *   Value formatted
   */
  public function formatCurrency($value) {
    $value = $this->utils->formatCurrency($value, true, true);
    $value = str_replace(' ', '', $value);
    $value = strtoupper($value);
    return $value;
  }


  public function getDateDiffLabel($endDate) {
    $timeEnd = strtotime($endDate);
    $diff = ($timeEnd - time()) / (24 * 60 * 60);
    $diff = intval($diff);
    $value = '';
    if ($diff > 0) {
      $value = $this->utils->formatRemainingTimeDayHour($endDate);
    }
    else {
      $value = $this->utils->formatRemainingTime($endDate);
    }
    return isset($value) ? $value : '';
  }

  /**
   * Get balances from tigo api.
   *
   * @param string $msisdn
   *   Msisdn to get balances.
   *
   * @return mixed
   *   Response of tigo api
   *
   * @throws \ReflectionException
   */
  protected function getBalances($msisdn) {
    // Get balances.
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_core_balance_endpoint')
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      $messages = $this->configBlock['messages'];
      $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
      $message = ($exception->getCode() == '404') ? $title . $messages['empty'] : $title . $messages['error'];
      $reflectedObject = new \ReflectionClass(get_class($exception));
      $property = $reflectedObject->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);
      throw $exception;
    }
  }

}
