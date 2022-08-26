<?php

namespace Drupal\oneapp_mobile_loan_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_mobile_loan_gt
 *
 * @package Drupal\oneapp_mobile_loan_gt
 */
class OneappMobileLoanGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $balance = $container->getDefinition('oneapp_mobile_loan.v2_0.loan_data_balance_rest_logic');
    $balance->setClass('Drupal\oneapp_mobile_loan_gt\Services\v2_0\LoanDataBalanceGtRestLogic');
  }

}
