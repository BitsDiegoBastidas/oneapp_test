<?php

namespace Drupal\oneapp_convergent_payment_gateway_autopayments_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class OneAppMobileUpsellingGtServiceProvider.
 */
class OneappConvergentPaymentGatewayAutopaymentsGtServiceProvider extends ServiceProviderBase {


  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    // Overrides cron class to use our own service.
    $definition = $container->getDefinition('recurring_payment_gateway.v2_0.details_invoice_enrollment_rest_logic');    
    $definition->setClass('Drupal\oneapp_convergent_payment_gateway_autopayments_gt\Services\v2_0\DetailsInvoiceEnrollmentGtRestLogic'); 
    // Overrides enrollments service.
    $definition = $container->getDefinition('oneapp_convergent_payment_gateway.recurring_payments.v2_0.enrollments');    
    $definition->setClass('Drupal\oneapp_convergent_payment_gateway_autopayments_gt\Services\EnrollmentsGtService'); 

  }

}
