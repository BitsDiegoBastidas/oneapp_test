<?php

namespace Drupal\oneapp_home_gt\Services;

use Drupal\oneapp_home\Services\UtilsService;

/**
 * Class UtilsService.
 *
 * @package Drupal\oneapp_home\Services;
 */
class UtilsGtService  extends UtilsService
{

  /**
   * Get text of hasPayment.
   */
  public function getFormatValueHasPayment($hasPayment, $date)
  {

    if (empty($date)) {
      return t("Pagada");
    }

    if ($hasPayment) {
      return t("Pagada");
    } else {
      $now = strtotime(date("Y-m-d", time()));
      $dateExpiration = strtotime($date);
      if ($now > $dateExpiration) {
        return t("Vencido");
      }
      return t("Pendiente");
    }

  }

  /**
   * @param string $origin
   * @param array $urls
   * @return mixed|string
   */

  public function getUrlByOrigin($origin, $urls) {

    $config = \Drupal::config('oneapp.component.config')->get('webview_webcomponent')['configOrigen'];

    $url = '';

    if (!empty($config['oneappOrigin'])) {
      if ($config['oneappOrigin'] == $origin) {
        $url = (!empty($urls['oneapp'])) ? $urls['oneapp'] : '';
      }
    }

    if (!empty($config['selfcareOrigin'])) {
      if ($config['selfcareOrigin'] == $origin) {
        $url = (!empty($urls['selfcare'])) ? $urls['selfcare'] : '';
      }
    }

    return $url;
  }

  /**
   * @param string $origin
   * @param array $actions_array
   * @return array
   */
  public function searchAndgetUrlsByOrigin(string $origin, array $actions_array) {
    array_walk($actions_array, function (&$val, $idx, $origin) {
      if (!empty($val['url'] && is_array($val['url']))) {
        $val['url'] = $this->getUrlByOrigin($origin, $val['url']);
      }
      if (!empty($val['show'])) {
        $val['show'] = boolval($val['show']);
      }
    }, $origin);
    return $actions_array;
  }
}
