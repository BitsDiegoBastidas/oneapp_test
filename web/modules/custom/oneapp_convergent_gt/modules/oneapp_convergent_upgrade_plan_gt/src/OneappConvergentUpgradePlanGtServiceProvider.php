<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_convergent_upgrade_plan_gt.
 *
 * @package Drupal\OneappConvergentUpgradePlanGt
 */
class OneappConvergentUpgradePlanGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    //Overrides cron class to use our own service.
    $definition = $container->getDefinition('oneapp_convergent_upgrade_plan.v2_0.plan_card_rest_logic');
    $definition->setClass('Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradePlanCardGtRestLogic');

    $definition = $container->getDefinition('oneapp_convergent_upgrade_plan.v2_0.plan_send_rest_logic');
    $definition->setClass('\Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradePlanSendGtRestLogic');

    $definition = $container->getDefinition('oneapp_convergent_upgrade_plan.v2_0.recommended_offers_rest_logic');
    $definition->setClass('\Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradeRecommendedOffersGtRestLogic');

    $definition = $container->getDefinition('oneapp_convergent_upgrade_plan.upgrade_service');
    $definition->setClass('Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradeServiceGt');

    $definition = $container->getDefinition('oneapp_convergent_upgrade_plan.v2_0.plan_mobile_card_rest_logic');
    $definition->setClass('Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradePlanMobileCardGtRestLogic');

    $definition = $container->getDefinition('oneapp_convergent_upgrade_plan.v2_0.plan_mobile_send_rest_logic');
    $definition->setClass('Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradePlanMobileSendGtRestLogic');

    $definition = $container->getDefinition('oneapp_convergent_upgrade_plan.v2_0.recommended_offers_mobile_rest_logic');
    $definition->setClass('Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradeRecommendedOffersMobileGtRestLogic');
  }
}
