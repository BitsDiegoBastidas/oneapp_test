<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\SmsBalanceRestLogic;

/**
 * Class SmsBalanceGtRestLogic.
 */
class SmsBalanceGtRestLogic extends  SmsBalanceRestLogic {

  public $url;

  /**
   * Responds to GET requests.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   The Array of buckets.
   *
   * @throws \ReflectionException
   */
  public function get($msisdn) {
    $block_configs = $this->configBlock['smsBalance']['fields'];
    $get_info_token_by_msisdn = $this->utilsMobile->getInfoTokenByMsisdn($msisdn);
    $billing_type = (!empty($get_info_token_by_msisdn['billingType'])) ?
      $get_info_token_by_msisdn['billingType'] : '';

    if (!empty($billing_type)) {
      $this->url = ($billing_type === "hybrid" || $billing_type === "postpaid") ?
        'oneapp_mobile_upselling_v2_0_sms_balance_gt_postpaid' :
        'oneapp_mobile_upselling_v2_0_sms_balance';
    }
    else {
      $type_account = $this->getBalance($msisdn)->typeClient;
      $this->url = ($type_account === "CREDITO" || $type_account === "STAFF DE COMCEL" || $type_account === "FACTURA FIJA") ?
        'oneapp_mobile_upselling_v2_0_sms_balance_gt_postpaid' :
        'oneapp_mobile_upselling_v2_0_sms_balance';
    }

    $data_sms  = $this->getBuckets($msisdn);
    if (isset($data_sms->balances)) {
      $response = $this->strutureDateSmsPre($data_sms->balances, $block_configs);
      return empty($response)? $this->noData():['smsBalance' => $response];
    }
    elseif (isset($data_sms->consumption)) {
      $response = $this->structureDateSmsPost($data_sms, $block_configs);
      return empty($response)? $this->noData():['smsBalance' => $response];
    }
    else {
      $response = $this->noData();
    }

    return $response;
  }

  public function strutureDateSmsPre($data_sms, $block_configs) {
    $response = array();
    $rows_sms = [];

    foreach ($data_sms as $sms) {

      if ($sms->unit === "UNIDADES") {

        $rows_sms['unlimited'] = [
          'value' => false,
        ];

        $rows_sms['isActive'] = [
          'value' => true
        ];

        $rows_sms['showBar'] = [
          'value' => false
        ];

        $rows_sms['bucketsId'] = [
          'label' => $block_configs['bucketsId']['label'],
          'show'  => ($block_configs['bucketsId']['show']) ? TRUE : FALSE,
          'value' => $sms->wallet,
          'formattedValue' => $sms->wallet,
        ];

        $rows_sms['friendlyName'] = [
          "label" => $block_configs['friendlyName']['label'],
          "show" => ($block_configs['friendlyName']['show']) ? TRUE : FALSE,
          "value" => $sms->description,
          "formattedValue" => $sms->description
        ];

        $remaining_value = $sms->balanceAmount;
        $rows_sms['remainingValue'] = [
          "label" => $block_configs['remainingValue']['label'],
          "show" => ($block_configs['remainingValue']['show']) ? TRUE : FALSE,
          "value" => $remaining_value,
          "formattedValue" => $remaining_value . " " . $block_configs['remainingValue']['description']
        ];

        $rows_sms['reservedAmount'] = [
          "label" => '',
          "show" => false,
          "value" => 0,
          "formattedValue" => ""
        ];

        $rows_sms['endDateTime'] = [
          "label" => $block_configs['endDateTime']['label'],
          "show" => ($block_configs['endDateTime']['show']) ? TRUE : FALSE,
          "value" => [
            "startDate" => date(DATE_ATOM, time()),
            "endDateTime" => $sms->expirationDate
          ],
          "formattedValue" => strftime(date(DATE_ATOM, time())) > strftime($sms->expirationDate) ? 'Vencido' :
            $block_configs['endDateTime']['prefix'] . " " . $this->formatDateTime(date(DATE_ATOM, time()), $sms->expirationDate)
        ];
        array_push($response, $rows_sms);
      }
    }

    return $response;
  }

  public function structureDateSmsPost($data_sms, $block_configs) {
    $response = [];
    $rows_sms = [];

    foreach ($data_sms->consumption as $sms) {
      if ($sms->unit === 'SMS') {

        $rows_sms['unlimited'] = [
          'value' => $sms->limit >= $this->configBlock['postpaid']['limit'] ? true : false,
        ];

        $rows_sms['isActive'] = [
          'value' => true
        ];

        $rows_sms['showBar'] = [
          'value' => true
        ];

        if ($sms->type == "SMS ROAMING") {
          $friendly_name_summary = [
            "label" => $block_configs['friendlyName']['label'],
            "show" => ($block_configs['friendlyName']['show']) ? TRUE : FALSE,
            "value" => $this->configBlock['postpaid']['formattedValueRoam'],
            "formattedValue" => $this->configBlock['postpaid']['formattedValueRoam']
          ];
        }
        else {
          $friendly_name_summary = [
            "label" => $block_configs['friendlyName']['label'],
            "show" => ($block_configs['friendlyName']['show']) ? TRUE : FALSE,
            "value" => $this->configBlock['postpaid']['formattedValue'],
            "formattedValue" => $this->configBlock['postpaid']['formattedValue']
          ];
        }

        $rows_sms['bucketsId'] = [
          'label' => $block_configs['bucketsId']['label'],
          'show'  => ($block_configs['bucketsId']['show']) ? TRUE : FALSE,
          'value' => $sms->type,
          'formattedValue' => $sms->type,
        ];

        $rows_sms['friendlyName'] = $friendly_name_summary;

        $remaining_value = $sms->used;
        if ($sms->limit < $this->configBlock['postpaid']['limit']) {
          $remaining_value = $sms->limit - $sms->used;
          if ($remaining_value < 0) {
            $remaining_value = 0;
          }
        }
        $rows_sms['remainingValue'] = [
          "label" => $sms->limit >= $this->configBlock['postpaid']['limit'] ? $block_configs['reserveUsed']['label'] : $block_configs['remainingValue']['label'],
          "show" => ($block_configs['remainingValue']['show']) ? TRUE : FALSE,
          "value" => $remaining_value,
          "formattedValue" => $remaining_value . " " . $block_configs['remainingValue']['description']
        ];

        $rows_sms['reservedAmount'] = [
          "label" => $block_configs['reservedAmount']['label'],
          "show" => ($block_configs['reservedAmount']['show']) ? TRUE : FALSE,
          "value" => $sms->limit,
          "formattedValue" => $sms->limit >= $this->configBlock['postpaid']['limit'] ?
            $this->configBlock['messages']['unlimitedBucket'] : $sms->limit . " " . $block_configs['reservedAmount']['description']
        ];

        $rows_sms['endDateTime'] = [
          "label" => $block_configs['endDateTime']['label'],
          "show" => ($block_configs['endDateTime']['show']) ? TRUE : FALSE,
          "value" => [
            "startDate" => $data_sms->periodStart,
            "endDateTime" => $data_sms->periodEnd,
          ],
          "formattedValue" => $block_configs['endDateTime']['prefix'] . " " . $this->formatDateTime(date(DATE_ATOM, time()), $data_sms->periodEnd)
        ];
        array_push($response, $rows_sms);
      }
    }

    return $response;
  }

  public function noData() {
    $response = $rows_voice['noData'] = [
      'value' => "empty",
    ];
    return ['noData' => $response];
  }

  public function formatDateTime($init_date, $end_date) {

    $date_formatted = "";

    try {
      $end_date = new \DateTime($end_date);
    } catch (\Exception $e) {
    }

    $start_date = new \DateTime($init_date, new \DateTimeZone($end_date->getTimezone()->getName()));

    if ($start_date->getTimestamp() < $end_date->getTimestamp()) {

      $diff = $end_date->diff($start_date);

      // Horas mas minutos.
      $minutes = $minutes_diff = ($diff->h * 60) + $diff->i;
      $hours = (($diff->days === 0) ? 0 : $diff->days * 24) + $diff->h;

      // Calculate date difference.
      if ($diff->days > 0) {
        $minutes = ($diff->days * 24 * 60) + $minutes;
      }

      // Config suffixes.
      if ($diff->days > 1) {
        $days_suffix = " dias";
      } else {
        $days_suffix = ($minutes_diff >= 720) ? " dias" : " dia";
      }
      $suffix_hours = $hours > 1 ? " horas " : " hora ";
      $suffix_minutes = $minutes > 1 ? " minutos" : " minuto";

      switch (TRUE) {

        // XX días XX horas.
        case $minutes > 2880:
          $amount = ($minutes_diff >= 720) ? $diff->days : $diff->days;
          $date_formatted = $amount . $days_suffix .", ". $diff->h . $suffix_hours;
          break;

         // XX horas.
        case $minutes <= 2880 && $minutes >= 180:
          $date_formatted = $hours . rtrim($suffix_hours);
          break;

          // XX horas, XX minutos.
        case $minutes <= 180 && $minutes >= 60:
          $date_formatted = $diff->h . $suffix_hours . $diff->i . $suffix_minutes;
          break;

          // XX  minutos.
        case $minutes < 60:
          $date_formatted = $diff->i . $suffix_minutes;
          break;
      }
      return $date_formatted;
    }
    return NULL;
  }

  /**
   * Implements getBuckets.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return object
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   * @throws \ReflectionException
   */
  protected function getBuckets($msisdn) {
    try {
      return $this->manager
        ->load($this->url)
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
    } catch (HttpException $exception) {
      $messages = $this->configBlock['messages'];
      $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
      $message = ($exception->getCode() == '404') ? $title . $messages['empty'] : $title . $messages['error'];

      $reflected_object = new \ReflectionClass(get_class($exception));
      $property = $reflected_object->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      throw $exception;
    }
  }

  public function getBalance($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      $reflected_object = new \ReflectionClass(get_class($exception));
      $property = $reflected_object->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $this->configBlock['messages']['error']);
      $property->setAccessible(FALSE);
      throw $exception;
    }
  }

}
