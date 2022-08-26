<?php

namespace Drupal\oneapp_home_services_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class OneappHomeServicesGtServiceProvider.
 */
class OneappHomeServicesGtServiceProvider extends ServiceProviderBase {
  
  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $definition = $container->getDefinition('oneapp_home_services.v2_0.services_rest_logic');
    $definition->setClass('Drupal\oneapp_home_services_gt\Services\v2_0\ServicesGtRestLogic');
    $definition = $container->getDefinition('oneapp_home_services.v2_0.data_service');
    $definition->setClass('Drupal\oneapp_home_services_gt\Services\ServicesServiceGt');
    $definition = $container->getDefinition('oneapp_home_services.v2_0.outage_notification_logic');
    $definition->setClass('Drupal\oneapp_home_services_gt\Services\v2_0\OutageNotificationRestLogicGt');
  }

}
