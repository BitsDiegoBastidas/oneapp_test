<?php

namespace Drupal\oneapp_mobile_billing_gt\Plugin\rest\resource\v2_0;

use Drupal\rest\ResourceResponse;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\oneapp_mobile_billing\Plugin\rest\resource\v2_0\CallDetailsRestResource;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   api_response_version = "v2_0",
 *   block_id = "oneapp_mobile_billing_v2_0_call_details_block",
 *   id = "oneapp_mobile_billing_v2_0_call_details_rest_resource",
 *   label = @Translation("ONEAPP Mobile Billing - Call Details v2.0"),
 *   uri_paths = {
 *     "canonical" = "api/v2.0/mobile/billing/{idType}/{id}/calldetails"
 *   }
 * )
 */
class CallDetailsGtRestResource extends CallDetailsRestResource {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->init();

    $this->utils = \Drupal::service('oneapp.utils');

    $config = $this->configBlock['config'];
    $service = \Drupal::service('oneapp_mobile_billing.v2_0.call_details_rest_logic');

    // Get query params.
    $start_date = \Drupal::request()->query->get('startDate');
    $end_date = (\Drupal::request()->query->get('endDate'));
    $limit = (\Drupal::request()->query->get('limit')) ?? $config['limit']['limit'];
    $event_type = (\Drupal::request()->query->get('eventType'));
    // Get range and format dates.
    $slack = intval($config['slack']['slack']);
    $range = ($slack !== 0) ? $slack : 15;
    $this->getDatesFormatted($start_date, $end_date, $range);

    $service->setConfig($this->configBlock);
    $data = $service->get($this->accountId, $start_date, $end_date, intval($limit), $event_type);

    // Build meta, config and data.
    $this->apiResponse->getData()->setAll($data);
    $this->responseMeta($start_date, $end_date, $limit, $event_type);
    $this->responseConfig($data);

    // Build response with data.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function responseMeta($start_date, $end_date, $limit, $event_type = '') {
    $meta = $this->apiResponse->getMeta()->getAll();
    $meta['params']['startDate'] = $start_date;
    $meta['params']['endDate'] = $end_date;
    $meta['params']['limit'] = $limit;
    $meta['params']['eventType'] = (!empty($event_type)) ? $event_type : 'All';

    $this->apiResponse->getMeta()->setAll($meta);
  }

  /**
   * {@inheritdoc}
   */
  public function responseConfig($data) {

    $config = $this->configBlock['config'];

    $message = $this->configBlock['message'];
    if (isset($data['noData'])) {
      $response = [
        'label' => $message[$data['noData']['value']]['label'],
        'show' => (bool) $message[$data['noData']['value']]['show'],
      ];
      $this->apiResponse
        ->getConfig()
        ->set('message', $response);
    }
    $back_button_config = $this->configBlock['config']['buttons']['backButton'];
    $utils = \Drupal::service('oneapp.utils');
    $back_button = [
      'show' => $utils->formatBoolean($back_button_config['show']),
      'label' => $back_button_config['label'],
      'url' => $back_button_config['url'],
      'type' => 'link',
    ];

    $this->apiResponse->getConfig()
      ->set('actions', ['backButton' => $back_button]);

    $slack = intval($config['slack']['slack']);
    $range = ($slack !== 0) ? $slack : 15;
    $start_date = '';
    $end_date = '';
    $this->getDatesFormatted($start_date, $end_date, $range);

    $filters = $this->getConfigfilters($start_date, $end_date);

    $this->apiResponse->getConfig()
      ->set('form', ['filters' => $filters]);

  }

  /**
   * Set startDate, endDate.
   *
   * @param string $start_date
   *   StartDate.
   * @param string $end_date
   *   EndDate.
   * @param int $range
   *   Range.
   */
  private function getDatesFormatted(&$start_date, &$end_date, $range) {
    $format = (isset($this->configBlock["config"]["dateInput"]["format"]) && !empty($this->configBlock["config"]["dateInput"]["format"])) ?
      $this->configBlock["config"]["dateInput"]["format"] : "short";

    if ($end_date) {
      $end_date = $this->utils->formatDate(strtotime($end_date), $format);
    }
    // If not sent endDate queryParams.
    else {
      $value = new \DateTime('now');
      $end_date = $this->utils->formatDate($value->getTimestamp(), $format);
    }

    // If not sent startDate queryParams.
    if (!$start_date) {
      $date = strtotime('-' . $range . ' day', strtotime($end_date));
      $start_date = $this->utils->formatDate($date, $format);
    }
    else {
      $start_date = $this->utils->formatDate(strtotime($start_date), $format);
    }
  }

  /**
   * GetConfigfilters.
   */
  private function getConfigfilters($start_date, $end_date) {

    $format_value = (
      isset($this->configBlock["config"]["dateInput"]["formatValue"]) &&
      !empty($this->configBlock["config"]["dateInput"]["formatValue"])
    ) ?
      $this->configBlock["config"]["dateInput"]["formatValue"] : "short";
    $date_input = (!empty($this->configBlock['config']['dateInput']['formatServices']['fields'])) ?
      $this->configBlock['config']['dateInput']['formatServices']['fields'] : [];
    $start_date_label = (!empty($date_input['startDate']['label'])) ? $date_input['startDate']['label'] : $this->t('Desde');
    $end_date_label = (!empty($date_input['endDate']['label'])) ? $date_input['endDate']['label'] : $this->t('Hasta');
    $start_date_show = (isset($date_input['startDate']['show'])) ? $date_input['startDate']['show'] : 1;
    $end_date_show = (isset($date_input['endDate']['show'])) ? $date_input['endDate']['show'] : 1;
    $start_date_require = (isset($date_input['startDate']['require'])) ? $date_input['startDate']['require'] : 1;
    $end_date_require = (isset($date_input['endDate']['require'])) ? $date_input['endDate']['require'] : 1;

    $event_type_settings = (!empty($this->configBlock['config']['eventType']['settings']['fields'])) ?
      $this->configBlock['config']['eventType']['settings']['fields'] : [];
    $event_type_options = (!empty($this->configBlock['config']['eventType']['options'])) ?
      $this->configBlock['config']['eventType']['options'] : [];

    $date_format_entity = DateFormat::load($format_value);
    $date_format = $date_format_entity->getPattern();

    $filters = [
      'evenType' => [
        'label' => $event_type_settings['label'],
        'show' => ($event_type_settings['show']) ? TRUE : FALSE,
        'options' => [
          [
            'value' => $event_type_options['all']['variable'],
            'formattedValue' => $event_type_options['all']['label'],
            'show' => ($event_type_options['all']['show']) ? TRUE : FALSE,
          ],
          [
            'value' => $event_type_options['incoming']['variable'],
            'formattedValue' => $event_type_options['incoming']['label'],
            'show' => ($event_type_options['incoming']['show']) ? TRUE : FALSE,
          ],
          [
            'value' => $event_type_options['outgoing']['variable'],
            'formattedValue' => $event_type_options['outgoing']['label'],
            'show' => ($event_type_options['outgoing']['show']) ? TRUE : FALSE,
          ],
        ],
        'validations' => [
          'required' => ($event_type_settings['require']) ? TRUE : FALSE,
        ],
      ],
      'startDate' => [
        'label' => $start_date_label,
        'show' => ($start_date_show) ? TRUE : FALSE,
        'type' => 'select',
        'format' => $date_format,
        'value' => $start_date,
        'formatValue' => $this->utils->formatDate(strtotime($start_date), $format_value),
        'validations' => [
          'required' => ($start_date_require) ? TRUE : FALSE,
          'minDate' => $start_date,
          'maxDate' => $end_date,
        ],
      ],
      'endDate' => [
        'label' => $end_date_label,
        'show' => ($end_date_show) ? TRUE : FALSE,
        'type' => 'select',
        'format' => $date_format,
        'value' => $end_date,
        'formatValue' => $this->utils->formatDate(strtotime($end_date), $format_value),
        'validations' => [
          'required' => ($end_date_require) ? TRUE : FALSE,
          'minDate' => $start_date,
          'maxDate' => $end_date,
        ],
      ],
    ];
    return $filters;
  }

}
