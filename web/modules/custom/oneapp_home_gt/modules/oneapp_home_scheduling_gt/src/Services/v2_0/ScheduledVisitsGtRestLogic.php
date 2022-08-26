<?php

namespace Drupal\oneapp_home_scheduling_gt\Services\v2_0;

use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\oneapp_home_scheduling\Services\v2_0\ScheduledVisitsRestLogic;

/**
 * Class ScheduledRestLogic.
 */
class ScheduledVisitsGtRestLogic extends ScheduledVisitsRestLogic {

  /**
   * Get Schedule by document number.
   *
   * @param string $id
   *   Number or contract (Or value of billing account).
   */
  public function get($id) {
    $visit_list = [];
    $scheduleds_response = null;
    $appointment_status = null;

    $info = $this->utils->getInfoTokenByBillingAccountId($id);

    if (empty($info['contractId']) || empty($info["subscriberId"])) {
      return $this->schedulingService::HIDE_STATE;
    }

    $scheduled_visits = $this->schedulingService->getVisitListEndpoint($info['contractId']);

    if (!empty($scheduled_visits) && is_array($scheduled_visits)) {
      foreach ($scheduled_visits as $visit) {
        $row = [];
        if (!empty($info["subscriberId"]) && !empty($visit->id) && !empty($visit->subid)) {
          $appointment_status = $this->schedulingService->getVisitDetailsById($info["subscriberId"], $visit->id, $visit->subid);
        }
        if (!empty($appointment_status->id)) {
          foreach ($this->configBlock['fields'] as $field_name => $field) {
            switch ($field_name) {
              case 'appointmentId':
                $row[$field_name]['label'] = $field['label'];
                $row[$field_name]['show'] = (bool) $field['show'];
                $row[$field_name]['value'] = $visit->id;
                $row[$field_name]['formattedValue'] = $visit->id;
                break;
              case 'subAppointmentId':
                $row[$field_name]['label'] = $field['label'];
                $row[$field_name]['show'] = (bool) $field['show'];
                $row[$field_name]['value'] = $visit->subid;
                $row[$field_name]['formattedValue'] = $visit->subid;
                break;
              case 'scheduleDate':
                $row[$field_name]['label'] = $field['label'];
                $row[$field_name]['show'] = (bool) $field['show'];
                $row[$field_name]['value'] = $appointment_status->validfor->startDateTime;
                $row[$field_name]['formattedValue'] = $this->schedulingService->formatCreationDate($appointment_status->validfor->startDateTime, $appointment_status->validfor->endDateTime,  $field['format']);
                break;
              case 'scheduleJourney':
                $row[$field_name]['label'] = $field['label'];
                $row[$field_name]['show'] = (bool) $field['show'];
                $row[$field_name]['formattedValue'] = t('@startHour - @endHour', [
                  '@startHour' => $this->schedulingService->formatScheduleJourneyDate($appointment_status->validfor->startDateTime, $field['format']),
                  '@endHour' => $this->schedulingService->formatScheduleJourneyDate($appointment_status->validfor->endDateTime, $field['format'])
                ]);
                break;
              case 'appointmentStatus':
                $row[$field_name]['label'] = $field['label'];
                $row[$field_name]['show'] = (bool) $field['show'];
                $row[$field_name]['class'] = $this->getAppointmentClass($appointment_status->status);
                $row[$field_name]['value'] = $this->getAppointmentStatus($appointment_status->status);
                $row[$field_name]['formattedValue'] = $this->getAppointmentStatus($appointment_status->status);
                break;
              default:
                break;
            }
          }
        }
        $actions = $this->configBlock['actions'];
        foreach ($actions as $action => $value) {
          $row[$action]['value'] = (bool) $value['show'];
        }
        array_push($visit_list, $row);
      }

      $filter_visit_list = $this->filterVisitListForCompletedStatus($visit_list);

      if (!count($filter_visit_list)) {
        return $this->schedulingService::HIDE_STATE;
      }

      $scheduleds_response = ["visitList"  => $filter_visit_list];
    }
    else {
      $scheduleds_response = $this->schedulingService::HIDE_STATE;
    }
    return $scheduleds_response;
  }

  /**
   * Format the reponse with the block configuarion values (In action section).
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function getActions() {
    $actions = $this->utils->searchAndgetUrlsByOrigin(getallheaders()['Origin'] ?? '', $this->configBlock['actions']);
    return $actions;
  }

  public function filterVisitListForCompletedStatus(array &$visit_list) {
    foreach ($visit_list as $key => $visit) {
      if ($this->isCompletedStatus($visit)
      && !$this->schedulingService->isCreationDateVisitLessThanSevenDays($visit["scheduleDate"]["value"])) {
        unset($visit_list[$key]);
      }
    }

    $filter_visit_list = [];

    foreach ($visit_list as $visit) {
      $filter_visit_list[] = [
        "appointmentId"     => $visit["appointmentId"],
        "subAppointmentId"  => $visit["subAppointmentId"],
        "scheduleDate"      => $visit["scheduleDate"],
        "scheduleJourney"   => $visit["scheduleJourney"],
        "appointmentStatus" => $visit["appointmentStatus"],
        "visitDetails"      => $visit["visitDetails"],
        "confirmVisit"      => $visit["confirmVisit"],
        "downloadPdf"       => $visit["downloadPdf"],
        "scheduleVisit"     => $visit["scheduleVisit"],
      ];
    }

    return $filter_visit_list;
  }

  public function isCompletedStatus(array $row) {
    return $row["appointmentStatus"]["formattedValue"] == "Completada" ? TRUE : FALSE;
  }

  public function getAppointmentStatus($appointment_status_receive) {
    $status_list = $this->utils->getConfigGroup('scheduling')['visit_status'];
    $appointment_status = null;
    foreach ($status_list['visit_status_list'] as $key => $status) {
      if ($status['label'] == $appointment_status_receive) {
        $appointment_status = $status['alternative_label'];
      }
    }
    return $appointment_status ? $appointment_status : $appointment_status_receive;
  }

  public function getAppointmentClass($appointment_status_receive) {
    $status_list = $this->utils->getConfigGroup('scheduling')['visit_status'];
    $appointment_status = null;
    foreach ($status_list['visit_status_list'] as $key => $status) {
      if ($status['label'] == $appointment_status_receive) {
        $appointment_status = $status['class'];
      }
    }
    return $appointment_status ? $appointment_status : $appointment_status_receive;
  }

  public function getAppointmentLabel($appointment_status_receive) {
    $status_list = $this->utils->getConfigGroup('scheduling')['visit_status'];
    $appointment_status = null;
    foreach ($status_list['visit_status_list'] as $key => $status) {
      if ($status['label'] == $appointment_status_receive) {
        $appointment_status = $status['label'];
      }
    }
    return $appointment_status ? $appointment_status : $appointment_status_receive;
  }
}
