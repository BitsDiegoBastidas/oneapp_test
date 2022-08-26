<?php

namespace Drupal\oneapp_mobile_premium_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_mobile_premium.
 *
 * @package Drupal\oneapp_mobile_premium_gt
 */
class OneappMobilePremiumGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    $psc = $container->getDefinition('oneapp_mobile_premium.premium_data');
    $psc->setClass('Drupal\oneapp_mobile_premium_gt\Services\PremiumServiceGt');

    $supermarket = $container->getDefinition('oneapp_mobile_premium.supermarket');
    $supermarket->setClass('Drupal\oneapp_mobile_premium_gt\Services\PremiumServiceSuperMarketGt');

    $detail = $container->getDefinition('oneapp_mobile_premium.v2_0.premium_detail_rest_logic');
    $detail->setClass('Drupal\oneapp_mobile_premium_gt\Services\v2_0\PremiumDetailRestLogicGt');

    $amz = $container->getDefinition('oneapp_mobile_premium.amz');
    $amz->setClass('Drupal\oneapp_mobile_premium_gt\Services\PremiumServiceAmzGt');

    $channel = $container->getDefinition('oneapp_mobile_premium.channel');
    $channel->setClass('Drupal\oneapp_mobile_premium_gt\Services\PremiumServiceChannelGt');

    $porfolio = $container->getDefinition('oneapp_mobile_premium.v2_0.premium_portfolio_rest_logic');
    $porfolio->setClass('Drupal\oneapp_mobile_premium_gt\Services\v2_0\PremiumPortfolioRestLogicGt');

    $products = $container->getDefinition('oneapp_mobile_premium.v2_0.premium_rest_logic');
    $products->setClass('Drupal\oneapp_mobile_premium_gt\Services\v2_0\PremiumRestLogicGt');  
  }


}
