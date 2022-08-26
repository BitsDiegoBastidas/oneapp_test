<?php

namespace Drupal\oneapp_mobile_plans_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_plans\Services\v2_0\CurrentRestLogic;

/**
 * Class CurrentGtRestLogic.
 */
class CurrentGtRestLogic extends CurrentRestLogic {

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $utils;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $manager;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $configBlock;

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
    uasort($configBlock['fields'], ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $this->configBlock = $configBlock;
  }

  /**
   * Responds to GET requests.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   The HTTP response object.
   */
  public function get($msisdn) {
    $data = [];
    $rows = [];
    $config = $this->configBlock['config'];
    $product_offering_list = $this->configBlock['productOfferingList'];
    $current_plan = $this->getCurrentPlan($msisdn);

    foreach ($this->configBlock['fields'] as $id => $field) {
      $data[$id] = [
        'label' => $field['label'],
        'show' => ($field['show']) ? TRUE : FALSE,
      ];

      switch ($id) {
        case 'planName':
          $data[$id]['value'] = $current_plan['currentPlan']->consumption[0]->planName;
          $data[$id]['formattedValue'] = $current_plan['currentPlan']->consumption[0]->planName;
          break;

        case 'billingCycle':
          $value = '';
          $format_date = '';
          $value = $current_plan['currentByContracts']->Envelope->Body->GetPostpaidContractDetailsResponse->nextBillingDate;
          if ($this->isValidTimeStamp($value)) {
            $format_date = $this->utils->formatDate(strtotime($value), 'selfcare_mes');
          }
          $data[$id]['value'] = $value;
          $data[$id]['formattedValue'] = $format_date;
          break;

        case 'endDate':
          $value = '';
          $format_date = '';
          if (isset($current_plan['currentByContracts']->Envelope->Body->GetPostpaidContractDetailsResponse->contractEndDate)) {
            $value = $current_plan['currentByContracts']->Envelope->Body->GetPostpaidContractDetailsResponse->contractEndDate;
            if ($this->isValidTimeStamp($value)) {
              $format_date = $this->utils->formatDate(strtotime($value), 'selfcare_mes');
            }
          }
          $data[$id]['value'] = $value;
          $data[$id]['formattedValue'] = $format_date;
          $data[$id]['show'] = $this->isValidTimeStamp($value) ? TRUE : FALSE;
          break;

        case 'monthlyAmount':
          $data[$id]['value'] = $current_plan['currentPlan']->planPrice;
          $data[$id]['formattedValue'] = $this->utils->formatCurrency($current_plan['currentPlan']->planPrice, TRUE, FALSE);
          $data[$id]['description'] = $field['description'];
          break;

        case 'productOfferingList':
          unset($data[$id]['label']);
          unset($data[$id]['show']);
          $consumption = $current_plan['currentPlan']->consumption;
          foreach ($consumption as $productList) {

            $type = str_replace(" ", "", lcfirst(ucwords(strtolower($productList->type))));

            if (isset($product_offering_list[$type])) {
              $product = [];
              $limit = $productList->limit;
              $unit = $productList->unit;

              if ($limit > 0) {
                $product = [
                  'label' => $product_offering_list[$type]['label'],
                  'show' => ($product_offering_list[$type]['show']) ? TRUE : FALSE,
                  'value' => $limit,
                ];
                if (in_array($type, ['data', 'vozOnnet', 'vozExnet'])) {
                  $format_data = $type;
                  if ($type != 'data') {
                    $format_data = 'voice';
                    $get_limit = $this->getLimit($type);
                    $limit = ($limit >= $get_limit["{$type}Limit"]) ? -1 : $limit;
                  }
                  /* If limit is -1 => "Ilimitado" else $limit. */
                  $product['formattedValue'] = ($limit == -1)
                    ? $config['messages']['unlimited']
                    : strtoupper($this->formatData($format_data, $limit));
                  if ($type == 'vozOnnet' && $unit == 'QUETZALES' && $limit > -1) {
                    $product['formattedValue'] = $this->utils->formatCurrency($limit, TRUE, FALSE);
                    $product['label'] = $product_offering_list["{$type}Quetzales"]['label'];
                  }
                }
                else {
                  $get_limit = $this->getLimit($type);
                  $limit = ($limit >= $get_limit) ? -1 : $limit;
                  /* If limit is -1 => "Ilimitado" else limit. */
                  $product['formattedValue'] = ($limit == -1)
                    ? $config['messages']['unlimited']
                    : (string) $limit;
                }
                $data[$id][] = $product;
              }
            }
          }
          break;

        case 'additionalRecurrentOfferingList':
          $data[$id] = [];
          break;
      }

    }

    return $data;
  }

  /**
   * Implements getCurrentPlan.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return ResponseInterface
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   */
  protected function getCurrentPlan($msisdn) {
    try {
      $current_by_contracts = $this->manager
        ->load('oneapp_mobile_plans_v2_0_current_by_contracts_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();

      $current_plan = $this->manager
        ->load('oneapp_mobile_plans_v2_0_current_plan_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();

      return [
        'currentByContracts' => $current_by_contracts,
        'currentPlan' => $current_plan,
      ];
    }
    catch (HttpException $exception) {
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

  /**
   * Implements formatData.
   *
   * @param string $offeringCategory
   *   Offering category value.
   * @param string $reservedAmount
   *   Reserved amount value.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  protected function formatData($offering_category, $reserved_amount) {

    switch ($offering_category) {
      case 'data':
        if ($reserved_amount > 1024) {
          return $this->utils->formatData($reserved_amount, 'Mb');
        }
        return $reserved_amount . ' Mb';

      default:
        return $reserved_amount;
    }
  }

  /**
   * Implements formatData.
   *
   * @param string $type
   *   Reserved amount value.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   */
  protected function getLimit($type) {

    if ($type == 'sms') {
      // Get Limit by SMS Balance config.
      $config_sms_balance = \Drupal::config('adf_block_config.oneapp_mobile_upselling_v2_0_sms_balance_block')->getRawData();
      $sms_balance_limit = 0;
      if (!empty($config_sms_balance['block']['postpaid']['limit'])) {
        $sms_balance_limit = $config_sms_balance['block']['postpaid']['limit'];
      }
      return $sms_balance_limit;
    }

    // Get Limit by Voice Balance config.
    $config_voice_balance = \Drupal::config('adf_block_config.oneapp_mobile_upselling_v2_0_voice_balance_block')->getRawData();
    $voice_balance_limit = [
      'vozOnnetLimit' => 0,
      'vozExnetLimit' => 0,
    ];

    if (!empty($config_voice_balance['block']['config']['postpaid'])) {
      $postpaid = $config_voice_balance['block']['config']['postpaid'];
      $voice_balance_limit = [
        'vozOnnetLimit' => $postpaid['limitOnnet'],
        'vozExnetLimit' => $postpaid['limitExnet'],
      ];
    }

    return $voice_balance_limit;

  }

  private function isValidTimeStamp($value) {
    return !empty($value);
  }

}
