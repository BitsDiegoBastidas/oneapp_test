<?php

namespace Drupal\oneapp_home_scheduling_gt\Services;

use DateTime;
use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\oneapp_home_scheduling\Services\SchedulingService;

/**
 * Class SchedulingServiceCr.
 */
class SchedulingServiceGt extends SchedulingService {
  /**
   * @param $start_date_time
   * @param $end_date_time
   * @param $date_format
   * @return mixed
   */
  public function formatCreationDate($start_date_time, $end_date_time, $date_format) {
    $data_time = $start_date_time ? $start_date_time : $end_date_time;
    $schedule_date = substr($data_time, 0, 10);
    return $this->utils->getFormattedValue($date_format, $schedule_date);
  }
  /**
   * @param $visit_day
   * @return string
   */
  public function getDiffHoursForRescheduleVisit($visit_day){
    try {
      $now = new DateTime();
      $future_date = new DateTime($visit_day);
      $interval = $future_date->diff($now);
      $days_to_hours = $interval->format("%a") * 24;
      $hours = $interval->format("%h");
      $minutes_to_hours = ($interval->format("%i") / 60);
      $diff_hourse = ($days_to_hours + $hours + $minutes_to_hours);
      return ($now > $future_date) ? ($diff_hourse * -1) : $diff_hourse;
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
  }

  public function isCreationDateVisitLessThanSevenDays($creation_date) {

    $now_date_time = new DateTime();
    $seven_days_gone_date_time = new DateTime();
    $seven_days_gone_current_date = $seven_days_gone_date_time->modify("-7 day");

    $currenta_day = new DateTime($now_date_time->format('Y-m-d'));
    $seven_days_gone = new DateTime($seven_days_gone_current_date->format('Y-m-d'));

    $creation_date_time = new DateTime($creation_date);
    $creation_date_time_formatted = new DateTime($creation_date_time->format('Y-m-d'));

    return ($creation_date_time_formatted >= $seven_days_gone &&  $creation_date_time_formatted <= $currenta_day) ? TRUE : FALSE;
  }

  public function getCurrentDateTimeForReschedule() {
    $now_date_time = new DateTime();
    $currenta_day = new DateTime($now_date_time->format('Y-m-d'));
    return $currenta_day->modify("+1 day");
  }

  public function formatDateTime($date) {
    $date_parts = explode("/", $date);
    $day   = $date_parts[0];
    $month = $this->getCurrentMonth($date_parts[1]);
    $year  = $date_parts[2];
    $date_to_modify = "{$year}-{$month}-{$day}";

    $time = strtotime($date_to_modify);
    $newformat = date('Y-m-d', $time);
    return new DateTime($newformat);
  }

  public function getCurrentMonth($month_string)
  {
    switch ($month_string) {
      case 'Ene':
        $month = "01";
        break;
      case 'Feb':
        $month = "02";
        break;
      case 'Mar':
        $month = "03";
        break;
      case 'Abr':
        $month = "04";
        break;
      case 'May':
        $month = "05";
        break;
      case 'Jun':
        $month = "06";
        break;
      case 'Jul':
        $month = "07";
        break;
      case 'Ago':
        $month = "08";
        break;
      case 'Sep':
        $month = "09";
        break;
      case 'Oct':
        $month = "10";
        break;
      case 'Nov':
        $month = "11";
        break;
      case 'Dic':
        $month = "12";
        break;
      default:
        $month = "";
        break;
    }
    return $month;
  }


  /**
   * @param $date_time
   * @param $date_format
   * @return false|string|string[]
   */
  public function formatScheduleJourneyDate($date_time,$date_format) {
    if (!empty($date_format)) {
      $journey_date = substr($date_time, 10, 9);
      return $this->utils->getFormattedValue($date_format, $journey_date);
    }
    return str_replace(' ','',date('g A', strtotime($date_time)));
  }


  /**
   * FormatVisitDateTime.
   *
   * @param string $appointment_date_time
   *   AppointmentDateTime.
   * @param string $date_format
   *   dateFormat.
   */
  public function formatVisitDate($appointment_date_time, $date_format) {
    $schedule_date = substr($appointment_date_time, 0, 10);
    return $this->utils->getFormattedValue($date_format, $schedule_date);
  }

  /**
   * FormatVisitDateTime.
   *
   * @param string $appointment_date_time
   *   AppointmentDateTime.
   * @param string $time_format
   *   timeFormat.
   */
  public function formatVisitJourney($appointment_date_time, $time_format) {
    $schedule_journey = explode(" ", substr($appointment_date_time, -18, 18));
    $start_journey = $this->utils->getFormattedValue($time_format, $schedule_journey[0]);
    $end_journey = $this->utils->getFormattedValue($time_format, $schedule_journey[1]);
    return (object) [
      'start' => $start_journey,
      'end' => $end_journey,
    ];
  }

  public function formatStartAndEndTimeForListDate($appointment_list_date_time, $time_format)
  {
    $start_journey = explode(" ", substr($appointment_list_date_time[0], -18, 18));
    $end_journey = explode(" ", substr($appointment_list_date_time[1], -18, 18));
    $start_journey = $this->utils->getFormattedValue($time_format, $start_journey[0]);
    $end_journey = $this->utils->getFormattedValue($time_format, $end_journey[0]);
    return (object) [
      'start' => $start_journey,
      'end' => $end_journey,
    ];
  }


  /**
   * getVisitDetailsById
   * @param int|string $id ContractId
   * @param int|string $appointment_id AppointmentId
   * @param int|string $sub_appointment_id
   * @return object|null
   */
  public function getVisitDetailsById($id, $appointment_id, $sub_appointment_id = '1') {
    $visit_details = NULL;
    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_details_endpoint')
        ->setParams([
          'id' => $id,
          'appointmentId' => $appointment_id,
          'subAppointmentId' => $sub_appointment_id
        ])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      // Filtra el listado de visitas por el Id de la Visita.
      foreach ($response->Appointment as $appointment) {
          $appointment_idd = explode("|",$appointment->id)[0];
        if ($appointment_idd == $appointment_id) {
            $visit_details = $appointment;
            break;
          }
      }
      return $visit_details;
    }
    catch (\Exception $e) {
      return $visit_details;
    }
  }

  public function getTechnicianLocation($appointment_id){
      try{
        return $this->manager
        ->load('oneapp_home_scheduling_v2_0_technician_location_visit_endpoint')
        ->setParams([
          'appointmentId' => $appointment_id
          ])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      }
      catch(\Exception $e){
          return $e->getMessage();
      }
  }

  /**
   * Send confirmation Appointment.
   *
   * @param array $body
   *   Body to send endpoint.
   * @param array $params
   *   Params to replace in path.
   * @param array $headers
   *   Headers to send endpoint.
   *
   * @return object
   *   Response data.
   */
  public function sendCancelVisitEndpoint(array $params, array $headers = []) {
    try {
      return $this->manager
        ->load('oneapp_home_scheduling_v2_0_cancel_visit_endpoint')
        ->setParams($params)
        ->setHeaders($headers)
        ->setBody([])
        ->setQuery([])
        ->sendRequest();
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Request to api for visit details.
   *
   * @param string $id
   *   Number or contract (Or value of billing account).
   *
   * @return object
   *   data from api.
   */
  public function getVisitListEndpoint($id) {
    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_scheduled_visits_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      return $response->Appointment;
    }
    catch (HttpException $exception) {
      return $exception->getMessage();
    }
  }

  /**
   * getValueStatusForDescription
   *
   * @param string $status
   *   Visit Status
   *
   * @param string $description
   *   Description of visit
   *
   * @param object|null $flags
   *   Flags of visit
   *
   * @return string
   *   status appointment by params
   */
  public function getValueStatusForDescription($status, $description, $flags = NULL) {

    if (isset($flags->isAppointmentConfirmed) && $flags->isAppointmentConfirmed &&
    ($status != "Técnico en Ruta" && $status != "Técnico en destino" && $status != "Finalizado")) {
      $status = "Confirmado";
    }

    return $status;
  }

  /**
   * Request to api for request call
   *
   * @param string $id
   *   Number or contract (Or value of billing account).
   *
   * @param string $appointment_id
   *   Id of visit
   *
   * @return object
   *   data from api.
   */
  public function requestCall($id, $appointment_id) {
    try {
      return $this->manager
        ->load('oneapp_home_scheduling_v2_0_technician_request_call_endpoint')
        ->setParams(['id' => $id, 'appointmentId' => $appointment_id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();

    }
    catch (HttpException $exception) {
      return $this->launchException($exception->getCode());
    }
  }

  public function launchException($error_code = 404) {
    $messages = $this->configBlock['message'];
    $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
    $message = ($error_code == 404) ? $title . $messages['empty']['label'] : $title . $messages['error']['label'];
    $error_base = new ErrorBase();
    $error_base->getError()->set('message', $message);
    throw new HttpException($error_code, $error_base);
  }

  /**
   * @param $external_appointment_id
   * @param $id
   * @return array|string
   */
  public function getReasonsCancelEndpoint($external_appointment_id, $id) {
    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_cancel_reasons_visit_endpoint')
        ->setParams([
          'id' => $id,
          'appointmentId' => $external_appointment_id,
          'externalId' => 1,
        ])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();

      return $response;
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        return [];
      }
      else {
       return $e->getMessage();
      }
    }
  }

  /**
   * Send confirmation Appointment.
   *
   * @param array $body
   *   Body to send endpoint.
   * @param array $params
   *   Params to replace in path.
   * @param array $headers
   *   Headers to send endpoint.
   *
   * @return object
   *   Response data.
   */
  public function sendConfirmVisitEndpoint(array $body, array $params, array $headers = []) {
    try {
      return $this->manager
        ->load('oneapp_home_scheduling_v2_0_confirm_visit_endpoint')
        ->setParams($params)
        ->setHeaders($headers)
        ->setQuery([])
        ->sendRequest();
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Send reschedule Visit.
   *
   * @param array $params
   *   Params to replace in path.
   * @param array $query
   *   Query params url to send endpoint .
   * @param array $headers
   *   Headers to send endpoint.
   *
   * @return object
   *   Response data.
   */
  public function sendRescheduleVisitEndpoint(array $params, array $query = [], array $headers = []) {
    try {
      return $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_reschedule_endpoint')
        ->setParams($params)
        ->setHeaders($headers)
        ->setQuery($query)
        ->setDecodeJson(FALSE)
        ->sendRequest();
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        return (object) ['code' => '404'];
      }
      elseif ($e->getCode() == 400) {
        return (object) ['code' => '404'];
      }
      else {
        return NULL;
      }
    }
  }


  /**
   * Request to api for visit details.
   *
   * @param string $id
   *   Number or contract (Or value of billing account).
   *
   * @return object
   *   data from api.
   */
  public function getVisitDetailsEndpoint($id) {
    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_details_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      return $response->Appointment;
    } catch (HttpException $exception) {
      return $exception->getMessage();
    }
  }

  /**
   * Retorna el detalle de la visita segun el subappointment id
   *
   * @param string $id
   *   Billing account Id.
   * @param string $appointment_id
   *   appointmentId.
   * @param string $query_params
   *   queryParams.
   *
   * @return HttpException|\Exception
   *   Subscriptions.
   */
  public function getVisitDetailsByIdSubApointment($id, $appointment_id, $query_params) {
    $visit_details = NULL;
    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_details_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      // Filtra el listado de visitas por el Id de la Visita.
      foreach ($response->Appointment as $appointment) {
        if ($appointment->id == $appointment_id && $appointment->subAppointmentID == $query_params['subAppointmentId']) {
          $visit_details = $appointment;
          break;
        }
      }
      return $visit_details;
    }
    catch (\Exception $e) {
      return $visit_details;
    }
  }

  /**
   * Request to api susbscriptions  by contract number.
   *
   * @param int $id
   *   Contract number to do query.
   *
   * @return object
   *   data from api.
   */
  public function getcurrentAddress($id) {
    $address = NULL;
    try {
      $response = $this->manager
        ->load('oneapp_home_services_v2_0_subscriptions_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      // Filtra el listado de visitas por el Id de la Visita.
      if (isset ($response[0]->offeringList)) {
        $address=$response[0]->offeringList[0]->serviceAddress;
      }
      return $address;
    }
    catch (\Exception $e) {
      return $address;
    }
  }


  /**
   * Retorna el listado de las fechas y horarios disponibles.
   *
   * @param string $id
   *   Billing account Id.
   * @param string $appointment_id
   *   appointmentId.
   * @param string $sub_appointment_id
   *   sub_appointmentId.
  * @param string $order_type
   *   OrderId.
   *
   * @return HttpException|\Exception
   *   Subscriptions.
   */
  public function retrieveAvailableDateList($id, $appointment_id, $sub_appointment_id, $order_type) {
    try {
      return $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_available_reschedule_endpoint')
        ->setParams([
          'id' => $id,
          'appointmentId' => $appointment_id,
          'subAppointmentID' => $sub_appointment_id,
        ])
        ->setHeaders([])
        ->setQuery([
          'appointmentTypeId' => $order_type
        ])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      if ($exception->getCode() == 404) {
        return null;
      }
      else {
        return $exception;
      }
    }
  }

  /**
   * Retorna el listado de las fechas y horaios disponibles.
   *
   * @param string $id
   *   Billing account Id.
   * @param string $external_appointment_id
   *   externalAppointmentId.
   * @param string $products_id
   *   productsId.
   * @param string $startDate
   *   startDate.
   * @param string $endDate
   *   endDate.
   *
   * @return array
   *   Subscriptions.
   */
  public function retrieveAvailableDatesByRange($id, $appointment_id, $sub_appointment_id, $start_date, $end_date) {
    try {
      return $this->manager
      ->load('oneapp_home_scheduling_v2_0_visit_available_reschedule_endpoint')
      ->setParams([
        'id' => $id,
        'externalAppointmentId' => $appointment_id,
        'productsId' => urlencode($sub_appointment_id),
      ])
      ->setHeaders([])
      ->setQuery([
        'startDateTime' => $start_date,
        'endDateTime' => $end_date,
      ])
      ->sendRequest();
    } catch (\Exception $e) {
      return [
        'code' => 404,
        'message' => $e->getMessage(),
        'status' => 'failed',
      ];
    }

  }

  /**
   * Request to api Scheduled details by id visit.
   *
   * @param int $id
   *   Id visit to do query.
   *
   * @return object
   *   data from api.
   */
  public function getScheduledEndpoint($id) {
    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_details_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      return $response->Appointment;
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404 || $e->getCode() == 429 ) {
        return [];
      }
      else {
        $message = $e->getMessage();
        $this->apiErrorResponse->getError()->set('message', isset($message) ? $message : 'Not Found');
        throw new NotFoundHttpException($this->apiErrorResponse, $e);
      }
    }
  }

  public function getScheduledEndpointBySubAppointmentId($id, $query_params) {
    $visit_details = null;

    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_details_endpoint')
        ->setParams([
          'id' => $id
        ])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();

      foreach ($response->Appointment as $appointment) {
        if ($appointment->id == $query_params['visitId'] && $appointment->subAppointmentID == $query_params['subAppointmentId']) {
          $visit_details = $appointment;
          break;
        }
      }

      return $visit_details;
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        return [];
      }
      else {
        $message = $e->getMessage();
        $this->apiErrorResponse->getError()->set('message', isset($message) ? $message : 'Not Found');
        throw new NotFoundHttpException($this->apiErrorResponse, $e);
      }
    }
  }

  /**
   * Request to api Scheduled by id visit.
   *
   * @param int $id
   *  Id visit to do query.
   *
   * @return object
   *   data from api.
   */
  public function getScheduledDetailEndpoint($id) {
    try {
      $response = $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_details_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
      return $response->Appointment;
    }
    catch (\Exception $e) {
      if ($e->getCode() == 404) {
        return [];
      }
      else {
        $message = $e->getMessage();
        $this->apiErrorResponse->getError()->set('message', isset($message) ? $message : 'Not Found');
        throw new NotFoundHttpException($this->apiErrorResponse, $e);
      }
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
    $date = str_replace("/", "-", $date);
    $date_parts = explode("-",$date);
   // $date_parts = (object) date_parse($date);
    $date = "$date_parts[2]-$date_parts[1]-$date_parts[0]";
    $time = strtotime($date);
    $journey = substr($appointment_date_time, -15, 15);
    $time_parts = explode('-', $journey);
    $start = isset($time_parts[0]) ? $time_parts[0] : 'now';
    $end = isset($time_parts[1]) ? $time_parts[1] : 'now';
    $time_start = strtotime($start);
    $time_end = strtotime($end);
    return [
      'date' => date($date_format, $time),
      'journey' => date($time_format, $time_start) . '-' . date($time_format, $time_end),
    ];
  }

  /**
   * Send reschedule Visit.
   *
   * @param array $params
   *   Params to replace in path.
   * @param array $query
   *   Query params url to send endpoint .
   * @param array $headers
   *   Headers to send endpoint.
   *
   * @return object
   *   Response data.
   */
  public function sendRescheduleVisitPatchEndpoint(array $params, array $query = [], array $headers = []) {
    try {
      return $this->manager
        ->load('oneapp_home_scheduling_v2_0_visit_reschedule_endpoint')
        ->setParams($params)
        ->setHeaders($headers)
        ->setQuery($query)
        ->sendRequest();
    }
    catch (\Exception $e) {
      return (object) [
        'code' => 404,
        'message' => $e->getMessage(),
        'status' => 'failed',
      ];
    }
  }

}
