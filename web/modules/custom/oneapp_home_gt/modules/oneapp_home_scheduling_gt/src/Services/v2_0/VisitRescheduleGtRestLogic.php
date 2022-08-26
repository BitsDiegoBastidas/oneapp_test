<?php

namespace Drupal\oneapp_home_scheduling_gt\Services\v2_0;

use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_home_scheduling\Services\v2_0\VisitRescheduleRestLogic;
use Drupal\Core\Datetime\Entity\DateFormat;



/**
 * Class VisitRescheduleGtRestLogic.
 */
class VisitRescheduleGtRestLogic extends VisitRescheduleRestLogic {

  /**
   * @var \Drupal\oneapp_home_scheduling_gt\Services\SchedulingServiceGt
   */
  protected $schedulingService;

  /**
   * @var \Drupal\oneapp_home_gt\Services\UtilsGtService
   */
  protected $utils;

  private $contract;

  /**
   * Get form by Appoinment Id.
   *
   * @param string $id
   *   Number or contract (Or value of billing account).
   * @param string $appointment_id
   *   Id of appointment visit.
   */
  public function get($id, $appointment_id, $external_id = '') {
    $this->dateFormat = $this->configBlock['others']['confReschedule']['formatDat'];
    $this->timeFormat = $this->configBlock['others']['confReschedule']['format'];
    $this->statesList = $this->schedulingService->getStatesVisits();
    $info = $this->utils->getInfoTokenByBillingAccountId($id);

    // TODO.
    $this->getAppointmentData($id, $appointment_id, $external_id);
    if ($this->availableDates->availableTimeslots) {
      return [
        'appointmentAddress' => $this->getAppointmentAddress($info["subscriberId"]),
        'appointmentId' => $this->getAppointmentId(),
        'appointmentContractId' => $this->getContractId(),
        'appointmentType' => $this->getAppointmentType(),
        'appointmentStatus' => $this->getAppointmentStatus(),
        'form' => $this->getForm(),
      ];
    }
    else {
      $response_hide_state = $this->schedulingService::HIDE_STATE;
      $response_hide_state["error_mesagge"] = $this->utils->getConfigGroup('scheduling')['visit_reschedule_date_failed_form']['success']['mail']['body']['value'];
      return $response_hide_state;
    }
  }

  /**
   * @param string $id
   * @param string $appointment_id
   * @param array $query_params
   * @param string $external_id
   * @return array
   */
  public function patch($id, $appointment_id, array $query_params, $external_id = '') {
    // Se hace explode al parámetro $appointment_id en caso de que traiga concatenado el subid ($external_id)
    $ids = explode('|', $appointment_id);
    $appointment_id = $ids[0];
    // Si $appointment_id tiene concatenado el subid, se usará ese ($ids[1]), sino el parametro $external_id
    $external_id = $ids[1] ?? $external_id;
    $ids = $this->utils->getInfoTokenByBillingAccountId($id);
    $contract_id = $ids['contractId'];
    $billing_account = $ids['billingAccount'];
    $visit_details = $this->schedulingService->getVisitDetailsById($contract_id, $appointment_id, $external_id);
    $this->visitId = $visit_details->id;
    // Obtener Parametros a enviar en la Url.
    $params = ['id' => $contract_id, 'appointmentId' => $appointment_id, 'externalId' => $external_id];
    // Obtener Parametros a enviar en el query.
    $query = $this->getRescheduleQuery($query_params);
    // Se pueden configurar cabeceras adicionales si se necesitan.
    $headers = [];
    // Envia la confirmacion del reagendamiento de la visita.
    $response = $this->schedulingService->sendRescheduleVisitPatchEndpoint($params, $query, $headers);
    // Retorno data segun el response.
    $config_reschedule_visit = (object) $this->utils->getConfigGroup('scheduling')['reschedule_visit'];
    $origin = $this->origin ?? (getallheaders()['Origin'] ?? '');
    // Ajuste a validación de visita reprogramada https://jira.tigo.com.hn/browse/ONEAPP-10046
    if (empty($response->code) || ($response->code >= 200 && $response->code < 400)) {
      $success = (object) $config_reschedule_visit->success;
      // Se retira el envío de emails
      return [
        'status' => 'success',
        'message' => [
          'title' => $success->title,
          'body' => $success->message,
          'icon_class' => $success->icon,
        ],
        'actions' => [
          'backVisits' => [
            'label' => $success->link_label,
            'type' => 'link',
            'url' => $this->utils->getUrlByOrigin($origin, $success->link_url),
            'show' => (bool) $success->link_show,
          ],
        ],
      ];
    }
    else {
      $failed = (object) $config_reschedule_visit->failed;
      return [
        'status' => 'failed',
        'message' => [
          'title' => $failed->title,
          'body' => $failed->message,
          'icon_class' => $failed->icon,
        ],
        'actions' => [
          'backVisits' => [
            'label' => $failed->link_label,
            'type' => 'link',
            'url' => $this->utils->getUrlByOrigin($origin, $failed->link_url),
            'show' => (bool) $failed->link_show,
          ],
        ],
      ];
    }
  }

  protected function getSuccessMessageWithEmail($message) {
    $email = $this->schedulingService->getEmailFromToken();
    $message = str_replace('@email', $email, $message);
    return $message;
  }

  protected function sendEmail($template = 'reschedule') {
    $this->schedulingService->sendEmail($template, $this->visitId);
  }


  /**
   * Retorna el listado de las fechas y horaios disponibles.
   *
   * @param string $id
   *   Billing account Id.
   * @param string $appointment_id
   *   appointmentId.
   */
  private function getAppointmentData($id, $appointment_id, $sub_appointment_id = '') {
    $range_date = $this->configBlock['others']['confReschedule']['days'];
    $range_date +=1;
    $format_date = $this->configBlock['others']['confReschedule']['formatReschedule'];
    $format_end = "";
    $date_types = DateFormat::loadMultiple();

    foreach ($date_types as $name => $format) {
      if ($name == $format_date) {
        $format_end = $format->getPattern();
        break;
      }
    }
    $system_date = date("d-m-Y");
    $time_zone = date_default_timezone_get();

    $start_date = \Drupal::service('date.formatter')
    ->format(strtotime($system_date), 'custom', $format_end, $time_zone);
    $end_date = \Drupal::service('date.formatter')
    ->format(strtotime("+$range_date day", strtotime($system_date)), 'custom', $format_end, $time_zone);

    $info = $this->utils->getInfoTokenByBillingAccountId($id);

    $this->contract = $info['contractId'];

    $var_appointment = $this->schedulingService->getVisitDetailsById($info['subscriberId'], $appointment_id, $sub_appointment_id);
    if ($var_appointment) {
      $this->appointment = $var_appointment;
      $this->availableDates = $this->schedulingService->
      retrieveAvailableDatesByRange($info['contractId'], $appointment_id, $sub_appointment_id, $start_date, $end_date);
      if ($this->availableDates->availableTimeslots) {
        return $this->availableDates->availableTimeslots;
      }else {
        return null;
      }
    }
  }

  /**
   * LaunchNotFoundException.
   */
  private function launchNotFoundException() {
    $messages = $this->configBlock['message'];
    $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
    $message = $title . $messages['empty']['label'];
    $error_base = new ErrorBase();
    $error_base->getError()->set('message', $message);
    throw new HttpException(404, $error_base);
  }

  /**
   * Get Form.
   *
   * @return array
   *   form.
   */
  protected function getForm() {
    $start_date_valid_for = $this->availableDates->availableTimeslots[0]->validFor->startDatetime;
    $start_date = $this->schedulingService->getCurrentDateTimeForReschedule($this->appointment->creationDate);
    $form = [];
    $splitted_time = $this->splitDateTime($start_date->format('Y-m-d\TH:i:s'), $this->dateFormat, $this->timeFormat);
    $time_journey = $this->splitDateTime($start_date_valid_for, $this->dateFormat, $this->timeFormat);
    $month = $this->dateFormattedOk($splitted_time['date']);
    $date_parts = explode("/", $splitted_time['date']);
    $date_formated = "$date_parts[0]/$month/$date_parts[2]";
    $form['calendarLabel'] = $this->getCalendarLabel();
    $form['selectLabel'] = $this->getSelectJournalyLabel();
    $form['rescheduleDate']['value'] = $splitted_time['date'];
    $form['rescheduleDate']['formattedValue'] = $date_formated;
    $form['rescheduleDate']['filters'] = $this->getFilterListOfValidDates();
    //$form['rescheduleJourney']['value'] = $this->getValueJourney($start_date_valid_for);
    //$form['rescheduleJourney']['formattedValue'] = $time_journey['journey'];

    $form['rescheduleJourney']['value'] = str_replace('-',' a ',$this->getFilterAvailableJournies()[0]["value"]);
    $form['rescheduleJourney']['formattedValue'] = str_replace("Tarde de ", "",$this->getFilterAvailableJournies()[0]["formattedValue"]);
    $form['rescheduleJourney']['options'] = $this->getFilterAvailableJournies();

    return $form;
  }


  public function getFilterListOfValidDates() {
    $filter_list_of_valid_dates = [];
    $list_of_valid_dates = $this->getSanitizeAvailableDates();
    $current_date_time_reschedule = $this->schedulingService->getCurrentDateTimeForReschedule();

    foreach($list_of_valid_dates["options"] as $valid_date) {
      $option_date = $this->schedulingService->formatDateTime($valid_date["formattedValue"]);
      if($option_date >= $current_date_time_reschedule) {
        $filter_list_of_valid_dates['options'][] = [
          'value' => $valid_date["value"],
          'formattedValue' => $valid_date["formattedValue"],
        ];
      }
    }

    return $filter_list_of_valid_dates;
  }

  public function getFilterAvailableJournies() {
    $filter_list_of_available_journies = [];
    $list_of_valid_available_journies = $this->getSanitizeAvailableJournies();
    $current_date_time_reschedule = $this->schedulingService->getCurrentDateTimeForReschedule();

    foreach ($list_of_valid_available_journies as $valid_date) {
      $option_date = $this->schedulingService->formatDateTime($valid_date["rescheduleDate"]);
      if ($option_date >= $current_date_time_reschedule) {
        $filter_list_of_available_journies[] = [
          'rescheduleDate' => $valid_date["rescheduleDate"],
          'value'          => $valid_date["value"],
          'formattedValue' => $valid_date["formattedValue"]
        ];
      }
    }

    return $filter_list_of_available_journies;
  }

  /**
   * Get Form Field.
   *
   * @param object $field
   *   Field.
   *
   * @return array
   *   form field specs
   */
  protected function getFormField($field) {
    $form_field = [];
    $arr_field_validations = ['required', 'minLength', 'maxLength'];
    foreach ($field as $id => $element) {
      if (in_array($id, $arr_field_validations)) {
        $value = $element;
        if ($id == 'required') {
          $value = (bool) $value;
        }
        $form_field['validations'][$id] = $value;
      }
      else {
        $value = $element;
        if ($id == 'show') {
          $value = (bool) $value;
        }
        $form_field[$id] = $value;
      }
    }
    return $form_field;
  }

  /**
   * GetSanitizeAvailableJournies.
   */
  private function getSanitizeAvailableJournies() {
    $date_time_format = $this->configBlock['others']['dateTimeForRescheduling']['format'];
    $num_journaly = $this->configBlock['others']['confReschedule']['journaly'] + 1;
    $response = [];
    $available_time_slots = $this->availableDates->availableTimeslots;
    $journaly = "";
    $i = 0;
    foreach ($available_time_slots as $slot) {
      $date = substr($slot->validFor->startDatetime, 0, 10);
      $time = substr($slot->validFor->startDatetime, 11, 19);
      $time_parts = explode(":", $time);
      if ($time_parts[0] >= 8 && $time_parts[0] < 13) {
        $journaly = "Mañana de ";
      }
      if ($time_parts[0] >= 13 && $time_parts[0] < 18) {
        $journaly = "Tarde de ";
      }
      $month = $this->dateFormattedOk($this->parseDate($slot->validFor->startDatetime));
      $date_parts = explode("/", $this->parseDate($slot->validFor->startDatetime));

      $date_formated = "$date_parts[0]/$month/$date_parts[2]";

      if ($response[$i-1]['rescheduleDate'] && $num_journaly == 1) {
        if ($response[$i-1]['rescheduleDate'] != $date_formated) {
          $response[$i] = [
            'rescheduleDate' => $date_formated,
            'value' => $this->parseDateTime($slot->validFor->startDatetime, $slot->validFor->endDatetime, $date_time_format),
            'formattedValue' => $journaly .
            $this->parseTime($slot->validFor->startDatetime) . ' a ' . $this->parseTime($slot->validFor->endDatetime),
          ];
          $i++;
        }
      }else {
        $response[] = [
          'rescheduleDate' => $date_formated,
          'value' => $this->parseDateTime($slot->validFor->startDatetime, $slot->validFor->endDatetime, $date_time_format),
          'formattedValue' => $journaly .
          $this->parseTime($slot->validFor->startDatetime) . ' a ' . $this->parseTime($slot->validFor->endDatetime),
        ];
        $i++;
      }

    }
    return $response;
  }

  /**
   * GetSanitizeAvailableDates.
   */
  private function getSanitizeAvailableDates() {
    $response = [
      'options' => [],
    ];
    $data_f = $this->availableDates->availableTimeslots;
    $diff_date = [];
    $available_ime_slots = $data_f;
    foreach ($available_ime_slots as $slot) {
      $start_date = $this->parseDate($slot->validFor->startDatetime);
      $start_date_formatted = $this->dateFormattedOkCalendar($start_date);
      $end_date = $this->parseDate($slot->validFor->endDatetime);
      $end_date_formatted = $this->dateFormattedOkCalendar($start_date);

      if (!in_array($start_date_formatted, $diff_date)) {
        $diff_date[] = $start_date_formatted;
      }
      if (!in_array($end_date_formatted, $diff_date)) {
        $diff_date[] = $end_date_formatted;
      }
    }
    foreach ($diff_date as $date) {
      $response['options'][] = [
        'value' => $date,
        'formattedValue' => $date,
      ];
    }
    return $response;
  }

  /**
   * Parse dateTime var in date format.
   *
   * @param string $date_time
   *   dateTime to parse.
   *
   * @return string
   *   Return string in date format
   */
  private function parseDate($date_time) {
    $time = strtotime($date_time);
    return date($this->dateFormat, $time);
  }

  /**
   * Parse dateTime var in time format.
   *
   * @param string $date_time
   *   dateTime to parse.
   *
   * @return string
   *   Return sting in time format
   */
  private function parseTime($date_time) {
    $time_zone = $this->configBlock['others']['confReschedule']['timeZone'];
    $date_time = str_replace('Z', $time_zone, $date_time);
    $time = strtotime($date_time);
    return date($this->timeFormat, $time);
  }

  /**
   * Parse dateTime var in time format.
   *
   * @param string $date_time
   *   dateTime to parse.
   *
   * @return string
   *   Return sting in time format
   */
  private function parseDateTime($date_time_start, $date_time_end, $format) {
    $time_zone = $this->configBlock['others']['appointmentDateTime']['timeZone'];
    $date_time_start = str_replace('Z', $time_zone, $date_time_start);
    $date_time_end = str_replace('Z', $time_zone, $date_time_end);
    $time_start = strtotime($date_time_start);
    $time_end = strtotime($date_time_end);
    $time_format = explode('\T', $format)[1];
    return date($format, $time_start) . '-' . date($time_format, $time_end);
  }

  /**
   * Get Value of Journey
   *
   * @param string $date_time
   *   dateTime to parse.
   *
   * @return string
   *   Return string in time format
   */
  private function getValueJourney($date_time_start) {
    if (isset($this->configBlock['others']['dateTimeForRescheduling']['format'])) {
      $format = $this->configBlock['others']['dateTimeForRescheduling']['format'];
      $formats = explode('\T', $format);
      $date_format = $formats[0];
      $time_format = $formats[1];
      $splitted_time = $this->splitDateTime(
        $date_time_start, $date_format, $time_format);
      return $splitted_time['date'] . 'T' .  $splitted_time['journey'];
    }
    else {
      return '';
    }

  }

  /**
   * Split DateTime.
   *
   * @param string $appointment_date_time
   *   AppointmentDateTime.
   * @param string $date_format
   *   dateFormat.
   * @param string $time_format
   *   timeFormat.
   */
  public function splitDateTime($appointment_date_time, $date_format, $time_format) {
    $date = substr($appointment_date_time, 0, 10);
    $end = "";
    $time = strtotime($date);
    $journey = substr($appointment_date_time, 11, 19);
    $time_parts = explode(':', $journey);
    if (isset($time_parts[0]) && isset($time_parts[1]) && isset($time_parts[2])) {
      $start = $time_parts[0] . $time_parts[1] . $time_parts[2];
    }
    $time_start = strtotime($start);
    $time_end = strtotime($end);
    return [
      'date' => date($date_format, $time),
      'journey' => date($time_format, $time_start) . ' a ' . date($time_format, $time_end),
    ];
  }

  /**
   * Split DateTime.
   *
   * @param string $appointment_date_time
   *   AppointmentDateTime.
   * @param string $date_format
   *   dateFormat.
   * @param string $time_format
   *   timeFormat.
   */
  public function dateFormattedOk($date) {
    $month = "";
    $date_parts = explode("/", $date);
    switch ($date_parts[1]) {
      case 'Jan':
        $month = "Ene";
        break;
      case 'Feb':
        $month = "Feb";
        break;
      case 'Mar':
        $month = "Mar";
        break;
      case 'Apr':
        $month = "Abr";
        break;
      case 'May':
        $month = "May";
        break;
      case 'Jun':
        $month = "Jun";
        break;
      case 'Jul':
        $month = "Jul";
        break;
      case 'Aug':
        $month = "Ago";
        break;
      case 'Sep':
        $month = "Sep";
        break;
      case 'Oct':
        $month = "Oct";
        break;
      case 'Nov':
        $month = "Nov";
        break;
       case 'Dec':
        $month = "Dic";
        break;
      default:
        $month = "";
        break;
    }
    return $month;
  }
  /**
   * Split DateTime.
   *
   * @param string $appointment_date_time
   *   AppointmentDateTime.
   * @param string $date_format
   *   dateFormat.
   * @param string $time_format
   *   timeFormat.
   */
  public function dateFormattedOkCalendar($date) {
    $month = "";
    $date_parts = explode("/", $date);
    switch ($date_parts[1]) {
      case 'Jan':
        $month = "Ene";
        break;
      case 'Feb':
        $month = "Feb";
        break;
      case 'Mar':
        $month = "Mar";
        break;
      case 'Apr':
        $month = "Abr";
        break;
      case 'May':
        $month = "May";
        break;
      case 'Jun':
        $month = "Jun";
        break;
      case 'Jul':
        $month = "Jul";
        break;
      case 'Aug':
        $month = "Ago";
        break;
      case 'Sep':
        $month = "Sep";
        break;
      case 'Oct':
        $month = "Oct";
        break;
      case 'Nov':
        $month = "Nov";
        break;
       case 'Dec':
        $month = "Dic";
        break;
      default:
        $month = "";
        break;
    }
    return "$date_parts[0]/$month/$date_parts[2]";
  }

  /**
   * Format the reponse with the block configuarion values (In action section).
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function getActions() {
    $origin = $this->origin ?? (getallheaders()['Origin'] ?? '');
    return $this->utils->searchAndgetUrlsByOrigin($origin, $this->configBlock['actions']);
  }

  /**
   * Get Address array response.
   *
   * @return array
   *   Return fields as array of objects.
   */
  private function getAppointmentAddress($id) {
    $address = $this->utils->getAppointmentAddressByPrimarySubscriberId($id);
    return [
      'label' => $this->configBlock['others']['appointmentAddress']['label'],
      'show' => (bool) $this->configBlock['others']['appointmentAddress']['show'],
      'value' => $address,
      'formattedValue' => $address,
    ];
  }

  /**
   * Get Address array response.
   *
   * @return array
   *   Return fields as array of objects.
   */
  private function getAppointmentId() {
    return [
      'label' => $this->configBlock['others']['appointmentId']['label'],
      'show' => (bool) $this->configBlock['others']['appointmentId']['show'],
      'value' => $this->appointment->id,
      'formattedValue' => $this->appointment->id,
    ];
  }

  /**
   * Returns if Valid Visit Details.
   *
   * @param object $visit_details
   *   Visit Details.
   */
  public function isValidVisitDetails($visit_details) {
    $related = !empty($visit_details->relatedEntity) ? array_column($visit_details->relatedEntity, 'href') : [];
    $bac_id = in_array('billingAccountId', $related);
    return !empty($visit_details->id)
      && !empty($visit_details->externalId)
      && !empty($visit_details->products->id)
      && $bac_id;
  }

  /**
   * Returns Params Visit Details.
   *
   * @param object $visit_details
   *   Visit Details.
   */
  public function getRescheduleParams($visit_details) {
    foreach ($visit_details->relatedEntity as $value) {
      if ($value->href == 'billingAccountId') {
        $bac_id = $value->id;
        break;
      }
    }

    return [
      'id' => $bac_id,
      'appointmentId' => $visit_details->id,
      'externalId' => $visit_details->externalId,
      'productsId' => $visit_details->products->id,
    ];
  }

  /**
   * @param array $query_params
   * @return array
   */
  public function getRescheduleQuery(array $query_params) {
    try {
      $date_time = explode('-', $query_params['startDateTime']);
      $ini_date = str_replace('/', '-', $date_time[0]);
      $end_time = $date_time[1];
      $end_date = substr($ini_date, 0, 11) . $end_time;

    } catch (\Exception $e) {
      $ini_date = '';
      $end_date = '';
    }

    return [
      'startDateTime' => $ini_date,
      'endDateTime' => $end_date
    ];
  }

  /**
   * Returns Calendar Label
   *
   */
  private function getCalendarLabel() {
    return [
      'label' => '',
      'show' => (bool) $this->configBlock['others']['confReschedule']['show'],
      'value' => $this->configBlock['others']['confReschedule']['labelForm'],
      'formattedValue' => $this->configBlock['others']['confReschedule']['labelForm'],
    ];
  }

  /**
   * Returns Calendar Label
   *
   */
  private function getSelectJournalyLabel() {
    return [
      'label' => '',
      'show' => (bool) $this->configBlock['others']['confReschedule']['show'],
      'value' => $this->configBlock['others']['confReschedule']['labelFormJor'],
      'formattedValue' => $this->configBlock['others']['confReschedule']['labelFormJor'],
    ];
  }

  /**
   * Returns Contract Id data
   *
   */
  private function getContractId() {
    return [
      'label' => '',
      'value' => $this->contract,
      'formattedValue' => $this->contract,
      'show' => FALSE,
    ];
  }

  /**
   * Returns Appointment Type data
   *
   */
  private function getAppointmentType() {
    return [
      'label' => '',
      'value' => $this->appointment->workOrderType,
      'formattedValue' => $this->appointment->workOrderType,
      'show' => FALSE,
    ];
  }

  /**
   * getAppointmentStatus.
   *
   * @param string $value
   *   Value.
   */
  protected function getAppointmentStatus() {
    $formatted_value = '';
    $value = $this->appointment->status;
    $value = $this->schedulingService->getValueStatusForDescription($value, $this->appointment->description);
    foreach ($this->statesList as $state) {
      if ($state['value'] == $value) {
        $formatted_value = $state['label'];
      }
    }
    return [
      'label' => '',
      'value' => $value,
      'formattedValue' => $formatted_value,
      'show' => FAlSE,
    ];
  }
}
