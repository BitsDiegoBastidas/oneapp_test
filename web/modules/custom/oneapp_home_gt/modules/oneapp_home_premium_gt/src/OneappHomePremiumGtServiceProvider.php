<?php

namespace Drupal\oneapp_home_premium_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_mobile_services_gt.
 *
 * @package Drupal\oneapp_mobile_services_gt
 */
class OneappHomePremiumGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $psc = $container->getDefinition('oneapp_home_premium.premium_data');
    $psc->setClass('Drupal\oneapp_home_premium_gt\Services\PremiumServiceGt');

    $psc_amz = $container->getDefinition('oneapp_home_premium.amz');
    $psc_amz->setClass('Drupal\oneapp_home_premium_gt\Services\PremiumServiceAmzGt');
  }

}
