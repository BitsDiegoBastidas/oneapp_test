<?php

namespace Drupal\oneapp_convergent_payment_gateway_tm_invoices_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of OneappConvergentPaymentGatewayTmInvoicesGtServiceProvider.
 *
 * @package Drupal\OneappConvergentPaymentGatewayTmInvoicesGtServiceProvider
 */
class OneappConvergentPaymentGatewayTmInvoicesGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('oneapp_convergent_payment_gateway_tigomoney.v2_0.payment_gateway_sync_rest_logic');
    $definition->setClass('Drupal\oneapp_convergent_payment_gateway_tm_invoices_gt\Services\v2_0\PaymentGatewayTmInvoicesSyncRestLogicGt');
  }

}
