<?php

namespace Drupal\oneapp_home_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class OneappHomeGtServiceProvider.
 */
class OneappHomeGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $definition = $container->getDefinition('oneapp.home.utils');
    $definition->setClass('Drupal\oneapp_home_gt\Services\UtilsGtService');

    // Overrides class intraway
    $billingService = $container->getDefinition('oneapp.home.intraway');
    $billingService->setClass('Drupal\oneapp_home_gt\Services\IntrawayGtService');
  }

}
