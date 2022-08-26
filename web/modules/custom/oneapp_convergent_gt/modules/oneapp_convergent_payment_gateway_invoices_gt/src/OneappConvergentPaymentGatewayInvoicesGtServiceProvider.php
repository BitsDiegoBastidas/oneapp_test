<?php

namespace Drupal\oneapp_convergent_payment_gateway_invoices_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_convergent_payment_gateway_invoices_py.
 */
class OneappConvergentPaymentGatewayInvoicesGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    $definition = $container->getDefinition('oneapp_convergent_payment_gateway.v2_0.payment_gateway_rest_logic');
    $definition->setClass('Drupal\oneapp_convergent_payment_gateway_invoices_gt\Services\v2_0\PaymentGatewayRestLogicGt');

  }

}
