<?php

namespace Drupal\oneapp_home_scheduling_gt\Services\v2_0;

use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\oneapp_home_scheduling\Services\v2_0\VisitDetailsRestLogic;
use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\HttpException;
use Drupal\rest\ResourceResponse;

/**
 * Class VisitDetailsCrRestLogic.
 */
class VisitDetailsGtRestLogic extends VisitDetailsRestLogic {

  public $subscriberId = "";

  /**
   * @var \Drupal\oneapp_home_scheduling_gt\Services\SchedulingServiceGt
   */
  protected $schedulingService;

  /**
   * @var \Drupal\oneapp_home_gt\Services\UtilsGtService
   */
  protected $utils;

  /**
   * @var \Drupal\oneapp_endpoints\Services\Manager
   */
  protected $manager;

  /**
   * @var string
   */
  protected $origin;

  /**
   * Get Schedule by document number.
   *
   * @param string $id
   *   Number or contract (Or value of billing account).
   * @param string $appointment_id
   *   Id of appointment or visit.
   */
  public function get($id, $appointment_id) {
    $this->origin = getallheaders()['Origin'] ?? '';
    $this->appointmentId = $appointment_id;
    $this->statesList = $this->schedulingService->getStatesVisits();
    $this->cancelForm = $this->schedulingService->getVisitCancelFormConfig();
    $this->classesStates = $this->schedulingService->getClassesStatesConfig();
    $subAppointmentId = $_REQUEST['subAppointmentId'];

    if (empty($id) || empty($this->appointmentId) || empty($subAppointmentId)) {
      return $this->schedulingService::HIDE_STATE;
    }

    $info = $this->utils->getInfoTokenByBillingAccountId($id);
    $this->id = $info["subscriberId"];
    $visit_responses = $this->schedulingService->getVisitDetailsById($info["subscriberId"], $this->appointmentId, $subAppointmentId);

    if (empty($info["subscriberId"] || empty($visit_responses->id))) {
      return $this->schedulingService::HIDE_STATE;
    }

    $visit_response[0] = $visit_responses;
    $response = [];
    if (isset($visit_responses->noData)) {
      $response = $visit_response;
    }
    else {
      $response = $this->findAndSanitize($visit_response, $this->id);
      if (!isset($response['noData'])) {
        $response['visitStatesList'] = $this->getSanitizeStateList($this->statesList,
          $response['visitDetails']['appointmentStatus']['value']);
      }
    }
    return $response;
  }

  /**
   * Get state list.
   *
   * @param array $states_list
   * @param string $status
   *
   * @return array
   */
  protected function getSanitizeStateList(array $states_list, $status) {
    usort($states_list, function ($a, $b) {
      return $a['weight'] > $b['weight'];
    });

    $sanitize_state_list = [];
    $active_class = $this->utils->getConfigGroup('scheduling')['visit_status']['visit_status_active'];
    $inactive_class = $this->utils->getConfigGroup('scheduling')['visit_status']['visit_status_inactive'];

    foreach($states_list as $state) {
      if (!$this->searchState($sanitize_state_list, $state['alternative_label'])) {
        $sanitize_state_list[] = [
          'label' => '',
          'class' => '',
          'value' => $state["value"],
          'formattedValue' => $state["alternative_label"],
          'show' => (bool) $state['show']
        ];
      }
    }

    $key_open = array_search("Abierto", array_column($sanitize_state_list, "formattedValue"));
    $key_accepted = array_search("Aceptado", array_column($sanitize_state_list, "formattedValue"));

    if(!empty($key_open)) {
      unset($sanitize_state_list[$key_open]);
    }

    if(!empty($key_accepted)){
      unset($sanitize_state_list[$key_accepted]);
    }

    $status_active = $this->getAppointmentStatus();
    $pos_status_sanitize_state = -1;
    foreach ($sanitize_state_list as  $key => $state) {
      if ($state["formattedValue"] == $status_active) {
        $pos_status_sanitize_state = $key;
      }
    }

    for ($i = 0; $i <= $pos_status_sanitize_state; $i++) {
      $sanitize_state_list[$i]['class'] = $active_class;
    }

    $nex_position = $pos_status_sanitize_state + 1;
    $total_position = (count($sanitize_state_list) - 1);

    if($nex_position <= $total_position){
      for ($i = $nex_position; $i <= $total_position; $i++) {
        $sanitize_state_list[$i]['class'] = $inactive_class;
      }
    }

    if ($this->appointmentDataReceive->status == "Incompleto" || $this->appointmentDataReceive->status == "Rechazado") {
      if ($this->searchState($sanitize_state_list, "Completada")) {
        $key = $this->returnKeyStatusForList($sanitize_state_list, "Completada");
        unset($sanitize_state_list[$key]);
      }
    }
    elseif ($this->appointmentDataReceive->status == "Finalizada" || $this->appointmentDataReceive->status == "Cancelado") {
      if ($this->searchState($sanitize_state_list, "Pendiente de Reagendar")) {
        $key = $this->returnKeyStatusForList($sanitize_state_list, "Pendiente de Reagendar");
        unset($sanitize_state_list[$key]);
      }
    }
    elseif ($this->searchState($sanitize_state_list, "Pendiente de Reagendar") && $this->searchState($sanitize_state_list, "Completada")) {
      $key = $this->returnKeyStatusForList($sanitize_state_list, "Pendiente de Reagendar");
      unset($sanitize_state_list[$key]);
    }

    $return_data = [];

    foreach($sanitize_state_list as $data) {
      $return_data[] = [
        'label' => '',
        'class' => $data['class'],
        'value' => $data["value"],
        'formattedValue' => $data["formattedValue"],
        'show' => (bool) $data['show']
      ];
    }

    return $return_data;
  }

  /**
   * Get Key for status.
   *
   * @param array $states_list
   * @param string $formatted_value
   *
   * @return null | int
   */
  public function returnKeyStatusForList($states_list, $formatted_value){
    if (!count($states_list)) {
      return NULL;
    }

    foreach ($states_list as  $key => $state) {
      if ($state["formattedValue"] == $formatted_value) {
        return $key;
      }
    }
    return NULL;
  }

  /**
   * Get search state.
   *
   * @param array $states_list
   * @param string $state_search
   *
   * @return bool
   */
  public function searchState($states_list, $state_search) {
    if (!count($states_list)) {
      return FALSE;
    }

    foreach ($states_list as $state) {
      if ($state["formattedValue"] == $state_search) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Get email message.
   *
   * @param string $message
   *
   * @return string
   */
  protected function getSuccessMessageWithEmail($message) {
    $email = $this->schedulingService->getEmailFromToken();
    $message = str_replace('@email', $email, $message);
    return $message;
  }

  /**
   * Send a email.
   *
   * @param string $template
   *
   * @return void
   */
  protected function sendEmail($template = 'schedule') {
    $this->schedulingService->sendEmail($template, $this->subscriberId);
  }

  /**
   * Format the reponse with the block configuarion values (In action section).
   *
   * @param string $status
   *   Status of appointment or visit.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function getActions($status) {
    $actions = $this->configBlock['actions'];
    $actions['confirmVisit']['show'] = (bool)$actions['confirmVisit']['show'];
    $actions['cancelVisit']['show'] = (bool)$actions['cancelVisit']['show'];
    $actions['rescheduleVisit']['show'] = (bool) $this->getValueShowRescheduleVisit();
    $actions['locateTechnician']['show'] = ($this->isCurrentStatusForLocateTechnician() && !is_null($this->getTechnicianLocationUrl())) ? TRUE : FALSE;
    $actions['locateTechnician']['url'] = $this->getTechnicianLocationUrl();
    $actions['callVisit']['show'] = (bool)$actions['callVisit']['show'];
    $actions['requestContact']['show'] = (bool)$actions['requestContact']['show'];

    foreach ($actions as &$action) {
      unset($action['showConditional']);
    }

    $this->insertVisitFailMessageInResponse($actions);

    return $actions;
  }

  /**
   * Current status Techician.
   *
   * @return bool
   */
  public function isCurrentStatusForLocateTechnician() {
    return in_array($this->getAppointmentStatus(), ["En Sitio", "En Camino"]);
  }

  /**
   * Get Url Location Technician.
   *
   * @return string | null
   */
  public function getTechnicianLocationUrl() {
    $response = $this->schedulingService->getTechnicianLocation($this->appointmentId);
    return isset($response->mission_url) ? $response->mission_url : NULL;
  }

  /**
   * Add failed message for reschedule visit.
   *
   * @param array $actions
   * @return void
   */
  public function insertVisitFailMessageInResponse(array &$actions) {
    $message = $this->utils->getConfigGroup('scheduling')['visit_failed_form']['success']['mail']['body']['value'];
    $actions['rescheduleVisit']["message"] = $message ? $message : "";
  }

  /**
   * Find and sanitize into an array an appointmentId.
   *
   * @param array $arr
   *   List of visits.
   * @param string $appointment_id
   *   ID to find.
   */
  protected function findAndSanitize(array $arr, $appointment_id) {
    $result = [];
    $technician_data = null;
    $appointment = NULL;
    foreach ($arr as $element) {
        $appointment = $element;
    }

    foreach($appointment->relatedParty as $relatedParty){
      if($relatedParty->role == "technician"){
          $technician_data = $relatedParty;
      }
    }

    if (isset($appointment)) {
      $this->appointmentDataReceive = $appointment;
      $date_format = $this->configBlock['fields']['scheduleDate']['format'];
      $time_format = $this->configBlock['fields']['scheduleJourney']['format'];
      $date_visit = $this->schedulingService->formatVisitDate($appointment->validfor->startDateTime, $date_format);
      $journey = $this->schedulingService->formatStartAndEndTimeForListDate([$appointment->validfor->startDateTime, $appointment->validfor->endDateTime], $time_format);
      $address = $this->utils->getAppointmentAddressByPrimarySubscriberId($this->id);
      $ids = explode('GTM-000', $appointment->id)[1];
      $appointmentsIds = explode('|',$ids);
      $appointment_status = $this->getAppointmentStatus();

      $values = [
        'appointmentId' => $appointmentsIds[0],
        'subAppointmentId' => $appointmentsIds[1],
        'scheduleDate' => $date_visit,
        'scheduleJourney' => t(
          '@startJourney - @endJourney',
          ['@startJourney' => $journey->start, '@endJourney' => $journey->end]
        ),
        'appointmentType' => $this->getTypeVisit(),
        'appointmentServices' => isset($appointment->products[0]->name) ? $appointment->products[0]->name : null,
        'appointmentContractId' => $this->id,
        'appointmentAddress' => $address ? strtolower($address) : NULL,
        'appointmentStatus' => $appointment_status,
        'technicianDocumentId' => isset($technician_data->documentId) ? $technician_data->documentId : null,
        'technicianName' => isset($technician_data->name) ? $technician_data->name : null,
        'technicianContractorCompany' => '',
        'requestContact' => '',
        'requestCall' => '',
        'technicianPicture' => isset($technician_data->photoURL) ? $technician_data->photoURL : null,
        'technicianPhone' => isset($technician_data->phone) ? $technician_data->phone : null
      ];
      $result['visitDetails'] = $this->fillConfigAndData('fields', $values);
      $result['technician'] = $this->fillConfigAndData('technician', $values);
    }
    else {
      return $this->schedulingService::EMPTY_STATE;
    }
    return $result;
  }

  /**
   * Get mapping status Appointment.
   *
   * @return string
   */
  public function getAppointmentStatus() {
    $status_list = $this->utils->getConfigGroup('scheduling')['visit_status'];
    $appointment_status = null;
    foreach ($status_list['visit_status_list'] as $status) {
         if ($status['label'] == $this->appointmentDataReceive->status) {
           $appointment_status = $status['alternative_label'];
       }
    }

    return $appointment_status ? $appointment_status: $this->appointmentDataReceive->status;
  }

  /**
   * Get visit type for scheduling.
   *
   * @return string
   */
  public function getTypeVisit() {
    $visit_type_list = $this->utils->getConfigGroup('scheduling')['visit_type_status'];
    $appointment_status = null;
    foreach ($visit_type_list['visit_type_status_list'] as $key => $type) {
      if ($type['label'] == $this->appointmentDataReceive->workOrderType) {
        $appointment_status = $type['alternative_label'];
      }
    }

    return $appointment_status ? $appointment_status: $this->appointmentDataReceive->status;
  }

  /**
   * Get Show field for Technician.
   *
   * @param string $key
   * @param string $field
   *
   * @return bool
   */
  protected function getShowForField($key, $field) {
    $unassigned_technician_value = $this->configBlock['others']['unassignedTechnician']['label'];
    $value = false;
    if ($key == 'technician') {
      if ($this->appointmentDataReceive->relatedParty[0]->name == " - ") {
        $value = false;
      }
      elseif ($this->appointmentDataReceive->relatedParty[0]->name != $unassigned_technician_value) {
        $value = (bool) $this->configBlock[$key][$field]['show'];
      }
    }
    else {
      $value = (bool) $this->configBlock[$key][$field]['show'];
    }
    return $value;
  }

  /**
   * Get configuration forn fields.
   *
   * @param string $key
   * @param string $field
   * @param object|string $value
   *
   * @return array
   */
  protected function getFieldConfigAndData($key, $field, $value) {
    $formatted_value = $value;
    $show = false;
    if ($field == 'appointmentStatus') {
      $formatted_value = $this->getFormattedStatusValue($value);
    }
    if ($field == 'appointmentContractId') {
      $formatted_value = $value;
    }
    if ($value != "") {
      if (gettype($value) == 'string' || gettype($value) == 'integer') {
        $show = (bool)$this->getShowForField($key, $field);
      }
      else {
        $arguments = $value->getArguments();
        if ($arguments['@startJourney'] == "" || $arguments['@endJourney'] == "") {
          $show = false;
        }
        else {
          $show = (bool)$this->getShowForField($key, $field);
        }
      }
    }
    if (isset($this->configBlock[$key][$field])) {
      return [
        'label' => $this->configBlock[$key][$field]['label'],
        'value' => isset($value) ? $value : '',
        'formattedValue' => isset($value) ? $formatted_value : '',
        'show' => $show,
      ];
    }

    return [];
  }

  /**
   * Get message for call.
   *
   * @return array
   */
  public function getContactMessage() {
    $call_message = [];
    $fields = $this->configBlock['fields'];
    $show_value = (bool) $fields['requestCall']['show'];

    $call_message['value'] = $fields['requestCall']['label'];
    $call_message['show'] = $show_value;

    return $call_message;
  }

  /**
   * Get Diff Hours For Reschedule Visit.
   *
   * @return bool
   */
  public function getValueShowRescheduleVisit() {
    if ($this->getAppointmentStatus() != "Asignada") {
      return FALSE;
    }
    $hours_for_reschedule_visit = $this->schedulingService->getDiffHoursForRescheduleVisit($this->appointmentDataReceive->validfor->startDateTime);
    return ($hours_for_reschedule_visit > 24) ? TRUE : FALSE;
  }

}

