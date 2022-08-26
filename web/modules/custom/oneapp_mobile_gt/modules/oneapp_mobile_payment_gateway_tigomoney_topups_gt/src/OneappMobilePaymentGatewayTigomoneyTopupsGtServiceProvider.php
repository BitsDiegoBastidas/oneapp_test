<?php

namespace Drupal\oneapp_mobile_payment_gateway_tigomoney_topups_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_mobile_payment_gateway_tigomoney_topups_gt.
 *
 * @package Drupal\oneapp_mobile_payment_gateway_tigomoney_topups_gt
 */
class OneappMobilePaymentGatewayTigomoneyTopupsGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $definition_sync = $container->getDefinition('oneapp_mobile_payment_gateway_tigomoney_sync_topups.v2_0.payment_gateway_topups_rest_logic');
    $definition_sync->setClass('Drupal\oneapp_mobile_payment_gateway_tigomoney_topups_gt\Services\v2_0\PaymentGatewayTigomoneySyncTopupsRestLogicGt');
  }
}
