<?php

namespace Drupal\oneapp_mobile_payment_gateway_topups_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class PaymentGatewayTopupsServiceProvider.
 */
class OneappMobilePaymentGatewayTopupsGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $definition = $container->getDefinition('oneapp_mobile_payment_gateway_topups.v2_0.generate_purchase_order_rest_logic');
    $definition->setClass('Drupal\oneapp_mobile_payment_gateway_topups_gt\Services\v2_0\GeneratePurchaseOrdersRestLogicGt');
  }

}
