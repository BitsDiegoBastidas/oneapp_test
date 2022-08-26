<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\Core\Database\Database;
use Drupal\oneapp_mobile_upselling\Services\v2_0\DataBalanceDetailRestLogic;

/**
 * Class DataBalanceDetailRestLogic.
 */
class DataBalanceDetailGtRestLogic extends DataBalanceDetailRestLogic {

  const BUCKET_TYPE_CURRENT_PACK = 2;
  const BUCKET_TYPE_QUEUED_PACK = 1;
  const BUCKET_TYPE_CURRENT_PASS = 0;
  const UNIT_BASE = 'Mb';
  const KB = 1024;
  const MB = 1024 * 1024;
  const GB = 1024 * 1024 * 1024;
  const CHARS_EXCLUDE = '*';


  /**
   * @var array
   */
  protected $tokenInfo;

  /**
   * {@inheritdoc}
   */

  protected $clientType;

  /**
   *
   * @var object
   */
  protected $quotaInfo;

  /**
   * Responds to setConfig.
   *
   * @param mixed $config_block
   *   Config card or default.
   */
  public function setConfig($config_block) {
    $this->configBlock = $config_block;
  }

  /**
   * Responds to GET requests.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   * @throws \Exception
   *   Throws exception expected.
   */
  public function get($msisdn) {
    $rows = [];
    $config = $this->configBlock;
    $bucket_list_sanitized = $this->getQuotaInfoSanitize($msisdn);
    $roaming_data = $this->getRoamingData($msisdn);

    if (count($bucket_list_sanitized) > 0) {
      if (isset($bucket_list_sanitized['currentPack'])) {
        $current_pack = $bucket_list_sanitized['currentPack'];
        foreach ($current_pack as $item) {
          $rows[] = $this->getFormattedListItem($config, $item, self::BUCKET_TYPE_CURRENT_PACK);
        }
      }
      $current_passes = $bucket_list_sanitized['currentPasses']['tags'];
      foreach ($current_passes as $key => $item) {
        $temp_item = [
          'tags' => $item['name'],
          'imageName' => $this->utilsMobile->getImageName($item['value']),
          'validFor' => [
            'value' => [
              'endDateTime' => $key,
            ]
          ]
        ];
        $rows[] = $this->getFormattedListItem($config, $temp_item, self::BUCKET_TYPE_CURRENT_PASS);
      }

      $queued_packs = $bucket_list_sanitized['queuedPacks'] ?? NULL;
      if (is_array($queued_packs) || is_object($queued_packs)) {
        foreach ($queued_packs as $item) {
          $rows[] = $this->getFormattedListItem($config, $item);
        }
      }


      if ($roaming_data != NULL) {
        $rows[] = $roaming_data;
      }

      return ['bucketsList' => $rows];
    }
    else if (count($bucket_list_sanitized) === 0 && $roaming_data != NULL) {
      $rows[] = $roaming_data;
      return ['bucketsList' => $rows];
    }
    else {
      return [
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
  }

  /**
   * Responds to GET requests.
   *
   * @param string $id_type
   *   Type.
   *
   * @param string $id
   *   Msisdn.
   *
   * @return array
   *   The Array of buckets.
   *
   * @throws \ReflectionException
   */
  public function getRoamingData($id) {
    $block_configs = $this->configBlock['detail']['fields'];
    $msisdn = $this->utilsMobile->modifyMsisdnCountryCode($id, TRUE);
    $data_roaming = $this->getPlanConsumptionBalance(trim($msisdn));
    if (empty($data_roaming)) {
      return NULL;
    }
    return $this->dataStructureRoamingPost($data_roaming, $block_configs);
  }

  /**
   * Get Plan Usage Summary with Roaming Data Plan Summary
   *
   * @param string $msisdn
   * @return object
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   * @throws \ReflectionException
   */
  public function getPlanConsumptionBalance($msisdn) {
    // Get Roaming Plan Summary
    $response = $this->currentPlanService->getRoamingDataPlanUsage($msisdn, 63);
    // Validation
    if (empty($response) || empty($response->packageCode)) {
      return false;
    }
    // Get Plan Usage Summary
    $summary = $this->currentPlanService->getPlanUsageSummary($msisdn);
    // Validation
    if (empty($summary) || empty($summary->consumption)) {
      return false;
    }
    // Adding Roaming Plan Summary to Plan Usage Summary
    $summary->consumption[] = (object) [
      "type" => "DATA ROAM",
      "planCode" => $response->packageCode,
      "planName" => $response->packageName,
      "planFee" => 0,
      "used" => (float) $response->unitUsedTraffic,
      "limit" => (float) $response->quotaFupLte,
      "extra" => 0,
      "unit" => "MB"
    ];
    return $summary;
  }


  /**
   * Undocumented function
   *
   * @param object $data_roaming
   * @param array $block_configs
   * @return array
   */
  public function dataStructureRoamingPost($data_roaming, $block_configs) {
    $response = [];
    $rows_roaming = [];

    foreach ($data_roaming->consumption as $roaming) {
      if ($roaming->unit === 'MB' && $roaming->type == 'DATA ROAM') {

        $rows_roaming['unlimited'] = [
          'value' => FALSE,
        ];

        $rows_roaming['isActive'] = [
          'value' => TRUE
        ];

        $rows_roaming['showBar'] = [
          'value' => TRUE
        ];

        $rows_roaming['bucketsId'] = [
          'label' => $block_configs['bucketsId']['label'],
          'show'  => ($block_configs['bucketsId']['show']) ? TRUE : FALSE,
          'value' => $roaming->type,
          'formattedValue' => $roaming->type,
        ];

        $rows_roaming['name'] = [
          "label" => $block_configs['name']['label'],
          "show" => ($block_configs['name']['show']) ? TRUE : FALSE,
          "value" => $roaming->planCode,
          "formattedValue" => $roaming->planName
        ];

        $aux = ($roaming->limit > 1024) ? 1024 : 1;
        $units = ($roaming->limit > 1024) ? 'GB' : $roaming->unit;

        $remaining_value = round(($roaming->limit - $roaming->used) / $aux, 1);
        $rows_roaming['remainingValue'] = [
          "label" => $block_configs['remainingValue']['label'],
          "show" => empty($block_configs['remainingValue']['show']) ? FALSE : TRUE,
          "value" => $remaining_value,
          "formattedValue" => $remaining_value . $units
        ];

        $reserved_amount = round($roaming->limit / $aux, 1);
        $rows_roaming['reservedAmount'] = [
          "label" => $block_configs['reservedAmount']['label'],
          "show" => empty($block_configs['reservedAmount']['show']) ? FALSE : TRUE,
          "value" => $reserved_amount,
          "formattedValue" => $reserved_amount . $units
        ];

        $reserved_used = round($roaming->used / $aux, 1);
        $rows_roaming['reserveUsed'] = [
          "label" => $block_configs['reserveUsed']['label'],
          "show" => empty($block_configs['reserveUsed']['show']) ? FALSE : TRUE,
          "value" => $reserved_used,
          "formattedValue" => $reserved_used . $units
        ];

        $rows_roaming['endDateTime'] = [
          "label" => $block_configs['validForLabel']['label'],
          "show" => ($block_configs['validForLabel']['show']) ? TRUE : FALSE,
          "value" => [
            "startDate" => $data_roaming->periodStart,
            "endDateTime" => $data_roaming->periodEnd,
          ],
          "formattedValue" => $block_configs['validForLabel']['label'] . " " . $this->formatDateTime(date(DATE_ATOM, time()), $data_roaming->periodEnd)
        ];
        array_push($response, $rows_roaming);
      }
    }

    return $response[0];
  }

  /**
   * Get formats dates for valid roaming period
   *
   * @param string $init_date
   * @param string $end_date
   * @return string
   */
  public function formatDateTime($init_date, $end_date) {
    $date_formatted = "";
    $end_date = new \DateTime($end_date);
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
   * Get formatted array for empty buckets.
   *
   * @param array $config
   *   Configuration Array.
   * @param array $info
   *   Data Array.
   ** @param integer $bucket_type
   *   category of info.
   *
   * @return array
   *   Array.
   */
  public function getFormattedListItem(array $config, array $info, $bucket_type = 1) {
    $row = [];
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PASS) {
      $row['isActive']['value'] = true;
      $row['showBar']['value'] = false;
    }
    elseif ($bucket_type == self::BUCKET_TYPE_QUEUED_PACK) {
      $row['isActive']['value'] = false;
      $row['showBar']['value'] = true;
    }
    else {
      $row['isActive']['value'] = true;
      $row['showBar']['value'] = true;
    }
    foreach ($config['detail']['fields'] as $field_name => $field) {
      switch ($field_name) {
        case 'bucketsId':
          $row[$field_name]['label'] = $field['label'];
          $row[$field_name]['show'] = (bool) $field['show'];
          $row[$field_name]['value'] = '0';
          $row[$field_name]['formattedValue'] = '';
          break;
        case 'reservedAmount':
          $reserved_amount = (!empty($info['reservedAmount']['value'])) ? $info['reservedAmount']['value'] : 0;
          $row[$field_name]['label'] = $this->getRemainingReservedAmountLabel($field['label'], $bucket_type);
          $row[$field_name]['show'] = $bucket_type == self::BUCKET_TYPE_CURRENT_PASS ? false : (bool) $field['show'];
          $row[$field_name]['value'] = $reserved_amount;
          $row[$field_name]['formattedValue'] = $this->formatData($reserved_amount);
          break;
        case 'name':
          $row[$field_name]['label'] = '';
          $row[$field_name]['show'] = (bool) $field['show'];
          $row[$field_name]['class'] = '';
          $row[$field_name]['value'] = $this->getNameLabel($info, $bucket_type);
          $row[$field_name]['formattedValue'] = $row[$field_name]['value'];
          break;
        case 'remainingValue':
          $remaining_value = (!empty($info['remainingValue']['value'])) ?
            $info['remainingValue']['value'] : 0;
          $row[$field_name]['label'] = $this->getRemainingReservedAmountLabel($field['label'], $bucket_type);
          $row[$field_name]['show'] = $bucket_type == self::BUCKET_TYPE_CURRENT_PASS ? false : (bool) $field['show'];
          $row[$field_name]['value'] = $remaining_value;
          $row[$field_name]['formattedValue'] = $this->formatData($remaining_value);
          break;
        case 'validFor':
          $row[$field_name]['label'] = $this->getValidForLabelValue($config, $bucket_type);
          $row[$field_name]['show'] =
            $bucket_type == self::BUCKET_TYPE_CURRENT_PASS && $this->clientType == 'POSTPAID' ? false : (bool) $field['show'];
          $row[$field_name]['value'] = [
            'startDate' => $this->getStartDateValue($info, $bucket_type),
            'endDateTime' => $this->getEndDateValue($info, $bucket_type),
          ];
          $row[$field_name]['formattedValue'] = $this->getValidForFormattedValue($field, $info, $bucket_type);
          break;
      }
    }
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PASS) {
      $row['tags'] = $this->addTagsElement($info);
    }
    return $row;
  }

  /**
   * Add tags imagen
   *
   * @param array $info
   * @return array
   */
  public function addTagsElement($info) {
    $row = [
      'label' => '',
      'show' => true,
      'value' => $info['tags'],
      'imageName' => $info['imageName'],
    ];
    return $row;
  }

  /**
   * Get name format for label
   *
   * @param array $info
   * @param string $bucket_type
   * @return string
   */
  public function getNameLabel($info, $bucket_type) {
    if (isset($info['name']['description'])) {
      $name = $info['name']['description'];
    }
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PASS) {
      if ($this->clientType == 'PREPAID') {
        $name = $this->configBlock['detail']['fields']['name_apps_unlimited_prepaid']['label'];
      }
      else {
        $name = $this->configBlock['detail']['fields']['name_apps_unlimited_postpaid']['label'];
      }
    }
    return $name;
  }

  /**
   * Get remaining reserved amount for label
   *
   * @param string $field_data
   * @param string $bucket_type
   * @return string
   */
  public function getRemainingReservedAmountLabel($field_data, $bucket_type) {
    $label = $field_data;
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PASS) {
      $label = '';
    }
    return $label;
  }

  /**
   * Get start date value
   *
   * @param array $info
   * @param string $bucket_type
   * @return string
   */
  public function getStartDateValue($info, $bucket_type) {
    $value = (!empty($info['validFor']['value']['startDate'])) ?
      $info['validFor']['value']['startDate'] : '';
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PASS) {
      return '';
    }
    return $value;
  }

  /**
   * Get end date value
   *
   * @param array $info
   * @param string $bucket_type
   * @return string
   */
  public function getEndDateValue($info, $bucket_type) {
    $value = $info['validFor']['value']['endDateTime'];
    if ($bucket_type == self::BUCKET_TYPE_QUEUED_PACK) {
      $value = date(DATE_ATOM, time());
    }
    return $value;
  }

  /**
   * Get valid for formatted value
   *
   * @param array $field
   * @param array $info
   * @param string $bucket_type
   * @return string
   */
  public function getValidForFormattedValue($field, $info, $bucket_type) {
    $formatted_value = $field['label'];
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PACK || $bucket_type == self::BUCKET_TYPE_CURRENT_PASS) {
      $formatted_value = $this->getDateDiffLabel($info['validFor']['value']['endDateTime']);
    }
    $formatted_value = isset($formatted_value) ? $formatted_value : '';
    return $formatted_value;
  }


  /**
   * Get valid for label value
   *
   * @param array $config
   * @param string $bucket_type
   * @return string
   */
  public function getValidForLabelValue($config, $bucket_type) {
    $label_value = '';
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PASS) {
     $label_value = $config['detail']['fields']['validForLabel']['label'];
    }
    if ($bucket_type == self::BUCKET_TYPE_CURRENT_PACK && $this->clientType == 'POSTPAID') {
      $label_value = $config['detail']['fields']['validForLabel']['label'];
    }
    return $label_value;
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
    if (!isset($value)) {return '';}
    $value = strtoupper($this->utils->formatData($value, self::UNIT_BASE));
    $pos = strpos($value, 'MB');
    if ($pos !== false) {
      $arr = explode('.', $value);
      if (count($arr) > 1) {
        $value = $arr[0] . ' ' . 'MB';

      }
    }
    return $value;
  }

  /**
   * Sanitized Current Pass Label.
   */
  public function sanitizedCurrentPassLabel($label) {
    $label = str_replace(str_split(self::CHARS_EXCLUDE), '', $label);
    $label = str_replace(' ', '_', $label);
    return strtolower($label . '.svg');
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
    $this->quotaInfo = $this->getQuotaInfo($msisdn);
    $quota_info =& $this->quotaInfo;
    $result = [];
    $this->clientType = $quota_info->clientType;
    if (isset($quota_info->queuedPacks)) {
      foreach ($quota_info->queuedPacks as $queuedPack) {
        $arr_data = [];
        $arr_data['reservedAmount']['value'] = $queuedPack->totalQuotaMbytes;
        $arr_data['remainingValue']['value'] = $queuedPack->availableQuotaMbytes;
        $arr_data['validFor']['value']['startDate'] = $queuedPack->purchaseDate;
        $arr_data['validFor']['value']['endDateTime'] = '';
        $arr_data['name']['value'] = $queuedPack->description;
        $arr_data['name']['description'] = $queuedPack->description;
        $result['queuedPacks'][] = $arr_data;
      }
    }
    if (isset($quota_info->currentPasses)) {
      $arr_data = [];
      foreach ($quota_info->currentPasses as $currentPass) {
        $expiration_date = isset($currentPass->expirationDate) ? $currentPass->expirationDate : date('Y-m-d\TH:m:sO');
        if (isset($arr_data[$expiration_date])) {
          $arr_data[$expiration_date]['value'][] = $this->sanitizedCurrentPassLabel($currentPass->description);
          $arr_data[$expiration_date]['name'][] = $currentPass->description;
        } else {
          $arr_data[$expiration_date]['value'] = [];
          $arr_data[$expiration_date]['name'] = [];
          $arr_data[$expiration_date]['value'][] = $this->sanitizedCurrentPassLabel($currentPass->description);
          $arr_data[$expiration_date]['name'][] = $currentPass->description;
        }
      }
      if (!empty($arr_data)) {
        $result['currentPasses'] = [
          'tags' => $arr_data,
        ];
      }
    }
    if ($quota_info->clientType == 'POSTPAID' && isset($quota_info->currentPack)) {
      $current_pack = $quota_info->currentPack;
      $arr_data = [];
      $arr_data['reservedAmount']['value'] = $current_pack->totalQuotaMbytes;
      $arr_data['remainingValue']['value'] = $current_pack->availableQuotaMbytes;
      $arr_data['validFor']['value']['startDate'] = $current_pack->purchaseDate;
      $arr_data['validFor']['value']['endDateTime'] = $current_pack->expirationDate;
      $arr_data['name']['value'] = $current_pack->description;
      $arr_data['name']['description'] = $current_pack->description;
      $result['currentPack'][] = $arr_data;

    }
    return $result;
  }

  /**
   * Get the bucket list.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return object
   *   The bucket list.
   *
   * @throws \Exception
   *   Throws exception expected.
   */
  public function getQuotaInfo($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_plans_v2_0_data_balance_detail_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (\Exception $exception) {
      $messages = $this->configBlock['messages'];
      $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
      $message = ($exception->getCode() == '404') ? $title . $messages['empty'] : $title . $messages['error'];

      $reflected_object = new \ReflectionClass(get_class($exception));

      $property = $reflected_object->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      $property_file = $reflected_object->getProperty('file');
      $property_file->setAccessible(TRUE);
      $property_file->setValue($exception, '');
      $property_file->setAccessible(FALSE);

      $property_line = $reflected_object->getProperty('line');
      $property_line->setAccessible(TRUE);
      $property_line->setValue($exception, 0);
      $property_line->setAccessible(FALSE);

      throw $exception;
    }
  }

  /**
   * devuelve string con la diferencia de fechas entre la actual y el dia que se pasa por parametro
   *
   * @param string $end_date
   *   endDate.
   *
   * @return string
   *   The date in string format.
   *
   */
  public function getDateDiffLabel($end_date) {
    $time_end = strtotime($end_date);
    $diff = ($time_end - time()) / (24 * 60 * 60);
    $diff = intval($diff);
    if ($diff > 0) {
      return $this->utils->formatRemainingTimeDayHour($end_date);
    }
    else {
      return $this->utils->formatRemainingTime($end_date);
    }
  }

  /**
   * getStorageImages.
   */
  public function getStorageImages($current_passes) {
    $arr_images = $this->utils->orderedListImages($current_passes, 0, 0);
    return $arr_images;
  }

}
