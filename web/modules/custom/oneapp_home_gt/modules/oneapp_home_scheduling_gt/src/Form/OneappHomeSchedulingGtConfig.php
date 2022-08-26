<?php

namespace Drupal\oneapp_home_scheduling_gt\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oneapp_home_scheduling\Form\OneappHomeSchedulingConfig;

/**
 * Class OneappHomeSchedulingGtConfig.
 */
class OneappHomeSchedulingGtConfig extends OneappHomeSchedulingConfig {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'oneapp_home_scheduling_gt.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oneapp_home_scheduling_gt_config';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('oneapp_home_scheduling_gt.config')
      ->set('homologations', $form_state->getValue(['homologations', 'field_container']))
      ->set('type_orders', $form_state->getValue(['type_orders', 'field_container_orders']))
      ->save();
  }

}
