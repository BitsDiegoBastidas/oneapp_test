<?php

namespace Drupal\oneapp_mobile_plans_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class OneappMobilePlansGtServiceProvider.
 */
class OneappMobilePlansGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $definition = $container->getDefinition('oneapp_mobile_plans.v2_0.current_rest_logic');
    $definition->setClass('Drupal\oneapp_mobile_plans_gt\Services\v2_0\CurrentGtRestLogic');

    $current_roaming_summary = $container->getDefinition('oneapp_mobile_plans.v2_0.current_roaming_rest_logic');
    $current_roaming_summary->setClass('Drupal\oneapp_mobile_plans_gt\Services\v2_0\CurrentRoamingSummaryGtRestLogic');
  }

}
