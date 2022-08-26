<?php

namespace Drupal\oneapp_convergent_user_verification_b2b_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_convergent_user_verification_b2b_gt.
 *
 * @package Drupal\OneappConvergentUserVerificationB2bGt
 */
class OneappConvergentUserVerificationB2bGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('oneapp_convergent_user_validation_b2b.v2_0.rest_logic');
    $definition->setClass('Drupal\oneapp_convergent_user_verification_b2b_gt\Services\v2_0\UserVerificationB2bRestLogicGt');
  }

}
