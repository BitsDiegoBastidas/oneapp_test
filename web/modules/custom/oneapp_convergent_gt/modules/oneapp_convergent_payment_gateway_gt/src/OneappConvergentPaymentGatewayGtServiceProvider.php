<?php

namespace Drupal\oneapp_convergent_payment_gateway_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of OneappConvergentPaymentGatewayGtServiceProvider.
 *
 * @package Drupal\oneapp_convergent_payment_gateway_gt
 */
class OneappConvergentPaymentGatewayGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $invoices = $container->getDefinition('oneapp_convergent_payment_gateway.v2_0.utils_service');
    $invoices->setClass('Drupal\oneapp_convergent_payment_gateway_gt\Services\v2_0\UtilsGtService');
  }

}
