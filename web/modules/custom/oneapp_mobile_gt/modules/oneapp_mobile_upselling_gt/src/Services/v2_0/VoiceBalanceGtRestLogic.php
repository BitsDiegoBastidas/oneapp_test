<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\VoiceBalanceRestLogic;

/**
 * Class VoiceBalanceRestLogic.
 */
class VoiceBalanceGtRestLogic extends VoiceBalanceRestLogic {

  public $url;

  /**
   * Responds to GET requests.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   The associative array.
   *
   * @throws \ReflectionException
   */
  public function get($msisdn) {

    $block_configs = $this->configBlock['voiceBalance'];
    $get_info_token_by_msisdn = $this->utilsMobile->getInfoTokenByMsisdn($msisdn);
    $billing_type = (!empty($get_info_token_by_msisdn['billingType'])) ?
      $get_info_token_by_msisdn['billingType'] : '';

    if (!empty($billing_type)) {
      $this->url = ($billing_type === "hybrid" || $billing_type === "postpaid") ?
        'oneapp_mobile_upselling_v2_0_voice_balance_post_endpoint' :
        'oneapp_mobile_upselling_v2_0_voice_balance_endpoint';
    } else {
      $type_account = $this->getBalance($msisdn)->typeClient;
      $this->url = ($type_account === "CREDITO" || $type_account === "STAFF DE COMCEL" || $type_account === "FACTURA FIJA") ?
        'oneapp_mobile_upselling_v2_0_voice_balance_post_endpoint' :
        'oneapp_mobile_upselling_v2_0_voice_balance_endpoint';
    }

    $data_voice  = $this->getBuckets($msisdn);

    if (isset($data_voice->balances)) {
      $response = $this->strutureDateVoicePre($data_voice->balances, $block_configs);;
      return empty($response) ? $this->noData() : ['voiceBalance' => $response];
    }
    elseif (isset($data_voice->consumption)) {
      $response = $this->strutureDateVoicePost($data_voice, $block_configs, $msisdn);
      return empty($response) ? $this->noData() : ['voiceBalance' => $response];
    }
    else {
      $response = $this->noData();
    }

    return $response;
  }

  public function strutureDateVoicePre($data_voice, $block_configs) {
    $response = array();
    $rows_voice = [];

    foreach ($data_voice as $voice) {

      if ($voice->unit === "SEGUNDOS") {

        $rows_voice['unlimited'] = [
          'value' => false,
        ];

        $rows_voice['isActive'] = [
          'value' => true
        ];

        $rows_voice['showBar'] = [
          'value' => false
        ];

        $rows_voice['bucketsId'] = [
          'label' => $block_configs['bucketsId']['label'],
          'show'  => ($block_configs['bucketsId']['show']) ? TRUE : FALSE,
          'value' => $voice->wallet,
          'formattedValue' => $voice->wallet,
        ];

        $rows_voice['friendlyName'] = [
          "label" => $block_configs['friendlyName']['label'],
          "show" => ($block_configs['friendlyName']['show']) ? TRUE : FALSE,
          "value" => $voice->description,
          "formattedValue" => $voice->description
        ];

        $remaining_value = $voice->balanceAmount;

        $rows_voice['remainingValue'] = [
          "label" => $block_configs['remainingValue']['label'],
          "show" => ($block_configs['remainingValue']['show']) ? TRUE : FALSE,
          "value" => $remaining_value,
          "formattedValue" => round($remaining_value / 60) . " " . $block_configs['remainingValue']['description']
        ];

        $rows_voice['reservedAmount'] = [
          "label" => '',
          "show" => false,
          "value" => 0,
          "formattedValue" => ""
        ];

        $rows_voice['endDateTime'] = [
          "label" => $block_configs['endDateTime']['label'],
          "show" => ($block_configs['endDateTime']['show']) ? TRUE : FALSE,
          "value" => [
            "startDate" => date(DATE_ATOM, time()),
            "endDateTime" => $voice->expirationDate
          ],
          "formattedValue" => strftime(date(DATE_ATOM, time())) > strftime($voice->expirationDate) ? 'Vencido' :
            $block_configs['endDateTime']['prefix'] . " " . $this->formatDateTime(date(DATE_ATOM, time()), $voice->expirationDate)
        ];
        array_push($response, $rows_voice);
      }
    }

    return $response;
  }

  public function strutureDateVoicePost($data_voice, $block_configs, $msisdn) {
    $rows_voice = [];
    $config = \Drupal::config('oneapp.config')->getRawData();
    $flag_has_wallets = FALSE;

    foreach ($data_voice->consumption as $voice) {
      if ($voice->unit === 'MINUTOS' || $voice->unit === "QUETZALES") {
        if ($voice->limit === 0) {
           continue;
        }
        $flag_has_wallets = TRUE;
        if ($voice->type === 'VOZ EXNET') {
          $suffix = 'Exnet';
        }
        elseif ($voice->type === 'VOZ ONNET') {
          $suffix = 'Onnet';
        }
        elseif (in_array($voice->type, ['VOZ ROAMIN', 'VOZ ROAMING IN'])) {
          $suffix = 'RoamIn';
        }
        elseif (in_array($voice->type, ['VOZ ROAMOUT', 'VOZ ROAMING OUT'])) {
          $suffix = 'RoamOut';
        }
        else {
          continue;
        }

        $rows_voice[$suffix]['unlimited'] = [
          'value' => $voice->limit >= $this->configBlock['config']['postpaid']['limit' . $suffix] ? true : false,
        ];

        $rows_voice[$suffix]['isActive'] = [
          'value' => true
        ];

        $rows_voice[$suffix]['showBar'] = [
          'value' => true
        ];

        $rows_voice[$suffix]['bucketsId'] = [
          'label' => $block_configs['bucketsId']['label'],
          'show'  => ($block_configs['bucketsId']['show']) ? TRUE : FALSE,
          'value' => $voice->type,
          'formattedValue' => $voice->type,
        ];

        $rows_voice[$suffix]['friendlyName'] = [
          "label" => $block_configs['friendlyName']['label'],
          "show" => ($block_configs['friendlyName']['show']) ? TRUE : FALSE,
          "value" => $this->configBlock['config']['postpaid']['formattedValue' . $suffix],
          "formattedValue" => $this->configBlock['config']['postpaid']['formattedValue' . $suffix]
        ];

        $remaining_value = $voice->used;
        if ($voice->limit < $this->configBlock['config']['postpaid']['limit' . $suffix]) {
          $remaining_value = $voice->limit - $voice->used;
          if ($remaining_value < 0) {
            $remaining_value = 0;
          }
        }

        $rows_voice[$suffix]['remainingValue'] = [
          "label" => $voice->limit >= $this->configBlock['config']['postpaid']['limit' . $suffix] ?
            $block_configs['reserveUsed']['label'] : $block_configs['remainingValue']['label'],
          "show" => ($block_configs['remainingValue']['show']) ? TRUE : FALSE,
          "value" => $remaining_value,
          "formattedValue" => $remaining_value . " " . $block_configs['remainingValue']['description']
        ];

        $rows_voice[$suffix]['reservedAmount'] = [
          "label" => $block_configs['reservedAmount']['label'],
          "show" => ($block_configs['reservedAmount']['show']) ? TRUE : FALSE,
          "value" => $voice->limit,
          "formattedValue" => $voice->limit >= $this->configBlock['config']['postpaid']['limit' . $suffix] ?
            $this->configBlock['config']['messages']['unlimitedBucket'] :
            $voice->limit . " " . $block_configs['reservedAmount']['description']
        ];

        if ($voice->unit === "QUETZALES") {
          $used = $voice->limit - $voice->used;
          $rows_voice[$suffix]['remainingValue'] = [
            "label" => $voice->limit >= $this->configBlock['config']['postpaid']['limit' . $suffix] ?
              $block_configs['reserveUsed']['label'] : $block_configs['remainingValue']['label'],
            "show" => ($block_configs['remainingValue']['show']) ? TRUE : FALSE,
            "value" => $used,
            "formattedValue" => $config['currency']['local_sign'] . $used
          ];

          $rows_voice[$suffix]['reservedAmount'] = [
            "label" => $block_configs['reservedAmount']['label'],
            "show" => ($block_configs['reservedAmount']['show']) ? TRUE : FALSE,
            "value" => $voice->limit,
            "formattedValue" => $config['currency']['local_sign'] . $voice->limit
          ];
        }

        $rows_voice[$suffix]['endDateTime'] = [
          "label" => $block_configs['endDateTime']['label'],
          "show" => ($block_configs['endDateTime']['show']) ? TRUE : FALSE,
          "value" => [
            "startDate" => $data_voice->periodStart,
            "endDateTime" => $data_voice->periodEnd
          ],
          "formattedValue" => $block_configs['endDateTime']['prefix'] . " " .
            $this->formatDateTime(date(DATE_ATOM, time()), $data_voice->periodEnd)
        ];
      }
    }
    $response = array_values($rows_voice);

    if (!$flag_has_wallets) {
      array_push($response, $this->noData());
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
    }
    catch (\Exception $e) {
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
          $date_formatted = $amount . $days_suffix . ", " . $diff->h . $suffix_hours;
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
  protected function getBuckets($msisdn)
  {
    try {

      return $this->manager
        ->load($this->url)
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
    } catch (HttpException $exception) {
      $messages = $this->configBlock['config']['messages'];
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

  public function getBalance($msisdn)
  {
    $config = $this->configBlock;
    $message_error = $config['config']['messages'];
    try {

      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    } catch (HttpException $exception) {
      $message = $message_error;
      $reflected_object = new \ReflectionClass(get_class($exception));
      $property = $reflected_object->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      throw $exception;
    }
  }
}
