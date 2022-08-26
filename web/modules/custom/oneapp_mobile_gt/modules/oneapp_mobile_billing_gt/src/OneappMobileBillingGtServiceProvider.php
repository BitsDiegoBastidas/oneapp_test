<?php

namespace Drupal\oneapp_mobile_billing_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_mobile_billing_gt.
 *
 * @package Drupal\oneapp_mobile_billing_gt
 */
class OneappMobileBillingGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $billing = $container->getDefinition('oneapp_mobile_billing.billing_service');
    $billing->setClass('Drupal\oneapp_mobile_billing_gt\Services\BillingServiceGt');

    $billing = $container->getDefinition('oneapp_mobile_billing.v2_0.sms_details_rest_logic');
    $billing->setClass('Drupal\oneapp_mobile_billing_gt\Services\v2_0\SmsDetailsGtRestLogic');

    //redefining
    $download = $container->getDefinition('oneapp_mobile_billing.v2_0.download_invoice_rest_logic');
    $download->setClass('Drupal\oneapp_mobile_billing_gt\Services\v2_0\DownloadInvoiceGtRestLogic');

    //Override oneapp_mobile_upselling.v2_0.internet_transparency_rest_logic
    $definition_internet_transparency = $container->getDefinition('oneapp_mobile_billing.v2_0.internet_transparency_rest_logic');
    $definition_internet_transparency->setClass('Drupal\oneapp_mobile_billing_gt\Services\v2_0\InternetTransparencyGtRestLogic');

    //Redefining
    $balance = $container->getDefinition('oneapp_mobile_billing.v2_0.balance_rest_logic');
    $balance->setClass('Drupal\oneapp_mobile_billing_gt\Services\v2_0\BalanceGtRestLogic');

    //Redefining
    $invoices = $container->getDefinition('oneapp_mobile_billing.v2_0.invoices_rest_logic');
    $invoices->setClass('Drupal\oneapp_mobile_billing_gt\Services\v2_0\InvoicesGtRestLogic');

    $calls = $container->getDefinition('oneapp_mobile_billing.v2_0.call_details_rest_logic');
    $calls->setClass('Drupal\oneapp_mobile_billing_gt\Services\v2_0\CallDetailsGtRestLogic');
  }

}
