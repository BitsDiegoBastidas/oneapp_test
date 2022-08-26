<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class DataBalanceRestLogic.
 */
class DataBalanceGtRestLogic {

  const BUCKET_TYPE_ALLOWED = 'data';
  const UNIT_BASE = 'Mb';
  const KB = 1024;
  const MB = 1024 * 1024;
  const GB = 1024 * 1024 * 1024;


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
   * Default API.
   *
   * @var mixed
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  public function __construct($manager, $utils) {
    $this->manager = $manager;
    $this->utils = $utils;
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
   *bucketList = \Drupal::config('oneapp_mobile_upselling.v2_0.packets_order_details_rest_logic')->get($msisdn);
   * @return array
   *   The response to summary configurations.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   * @throws \Exception
   *   Throws exception expected.
   */
  public function get($msisdn) {
    $config = $this->configBlock['summary']['fields'];
    $quota_info = $this->getQuotaInfoSanitize($msisdn);
    $arrData = [];
    $arrConfig = [];
    if (!empty($quota_info)) {
      $summaryRemainingValue = $quota_info['availableQuotaMbytes'];
      $summaryReservedAmount = $quota_info['totalQuotaMbytes'];
      $summaryUsedValue = $quota_info['actualQuotaMbytes'];
      $description = $quota_info['description'];
      $arrData = [
        'description' => [
          'value' => isset($description) ? $description : "",
          'formattedValue' => isset($description) ? $description : "",
          'label' => $config['description']['label'],
          'show'  => (bool) $config['description']['show'],
        ],
        'summaryRemainingValue' => [
          'value' => isset($summaryRemainingValue) ? $summaryRemainingValue : 0,
          'formattedValue' => $this->formatData($summaryRemainingValue),
          'label' => $config['remainingValue']['label'],
          'show'  => (bool) $config['remainingValue']['show'],
        ],
        'summaryReservedAmount' => [
          'value' => isset($summaryReservedAmount) ? $summaryReservedAmount : 0,
          'formattedValue' => $this->formatData($summaryReservedAmount),
          'label' => $config['reservedAmount']['label'],
          'show'  => (bool) $config['reservedAmount']['show'],
        ],
        'summaryUsedValue' => [
          'value' => $summaryUsedValue,
          'formattedValue' => $this->formatData($summaryUsedValue),
          'label' => $config['usedValue']['label'],
          'show'  => (bool) $config['usedValue']['show'],
        ],
        'summaryDateValue' => [
          'value' => $quota_info['endDate'],
          'formattedValue' => $this->getDateDiffLabel($quota_info['endDate']),
          'label' => $config['dateValue']['label'],
          'show'  => (bool) $config['dateValue']['show'],
        ],
      ];
      $arrConfig = [
        'description' => $quota_info['description'],
      ];
      $result = [
        'data' => $arrData,
        'config' => $arrConfig,
      ];
    }
    else {
      $result = [
        'data' => [
          'noData' => [
            'value' => 'empty',
          ]
        ],
        'config' => [
          'message_empty' => $this->configBlock['message']['empty']['label'],
        ],
      ];
    }

    return $result;
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
  public function formatData($value) {
    if (!isset($value))
      return '';
    return strtoupper($this->utils->formatData($value, self::UNIT_BASE));
  }

  /**
   * Get sanitize array value of internet balance.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   The quota info array.
   *
   * @throws \Exception
   *   Throws exception expected.
   */

  public function getQuotaInfoSanitize($msisdn) {
    $quota_info = $this->getQuotaInfo($msisdn);
    $result = [];
    if ($quota_info->clientType == 'PREPAID') {
      if (isset($quota_info->currentPack)) {
        $prepaid_info = $quota_info->currentPack;
        if (isset($prepaid_info)) {
          $result['availableQuotaMbytes'] = $prepaid_info->availableQuotaMbytes;
          $result['totalQuotaMbytes'] = $prepaid_info->totalQuotaMbytes;
          $result['actualQuotaMbytes'] = $prepaid_info->actualQuotaMbytes;
          $result['endDate'] = $prepaid_info->expirationDate;
          $result['description'] = $prepaid_info->description;
        }
      }
      return $result;
    }
    elseif ($quota_info->clientType == 'POSTPAID') {
      if (isset($quota_info->postpaidPlan)) {
        $postpaid_info = $quota_info->postpaidPlan;
        $result['availableQuotaMbytes'] = $postpaid_info->availableQuotaMbytes;
        $result['totalQuotaMbytes'] = $postpaid_info->totalQuotaMbytes;
        $result['actualQuotaMbytes'] = $postpaid_info->actualQuotaMbytes;
        $result['endDate'] = $postpaid_info->nextResetDate;
        $result['description'] = $postpaid_info->description;
      }
      return $result;
    }
  }

  /**
   * devuelve string con la diferencia de fechas entre la actual y el dia que se pasa por parametro
   *
   * @param string $endDate
   *   endDate.
   *
   * @return string
   *   The date in string format.
   *
   */

  public function getDateDiffLabel($endDate) {
    $timeEnd = strtotime($endDate);
    $diff = ($timeEnd - time()) / (24 * 60 * 60);
    $diff = intval($diff);
    if ($diff > 0) {
      return $this->utils->formatRemainingTimeDayHour($endDate);
    }
    else {
      return $this->utils->formatRemainingTime($endDate);
    }
  }

  /**
   * Get  array value of internet balance consuming apigee service.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return object
   *   The quota info object.
   *
   * @throws \Exception
   *   Throws exception expected.
   */
  public function getQuotaInfo($msisdn) {
    try {
      $data_balance_service = \Drupal::service('oneapp_mobile_upselling.service.data_balance');
      return $data_balance_service->getDataBalance($msisdn);
    }
    catch (\Exception $exception) {
      $messages = $this->configBlock['messages'];
      $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
      $message = ($exception->getCode() == '404') ? $title . $messages['empty'] : $title . $messages['error'];

      $reflectedObject = new \ReflectionClass(get_class($exception));

      $property = $reflectedObject->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      $propertyFile = $reflectedObject->getProperty('file');
      $propertyFile->setAccessible(TRUE);
      $propertyFile->setValue($exception, '');
      $propertyFile->setAccessible(FALSE);

      $propertyLine = $reflectedObject->getProperty('line');
      $propertyLine->setAccessible(TRUE);
      $propertyLine->setValue($exception, 0);
      $propertyLine->setAccessible(FALSE);

      throw $exception;
    }

  }

}
