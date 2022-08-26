<?php

namespace Drupal\oneapp_rest_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of OneappGtServiceProvider.
 *
 * @package Drupal\oneapp_rest_gt
 */
class OneappRestGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $restAccess = $container->getDefinition('oneapp_rest.access_rest');
    $restAccess->setClass('Drupal\oneapp_rest_gt\Services\v2_0\AccessAndCheckServiceGt');
  }

}
