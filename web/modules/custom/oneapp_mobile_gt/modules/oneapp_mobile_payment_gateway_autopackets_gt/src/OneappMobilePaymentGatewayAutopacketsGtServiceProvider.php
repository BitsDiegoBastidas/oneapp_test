<?php

namespace Drupal\oneapp_mobile_payment_gateway_autopackets_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_mobile_payment_gateway_autopackets_gt.
 *
 * @package Drupal\oneapp_mobile_payment_gateway_autopackets_gt
 */
class OneappMobilePaymentGatewayAutopacketsGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $enrollment_service = $container->getDefinition('oneapp_mobile_payment_gateway_autopackets.enrollments_service');
    $enrollment_service->setClass('Drupal\oneapp_mobile_payment_gateway_autopackets_gt\Services\EnrollmentsServiceGt');

    $details_auto_packets = $container->getDefinition('oneapp_mobile_payment_gateway_autopackets.v2_0.details_enrollment_rest_logic');
    $details_auto_packets->setClass('Drupal\oneapp_mobile_payment_gateway_autopackets_gt\Services\v2_0\DetailsAutoPacketsEnrollmentRestLogicGt');
  }

}
