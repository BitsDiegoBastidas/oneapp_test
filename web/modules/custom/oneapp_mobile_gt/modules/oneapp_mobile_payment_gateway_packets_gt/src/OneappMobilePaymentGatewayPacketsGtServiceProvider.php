<?php

namespace Drupal\oneapp_mobile_payment_gateway_packets_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class PaymentGatewayPacketsServiceProvider.
 */
class OneappMobilePaymentGatewayPacketsGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $definition = $container->getDefinition('oneapp_mobile_payment_gateway_packets.v2_0.payment_gateway_packets_rest_logic');
    $definition->setClass('Drupal\oneapp_mobile_payment_gateway_packets_gt\Services\v2_0\PaymentGatewayPacketsRestLogicGt');

    $definition = $container->getDefinition('oneapp_mobile_payment_gateway_packets.v2_0.data_service');
    $definition->setClass('Drupal\oneapp_mobile_payment_gateway_packets_gt\Services\PaymentGatewayPacketsServiceGt');
  }

}
