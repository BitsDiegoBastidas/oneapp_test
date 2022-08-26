<?php

namespace Drupal\oneapp_mobile_billing_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\Core\Datetime\Entity\DateFormat;

/**
 * Class CallDetailsGtRestLogic.
 */
class CallDetailsGtRestLogic {

  /**
   * Block configuration.
   *
   * @var mixed
   */
  protected $config_block;

  /**
   * Default utils.
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
   * @param string $start_date
   *   Start Date.
   * @param string $end_date
   *   End Date.
   * @param int $limit
   *   Limit.
   *
   * @return array
   *   The associative array.
   *
   * @throws \ReflectionException
   *   Exeption.
   */
  public function get($msisdn, $start_date, $end_date, $limit, $event_type) {
    $rows = [];
    $count = 0;
    $config = $this->configBlock['config'];
    $limit = intval($limit);

    if ($limit !== 0) {
      $calls = $this->getCallsDetail($msisdn, $start_date, $end_date, $limit, $event_type);
      if (isset($calls['noData'])) {
        return $calls;
      }
      if (!empty($calls)) {
        foreach ($calls as $call) {
          if ($count < intval($config['limit']['limit'])) {
            $row = [];
            $index = 0;

            foreach ($this->configBlock['callDetails'] as $key => $field) {

              $row[$key] = [
                'label' => $field['label'],
                'show' => ($field['show']) ? TRUE : FALSE,
              ];

              $event_type = $call->eventType;
              switch ($key) {
                case 'destination':
                  $destination = $call->calLFrom;
                  if ($event_type == 'S') {
                    $destination = $call->targetNumber;
                  }
                  $destination = $this->mobileUtils->modifyMsisdnCountryCode($destination, FALSE);
                  $row[$key]['value'] = $destination;
                  $row[$key]['formattedValue'] = $this->maskedDestination($destination);
                  break;

                case 'dateTimeStart':
                  $row[$key]['value'] = $call->eventDateTime;
                  $row[$key]['formattedValue'] =
                    $this->dateFormat($call->eventDateTime, $config['date']['format']);
                  break;

                case 'duration':
                  $row[$key]['value'] = $call->eventDuration;
                  $row[$key]['formattedValue'] = $this->utils->formatTime($call->eventDuration);
                  break;
              }

              $index++;
            }

            $row['eventType'] = $event_type;

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
        else {
          $rows = $this->configBlock['message']['empty']['label'];
        }
      }
    }
    else {
      if ($this->configBlock['message']['empty']['show'] == 1) {
        return $this->emptyState();
      }
      else {
        $rows = $this->configBlock['message']['empty']['label'];
      }
    }

    return [
      'callsList' => $rows,
    ];
  }

  /**
   * Get empty state.
   */
  public function emptyState() {
    $rows = $this->configBlock['message']['empty']['label'];
    return [
      'callsList' => $rows,
      'noData' => ['value' => 'empty'],
    ];
  }

  /**
   * Get calls detail.
   *
   * @param string $msisdn
   *   Msisdn.
   * @param string $start_date
   *   StartDate.
   * @param string $end_date
   *   EndDate.
   * @param int $limit
   *   Limit.
   *
   * @return array
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   * @throws \ReflectionException
   *    Exeption.
   */
  protected function getCallsDetail($msisdn, $start_date, $end_date, $limit, $event_type) {
    try {
      $dateInput = (!empty($this->configBlock['config']['dateInput']['formatServices']['fields'])) ?
        $this->configBlock['config']['dateInput']['formatServices']['fields'] : [];
      $start_date_variable = (!empty($dateInput['startDate']['variable'])) ? $dateInput['startDate']['variable'] : 'start_date';
      $end_date_variable = (!empty($dateInput['endDate']['variable'])) ? $dateInput['endDate']['variable'] : 'end_date';
      $ql = (!empty($event_type)) ? "SELECT * WHERE eventType IN ('{$event_type}')" : "SELECT * WHERE eventType IN ('S','E')";

      $response = $this->manager
        ->load('oneapp_mobile_billing_v2_0_call_details_endpoint')
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders([])
        ->setQuery([
          $start_date_variable => $start_date,
          $end_date_variable => $end_date,
          'limit' => intval($limit),
          'ql' => $ql,
        ])
        ->sendRequest();

      return $response->voiceUsage;
    }
    catch (HttpException $exception) {
      $messages = $this->configBlock['message'];
      if ($exception->getCode() == 404 && $messages['empty']['show'] === 1) {
        return $this->emptyState();
      }
      elseif (isset($messages['empty']['show'])) {
        return $this->emptyState();
      }
      else {
        $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
        $message = ($exception->getCode() == 404) ? $title . $messages['empty']['label'] : $title . $messages['error']['label'];
        $reflected_object = new \ReflectionClass(get_class($exception));
        $property = $reflected_object->getProperty('message');
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

    $end = floor(strlen($destination) / 2);
    $repeat = str_repeat('X', $end);
    $masked = substr_replace($destination, "{$repeat}-", 0, $end);

    return $masked;
  }

  /**
   * DateFormat.
   */
  protected function dateFormat($date, $format_value) {

    $date_format_entity = DateFormat::load($format_value);
    $date_format = $date_format_entity->getPattern();

    $date = date_create($date);
    return date_format($date, $date_format);
  }

}
