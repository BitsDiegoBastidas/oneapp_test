<?php

namespace Drupal\oneapp_home_billing_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_home_billing_gt.
 *
 * @package Drupal\oneapp_home_billing_gt
 */
class OneappHomeBillingGTServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $billing_data = $container->getDefinition('oneapp_home_billing.billing_data');
    $billing_data->setClass('Drupal\oneapp_home_billing_gt\Services\BillingServiceGt');

    //alter download_rest_logic
    $download = $container->getDefinition('oneapp_home_billing.v2_0.download_rest_logic');
    $download->setClass('Drupal\oneapp_home_billing_gt\Services\v2_0\DownloadInvoiceGtRestLogic');

    //oneapp_home_billing.v2_0.balance_rest_logic
    $balance = $container->getDefinition('oneapp_home_billing.v2_0.balance_rest_logic');
    $balance->setClass('Drupal\oneapp_home_billing_gt\Services\v2_0\BalanceGtRestLogic');

    //oneapp_home_billing.v2_0.invoices_rest_logic
    $invoices = $container->getDefinition('oneapp_home_billing.v2_0.invoices_rest_logic');
    $invoices->setClass('Drupal\oneapp_home_billing_gt\Services\v2_0\InvoicesGtRestLogic');

    //oneapp_home_billing.v2_0.electronic_invoice_rest_logic
    $electronic_invoice = $container->getDefinition('oneapp_home_electronic_invoices.v2_0.electronic_invoice_rest_logic');
    $electronic_invoice->setClass('Drupal\oneapp_home_billing_gt\Services\v2_0\ElectronicInvoiceGtRestLogic');
  }

}
