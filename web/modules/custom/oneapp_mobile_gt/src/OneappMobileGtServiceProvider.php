<?php

namespace Drupal\oneapp_mobile_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of OneappMobileGtServiceProvider.
 *
 * @package Drupal\oneapp_mobile_gt
 */
class OneappMobileGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $invoices = $container->getDefinition('oneapp.mobile.accounts');
    $invoices->setClass('Drupal\oneapp_mobile_gt\Services\AccountsServiceGt');

    $utilsService = $container->getDefinition('oneapp.mobile.utils');
    $utilsService->setClass('Drupal\oneapp_mobile_gt\Services\UtilsServiceGt');
  }

}
