<?php

namespace Drupal\oneapp_convergent_accounts_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of OneappConvergentAccountsGtServiceProvider.
 *
 * @package Drupal\oneapp_convergent_accounts_gt
 */
class OneappConvergentAccountsGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $invoices = $container->getDefinition('oneapp_convergent_accounts.v2_0.accounts');
    $invoices->setClass('Drupal\oneapp_convergent_accounts_gt\Services\v2_0\AccountsServiceGt');
  }

}
