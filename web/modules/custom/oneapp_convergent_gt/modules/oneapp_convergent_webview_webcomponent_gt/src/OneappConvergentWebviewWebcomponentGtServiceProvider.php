<?php

namespace Drupal\oneapp_convergent_webview_webcomponent_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the classes of oneapp_convergent_webview_webcomponent
 *
 * @package Drupal\oneapp_convergent_webview_webcomponent_gt
 */
class OneappConvergentWebviewWebcomponentGtServiceProvider extends ServiceProviderBase {
  
  public function alter(ContainerBuilder $container) {
    $convergent_webview = $container->getDefinition('oneapp_convergent_webview_webcomponent.v2_0.component_rest_logic');
    $convergent_webview->setClass('Drupal\oneapp_convergent_webview_webcomponent_gt\Services\v2_0\ComponentRestLogicGt');
  }
  
}
