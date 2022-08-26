<?php

namespace Drupal\oneapp_mobile_billing_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\Core\Datetime\Entity\DateFormat;

/**
 * Class SmsDetailsRestLogic.
 */
class SmsDetailsGtRestLogic {

  /**
   * Block configuration.
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
   * Default utils.
   *
   * @var mixed
   */
  protected $mobileUtils;

  /**
   * Default configuration.
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
    $this->mobileUtils = \Drupal::service('oneapp.mobile.utils');
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
   * @param string $startDate
   *   Start Date.
   * @param string $endDate
   *   End Date.
   * @param int $limit
   *   Limit.
   *
   * @return array
   *   The associative array.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   * @throws \ReflectionException
   *   Exception.
   */
  public function get($msisdn, $startDate, $endDate, $limit, $eventType) {

    $rows = [];
    $count = 0;
    $config = $this->configBlock['config'];
    $limit = intval($limit);

    if ($limit !== 0) {
      $messages = $this->getSmsDetail($msisdn, $startDate, $endDate, $limit, $eventType);
      if (isset($messages['noData'])) {
        return $messages;
      }
      if (!empty($messages)) {
        foreach ($messages as $message) {
          if ($count < intval($config['limit']['limit'])) {
            $row = [];
            $index = 0;

            foreach ($this->configBlock['smsDetail'] as $key => $field) {
              $row[$key] = [
                'label' => $field['label'],
                'show' => ($field['show']) ? TRUE : FALSE,
              ];

              switch ($key) {
                case 'destination':
                  $destination = $this->mobileUtils->modifyMsisdnCountryCode($message->targetNumber, FALSE);
                  $row[$key]['value'] = $destination;
                  $row[$key]['formattedValue'] = $this->maskedDestination($destination);
                  break;

                case 'dateTimeStart':
                  $row[$key]['value'] = $message->eventDateTime;
                  $row[$key]['formattedValue'] = $this->dateFormat($message->eventDateTime, $config['date']['format']);
                  break;
              }
              $index++;
            }

            $row['eventType'] = $message->eventType;

            $rows[$count] = $row;
            $count++;
          }
          else {
            break;
          }
        }
      }
      else {
        if ($this->configBlock['message']['empty']['show'] == 1) {
          return $this->emptyState();
        }
      }
    }
    else {
      if ($this->configBlock['message']['empty']['show'] == 1) {
        return $this->emptyState();
      }
    }
    return [
      'smsList' => $rows,
    ];
  }

  /**
   * Get empty state.
   */
  public function emptyState() {
    return [
      'smsList' => [],
      'noData' => ['value' => 'empty'],
    ];
  }

  /**
   * Get sms detail.
   *
   * @param string $msisdn
   *   Msisdn.
   * @param string $startDate
   *   StartDate.
   * @param string $endDate
   *   EndDate.
   * @param int $limit
   *   Limit.
   *
   * @return array
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   * @throws \ReflectionException
   *   Exception.
   */
  protected function getSmsDetail($msisdn, $startDate, $endDate, $limit, $eventType) {
    try {
      $dateInput = (!empty($this->configBlock['config']['dateInput']['formatServices']['fields'])) ? $this->configBlock['config']['dateInput']['formatServices']['fields'] : [];
      $startDateVariable = (!empty($dateInput['startDate']['variable'])) ? $dateInput['startDate']['variable'] : 'start_date';
      $endDateVariable = (!empty($dateInput['endDate']['variable'])) ? $dateInput['endDate']['variable'] : 'end_date';
      $ql = (!empty($eventType)) ? "SELECT * WHERE eventType IN ('{$eventType}')" : "SELECT * WHERE eventType IN ('S','E')";

      $response = $this->manager
        ->load('oneapp_mobile_billing_v2_0_sms_details_endpoint')
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders([])
        ->setQuery([
          $startDateVariable => $startDate,
          $endDateVariable => $endDate,
          'limit' => $limit,
          'ql' => $ql,
        ])
        ->sendRequest();

      return (!empty($response->smsUsage)) ? $response->smsUsage : $this->emptyState();
    }
    catch (HttpException $exception) {
      $messages = $this->configBlock['message'];
      if ($exception->getCode() == 404 && $messages['empty']['show'] === 1) {
        return $this->emptyState();
      }
      else {
        $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
        $message = ($exception->getCode() == '404') ? $title . $messages['empty']['label'] : $title . $messages['error']['label'];
        $reflectedObject = new \ReflectionClass(get_class($exception));
        $property = $reflectedObject->getProperty('message');
        $property->setAccessible(TRUE);
        $property->setValue($exception, $message);
        $property->setAccessible(FALSE);

        throw $exception;
      }
    }
  }

  /**
   * MaskedDestination.
   */
  protected function maskedDestination($destination) {

    if (is_numeric($destination)) {
      $len = strlen($destination);
      $end = floor($len / 2);
      $repeat = str_repeat('X', $end);
      $masked = substr_replace($destination, "{$repeat}-", 0, $end);
    }
    else {
      $masked = $destination;
    }

    return $masked;
  }

  /**
   * MaskedDestination.
   */
  protected function dateFormat($date, $formatValue) {

    $date_format_entity = DateFormat::load($formatValue);
    $date_format = $date_format_entity->getPattern();

    $date = date_create($date);
    return date_format($date, $date_format);
  }

}
