<?php

namespace Drupal\oneapp_convergent_payment_gateway_tigomoney_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of OneappConvergentPaymentGatewayTigomoneyGtServiceProvider.
 *
 * @package Drupal\OneappConvergentPaymentGatewayTigomoneyGtServiceProvider
 */
class OneappConvergentPaymentGatewayTigomoneyGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $validity_tigomoney_definition = $container->getDefinition('oneapp_convergent_payment_gateway_tigomoney.v2_0.validityTigomoneyAccount_service');
    $validity_tigomoney_definition->setClass('Drupal\oneapp_convergent_payment_gateway_tigomoney_gt\Services\v2_0\ValidityTigomoneyAccountServiceGt');
  }

}
