<?php

namespace Drupal\oneapp_mobile_premium_gt\Plugin\Block\v2_0;

use Drupal\oneapp_mobile_premium\Plugin\Block\v2_0\PremiumDetailBlock;

/**
 * {@inheritdoc}
 */
class PremiumDetailBlockGt extends PremiumDetailBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    parent::defaultConfiguration();
    $this->actions['detail_active']['buttons']['send_license'] = [
      'show' => FALSE,
      'label' => 'RECIBIR LICENCIA POR SMS',
      'type' => 'link',
    ];

    $this->messages['license_success'] = [
      'title' => 'Se ha enviado la licencia por SMS',
      'body' => '',
    ];
    $this->messages['license_failed'] = [
      'title' => 'No se pudo enviar la licencia',
      'body' => '',
    ];

    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {
      return [
        'fields' => $this->fields,
        'actions' => $this->actions,
        'messages' => $this->messages,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

}
