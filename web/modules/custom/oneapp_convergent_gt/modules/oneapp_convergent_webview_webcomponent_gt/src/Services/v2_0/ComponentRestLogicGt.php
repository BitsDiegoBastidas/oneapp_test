<?php

namespace Drupal\oneapp_convergent_webview_webcomponent_gt\Services\v2_0;

use Drupal\oneapp_convergent_webview_webcomponent\Services\v2_0\ComponentRestLogic;

class ComponentRestLogicGt extends ComponentRestLogic {

  protected $account_is_dth = false;

  /**
   * Get data all component formated.
   *
   * @param array $filters
   *   Filters
   * @return array
   *   Return fields as array.
   */
  public function get($filters, $payload) {

    $this->payload = $payload;
    $this->setFilters($filters);
    // validar si la línea es dth asignarlo a $this->account_is_dth
    if ( \Drupal::hasService('oneapp_home_dth.service') ) {
      $dth_utils = \Drupal::service('oneapp_home_dth.service');
      $this->account_is_dth = $dth_utils->isDth($filters["id"]);
    }
    // validar si la cuenta es dth
    return $this->transformComponents();
  }

  /**
   * @inheritDoc
   */
  protected function transformComponents() {
    $components_array = [];

    $components = $this->filterComponents();

    $components_array = $this->getPageTypes();

    foreach ($components as $component) {

      $page_types = $this->getValue($component, 'page_type');
      $min_version = $this->getValue($component, 'min_version');
      $max_version = $this->getValue($component, 'max_version');
      $option_more = $this->getValue($component, 'more');
      $order = $this->getValue($component, 'component_order');
      $order_more = $this->getValue($component, 'component_order_more');
      $content_is_dth = $this->getValue($component, 'is_dth') ? true : false;

      $page_types = empty($page_types) ? [] : [$page_types];

      if ($option_more == '1') {
        $page_types[] = 'Mas';
      }

      foreach ($page_types as $page_type) {
        // ocultar los componentes dth cuando la cuenta (línea) no sea DTH y el contenido si
        $hide_dth_content = (!$this->account_is_dth && $content_is_dth);
        if (array_key_exists($page_type, $components_array) && !$hide_dth_content) {
          $components_array[$page_type]['items'][] = [
            'componentType' => $this->getValue($component, 'component_type'),
            'componentName' => $this->getValue($component, 'name'),
            'order' => $page_type != 'Más' ? $order : $order_more,
            'supportedVersions' => [
              'min' => empty($min_version) ? NULL : $min_version,
              'max' => empty($max_version) ? NULL : $max_version,
            ],
            'data' => $this->getDataComponent($component),
            ];
        }
      }
    }

    $components_result = array_values($components_array);

    foreach ($components_result as &$component) {
      array_multisort(array_column($component['items'], 'order'), SORT_ASC, $component['items']);

      foreach ($component['items'] as &$item) {
        unset($item['order']);
      }
    }

    return $components_result;
  }

}
