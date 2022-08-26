<?php

namespace Drupal\oneapp_home_payment_gateway_packets_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_home_payment_gateway_packets.v2_0.payment_gateway_packets_rest_logic.
 *
 * @package Drupal\oneapp_home_payment_gateway_packets_gt
 */
class OneappHomePaymentGatewayPacketsGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc} oneapp_home_payment_gateway_packets.v2_0.payment_gateway_packets_rest_logic
   */
  public function alter(ContainerBuilder $container) {
    $paymentGatewayPackets = $container->getDefinition('oneapp_home_payment_gateway_packets.v2_0.payment_gateway_packets_rest_logic');
    $paymentGatewayPackets->setClass('Drupal\oneapp_home_payment_gateway_packets_gt\Services\v2_0\PaymentGatewayPacketsRestLogicGt'); 
  }

}
