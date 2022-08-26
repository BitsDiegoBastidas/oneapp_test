<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oneapp_home_scheduling\Plugin\Block\v2_0\TechnicianLocationBlock;

/**
 * Provides a 'TechnicianLocationBlock' block.
 *
 * @Block(
 *  id = "oneapp_home_scheduling_gt_v2_0_technician_location_block",
 *  admin_label = @Translation("OneApp Home Technician Location Gt V2.0"),
 *  group = "oneapp_home_scheduling"
 * )
 */
class TechnicianLocationGtBlock extends TechnicianLocationBlock {
  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $fields;

  /**
   * Actions.
   *
   * @var mixed
   */
  protected $actions;

  /**
   * Others.
   *
   * @var mixed
   */
  protected $others;



  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->fields = [
      'technicianName' => [
        'title' => $this->t("Nombre del tecnico"),
        'show' => TRUE,
        'label' => "Nombre del tecnico",
      ],
    ];
    $this->actions = [
      'technicianCall' => [
        'label' => 'Llamar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'class' => '',
        'show' => TRUE,
      ],
    ];
    $this->others = [
        'successMessage' => [
          'title' => $this->t("Mensaje de exito"),
          'show' => TRUE,
          'label' => "Por favor llame a este numero {id} para ser conectado con el tecnico",
        ],
        'failedMessage' => [
          'title' => $this->t("Mensaje de error"),
          'show' => TRUE,
          'label' => "No se ha podido obtener informacion relacionada al tecnico",
        ],
     ];
    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    } else {
      return [
        'fields' => $this->fields,
        'actions' => $this->actions,
        'others' => $this->others,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {

    // Data.
    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Datos del Tecnico'),
      '#open' => TRUE,
    ];

    $diffFields = array_diff_key($this->fields, $this->configuration['fields']);
    $fields = array_replace($this->configuration['fields'], $diffFields);
    foreach ($fields as $id => $entity) {
      $form['fields'][$id] = [
        '#type' => 'details',
        '#title' =>$this->fields[$id]['title'],
        '#open' => TRUE,
      ];
      $form['fields'][$id]['show'] = [
        '#title' => t('Mostrar'),
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['fields'][$id]['label'] = [
        '#title' => t('Etiqueta'),
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 30,
      ];
    }

    // Actions.
    $form['actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Actions'),
      '#open' => TRUE,
    ];

    $form['actions']['actions'] = [
      '#type' => 'table',
      '#header' => [
        t('Etiqueta'), t('Mostrar'), t('Tipo'), t('URL'),
      ],
      '#empty' => t('There are no items yet. Add an item.'),
    ];

    $diffActions = array_diff_key($this->actions, $this->configuration['actions']);
    $actions = array_replace($this->configuration['actions'], $diffActions);
    foreach ($actions as $id => $action) {
      if (isset($action['label'])) {
        $form['actions']['actions'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $action['label'],
          '#size' => 10,
        ];
      } else {
        $form['actions']['actions'][$id]['label'] = [
          '#plain_text' => t('Dato no configurable'),
        ];
      }

      if (isset($action['show'])) {
        $form['actions']['actions'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $action['show'],
        ];
      } else {
        $form['actions']['actions'][$id]['show'] = [
          '#plain_text' => t('Dato no configurable'),
        ];
      }


      if (isset($action['type'])) {
        $form['actions']['actions'][$id]['type'] = [
          '#type' => 'select',
          '#options' => [
            'button' => $this->t('Boton'),
            'link' => $this->t('Link'),
          ],
          '#default_value' => $action['type'],
        ];
      } else {
        $form['actions']['actions'][$id]['type'] = [
          '#plain_text' => t('Dato no configurable'),
        ];
      }

      if (isset($action['url']['oneapp'])) {
        $form['actions']['actions'][$id]['url']['oneapp'] = [
          '#type' => 'textfield',
          '#title' => $this->t('OneApp'),
          '#default_value' => $action['url']['oneapp'],
          '#maxlength' => 512,
        ];
      } else {
        $form['actions']['actions'][$id]['url']['oneapp'] = [
          '#plain_text' => $this->t('Dato no configurable'),
        ];
      }

      if (isset($action['url']['selfcare'])) {
        $form['actions']['actions'][$id]['url']['selfcare'] = [
          '#type' => 'textfield',
          '#title' => $this->t('SelfCare'),
          '#default_value' => $action['url']['selfcare'],
          '#maxlength' => 512,
        ];
      } else {
        $form['actions']['actions'][$id]['url']['selfcare'] = [
          '#plain_text' => $this->t('Dato no configurable'),
        ];
      }
    }
    // End actions.
    // Others.
    $form['others'] = [
      '#type' => 'details',
      '#title' => $this->t('Otras configuraciones'),
      '#open' => TRUE,
    ];
    $diffOthers = array_diff_key($this->others, $this->configuration['others']);
    $others = array_replace($this->configuration['others'], $diffOthers);
    foreach ($others as $id => $entity) {
      $form['others'][$id] = [
        '#type' => 'details',
        '#title' =>$this->others[$id]['title'],
        '#open' => TRUE,
      ];
      $form['others'][$id]['show'] = [
        '#title' => t('Mostrar'),
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['others'][$id]['label'] = [
        '#title' => t('Etiqueta'),
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 100,
      ];
    }
    $form_state->setCached(FALSE);

     return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['fields'] = $form_state->getValue(['fields']);
    $this->configuration['actions'] = $form_state->getValue(['actions', 'actions']);
    $this->configuration['others'] = $form_state->getValue(['others']);
  }
}
