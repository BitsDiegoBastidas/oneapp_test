<?php
namespace Drupal\oneapp_home_scheduling_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class OneappHomeSchedulingGtServiceProvider.
 */

class OneappHomeSchedulingGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $visit_details = $container->getDefinition('oneapp_home_scheduling.v2_0.visit_details_rest_logic');
    $visit_details->setClass('Drupal\oneapp_home_scheduling_gt\Services\v2_0\VisitDetailsGtRestLogic');
    $scheduling_service = $container->getDefinition('oneapp_home_scheduling.v2_0.scheduling_service');
    $scheduling_service->setClass('Drupal\oneapp_home_scheduling_gt\Services\SchedulingServiceGt');
    $schedule_visit = $container->getDefinition('oneapp_home_scheduling.v2_0.scheduled_visits_rest_logic');
    $schedule_visit->setClass('Drupal\oneapp_home_scheduling_gt\Services\v2_0\ScheduledVisitsGtRestLogic');
    $reschedule_visit = $container->getDefinition('oneapp_home_scheduling.v2_0.visit_reschedule_rest_logic');
    $reschedule_visit->setClass('Drupal\oneapp_home_scheduling_gt\Services\v2_0\VisitRescheduleGtRestLogic');
  }
}
