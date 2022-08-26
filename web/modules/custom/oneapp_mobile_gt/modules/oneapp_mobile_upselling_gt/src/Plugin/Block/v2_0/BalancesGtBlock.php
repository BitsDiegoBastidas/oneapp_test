<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Block\v2_0;

use Drupal\adf_block_config\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\oneapp_mobile_upselling\Plugin\Block\v2_0\BalancesBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;


class BalancesGtBlock extends BalancesBlock {



  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->contentFields = [
      'general' => [
        'subtitle' => [
          'label' => $this->t('Saldo Disponible'),
          'type' => 'textfield',
          'show' => TRUE,
        ],
        'dateFormat_update' => [
          'title' => $this->t('Formato de fecha para label update:'),
          'label' => '',
          'pattern' => '',
          'type' => 'select',
        ],
        'label_details' => [
          'title' => $this->t('Label details'),
          'label' => 'Detalles',
          'show' => 1,
        ],
      ],
      'headerList' => [
        'fields' => [
          'name' => [
            'title' => $this->t('Tipo de saldo:'),
            'label' => 'Tipo de saldo',
            'show' => 'TRUE',
            'weight' => 1,
            'responsiveWeight' => 'high',
          ],
          'remainingAmount' => [
            'title' => $this->t('Valor:'),
            'label' => '',
            'show' => TRUE,
            'weight' => 2,
            'responsiveWeight' => 'high',
          ],
          'endDateTime' => [
            'title' => $this->t('Vence:'),
            'label' => 'Vence',
            'show' => TRUE,
            'weight' => 3,
            'responsiveWeight' => 'high',
          ],
        ],
      ],
      'buttons' => [
        'reloadButton' => [
          'title' => $this->t('Label Boton Recargar'),
          'label' => $this->t('Recargar'),
          'url' => '/',
          'type' => 'button',
          'show' => TRUE,
        ],
        'transferButton' => [
          'title' => $this->t('Label Boton Tranferir'),
          'label' => $this->t('Transferir'),
          'url' => '/',
          'type' => 'button',
          'show' => FALSE,
        ],
        'updateButton' => [
          'label' => $this->t('Actualizado hace'),
          'title' => $this->t('Actualizado hace'),
          'show' => TRUE,
          'type' => 'link',
        ],
      ],
    ];

    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {
      return $this->contentFields;
    }
  }

  /**
   * Build configuration form.
   *
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {
    $this->addFieldsGeneralTable($form);

    // Initialization variables.
    $config_general = $this->configuration['general'];
    // Config general.
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion general'),
      '#open' => FALSE,
    ];

    foreach ($config_general as $key => $field) {
      if ($key == 'dateFormat_update') {
        $form['general'][$key] = [
          '#type' => 'select',
          '#options' => $this->getDateTypes(),
          '#title' => $this->contentFields['general'][$key]['title'],
          '#default_value' => isset($field['label']) ? $field['label'] : $field['properties']['dateFormat_update']['label'],
        ];
      }
      if ($key == 'label_details') {
        $form['general'][$key]['label'] = [
          '#type' => 'textfield',
          '#title' => $this->contentFields['general'][$key]['title'],
          '#default_value' => isset($field['label']) ? $field['label'] : $field['properties']['label_details']['label'],
        ];
        $form['general'][$key]['show'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Mostrar Label Detalles'),
          '#default_value' => ($field['show']) ? $field['show'] : '',
        ];
      }

    }
    $this->headerListFields($form);
    $this->addFieldsButtonsTable($form);
    return $form;
  }


  /**
   * Submit handler.
   *
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['messages'] = $form_state->getValue('messages');
    $this->configuration['buttons'] = array_merge($this->configuration['buttons'], $form_state->getValue(['buttons', 'properties']));
    $this->configuration['general']['subtitle'] = $form_state->getValue('subtitle');
    $this->configuration['headerList'] = array_merge($this->configuration['headerList'], $form_state->getValue('headerList'));
    $this->configuration['general']['label_details'] = $form_state->getValue(['general', 'label_details']);

    $dateFormatupdate = $form_state->getValue(['general', 'dateFormat_update']);
    $this->configuration['general']['dateFormat_update']['label'] = $dateFormatupdate;
    if (!empty($dateFormatupdate)) {
      $dateType = DateFormat::load($dateFormatupdate);
      if (is_object($dateType)) {
        $this->configuration['general']['dateFormat_update']['pattern'] = $dateType->getPattern();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

}
