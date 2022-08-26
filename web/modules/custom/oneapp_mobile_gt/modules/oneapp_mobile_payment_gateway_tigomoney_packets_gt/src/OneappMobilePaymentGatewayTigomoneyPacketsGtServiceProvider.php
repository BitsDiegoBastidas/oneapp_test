<?php

namespace Drupal\oneapp_mobile_payment_gateway_tigomoney_packets_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_mobile_payment_gateway_tigomoney_packets_gt.
 *
 * @package Drupal\oneapp_mobile_payment_gateway_tigomoney_packets_gt
 */
class OneappMobilePaymentGatewayTigomoneyPacketsGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $definition_sync = $container->getDefinition('oneapp_mobile_payment_gateway_tigomoney_packets.v2_0.payment_gateway_tigomoney_sync_packets_rest_logic');
    $definition_sync->setClass('Drupal\oneapp_mobile_payment_gateway_tigomoney_packets_gt\Services\v2_0\PaymentGatewayTigomoneySyncPacketsRestLogicGt');
  }
}
