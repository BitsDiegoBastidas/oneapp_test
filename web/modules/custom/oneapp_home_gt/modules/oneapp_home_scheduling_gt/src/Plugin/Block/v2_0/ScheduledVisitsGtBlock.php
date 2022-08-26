<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oneapp_home_scheduling\Plugin\Block\v2_0\ScheduledVisitsBlock;

/**
 * Provides a 'Scheduled Visits' block.
 *
 * @Block(
 *  id = "oneapp_home_scheduling_gt_v2_0_scheduled_visits_block",
 *  admin_label = @Translation("OneApp Home Scheduled Visits Gt V2.0"),
 *  group = "oneapp_home_scheduling"
 * )
 */
class ScheduledVisitsGtBlock extends ScheduledVisitsBlock {

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
   * Others.
   *
   * @var mixed
   */
  protected $messages;

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
      'appointmentId' => [
        'title' => $this->t("ID de agendamiento"),
        'show' => 0,
        'label' => "",
        'format' => '',
        'weight' => 1,
      ],
      'subAppointmentId' => [
        'title' => $this->t("SubId de agendamiento"),
        'show' => 0,
        'label' => "",
        'format' => '',
        'weight' => 1,
      ],
      'scheduleDate' => [
        'title' => $this->t("Fecha agendamiento"),
        'show' => 1,
        'label' => "Fecha",
        'format' => '',
        'weight' => 2,
      ],
      'scheduleJourney' => [
        'title' => $this->t("Hora agendamiento"),
        'show' => 0,
        'label' => "Hora",
        'format' => '',
        'weight' => 3,
      ],
      'appointmentStatus' => [
        'title' => $this->t("Estado agendamiento"),
        'show' => 1,
        'label' => "Estado",
        'format' => '',
        'weight' => 4,
      ],
      'appointmentType' => [
        'title' => $this->t("Tipo de visita"),
        'show' => 1,
        'label' => "Tipo de visita",
        'value' => '',
        'formattedValue' => '',
        'weight' => 5,
      ],
    ];
    $this->actions = [
      'visitDetails' => [
        'label' => 'Ir',
        'url' => [
          'oneapp' => '',
          'selfcare' => '',
        ],
        'type' => 'button',
        'class' => '',
        'show' => TRUE,
      ],
      'confirmVisit' => [
        'label' => 'Confirmar',
        'url' => [
          'oneapp' => '',
          'selfcare' => '',
        ],
        'type' => 'button',
        'class' => '',
        'show' => TRUE,
      ],
      'downloadPdf' => [
        'label' => 'Descargar pdf',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'class' => '',
        'show' => TRUE,
      ],
      'scheduleVisit' => [
        'label' => 'Agendar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'class' => '',
        'show' => TRUE,
      ],
    ];
    $this->messages = [
      'descriptionCard' => [
        'title' => $this->t("Descripcion"),
        'value' => $this->t("Texto de descripcion"),
        'show' => TRUE,
      ],
      'visitMessage' => [
        'title' => $this->t("Etiqueta de visita"),
        'value' => 'Visita por agendar # {contractID}',
        'show' => TRUE,
      ],
    ];
    $this->others = [];
    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {
      return [
        'fields' => $this->fields,
        'actions' => $this->actions,
        'others' => $this->others,
        'messages' => $this->messages
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {
    $scheduling_service = \Drupal::service('oneapp_home_scheduling.v2_0.scheduling_service');
    $states = $scheduling_service->getStatesVisits();
    // Data.
    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Data'),
      '#open' => TRUE,
    ];
    $form['fields']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Campo'), $this->t('Etiqueta'), $this->t('Formato'), $this->t('Mostrar'), $this->t('Weight'),
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'fields-order-weight-fields',
        ],
      ],
    ];

    $diff_fields = array_diff_key($this->fields, $this->configuration['fields']);
    $fields = array_replace($this->configuration['fields'], $diff_fields);
    uasort($fields, [
      'Drupal\Component\Utility\SortArray',
      'sortByWeightElement',
    ]);
    $utils = \Drupal::service('oneapp.utils');
    $options_format = [
      '' => 'Ninguno',
      'Formato Moneda' => [
        'globalCurrency' => 'Moneda',
        'localCurrency' => 'Moneda Local',
      ],
    ];
    $options_format += ['Formato Fecha' => $utils->getDateFormats()];
    foreach ($fields as $id => $entity) {
      // Some table columns containing raw markup.
      $form['fields']['fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['fields']['fields'][$id]['label_default'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['fields']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 30,
      ];

      $form['fields']['fields'][$id]['format'] = [
        '#type' => 'select',
        '#options' => $options_format,
        '#default_value' => $entity['format'],
        '#attributes' => ['style' => 'width:125px'],
      ];

      $form['fields']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['fields']['fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        '#attributes' => ['class' => ['fields-order-weight-fields']],
      ];

      $form['fields']['fields'][$id]['title'] = [
        '#type' => 'hidden',
        '#value' => $entity['title'],
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
      '#header' => [t('Etiqueta'), t('Tipo'), t('URL'), t('Mostrar'), t('Mostrar Condicional')],
      '#empty' => t('There are no items yet. Add an item.'),
    ];

    $diff_actions = array_diff_key($this->actions, $this->configuration['actions']);
    $actions = array_replace($this->configuration['actions'], $diff_actions);

    foreach ($actions as $id => $action) {
      if (isset($action['label'])) {
        $form['actions']['actions'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $action['label'],
          '#size' => 40,
        ];
      }
      else {
        $form['actions']['actions'][$id]['label'] = [
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
      }
      else {
        $form['actions']['actions'][$id]['type'] = [
          '#plain_text' => t('Dato no configurable'),
        ];
      }
      // oneapp
      if (isset($action['url']['oneapp'])) {
        $form['actions']['actions'][$id]['url']['oneapp'] = [
          '#type' => 'textfield',
          '#title' => $this->t('OneApp'),
          '#default_value' => $action['url']['oneapp'],
          '#size' => 40,
        ];
      }
      else {
        $form['actions']['actions'][$id]['url']['oneapp'] = [
          '#plain_text' => $this->t('Dato no configurable'),
        ];
      }
      // selfcare
      if (isset($action['url']['selfcare'])) {
        $form['actions']['actions'][$id]['url']['selfcare'] = [
          '#type' => 'textfield',
          '#title' => $this->t('SelfCare'),
          '#default_value' => $action['url']['selfcare'],
          '#size' => 40,
        ];
      }
      else {
        $form['actions']['actions'][$id]['url']['selfcare'] = [
          '#plain_text' => $this->t('Dato no configurable'),
        ];
      }

      if (isset($action['show'])) {
        $form['actions']['actions'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $action['show'],
        ];
      }
      else {
        $form['actions']['actions'][$id]['show'] = [
          '#plain_text' => t('Dato no configurable'),
        ];
      }
      if (!empty($states)) {
        foreach ($states as $status) {
          $value = $status['value'];
          $form['actions']['actions'][$id]['showConditional'][$value] = [
            '#type' => 'checkbox',
            '#title' => $status['label'],
            '#default_value' => isset($action['showConditional'][$value]) ? $action['showConditional'][$value] : FALSE,
          ];
        }
      }
    }

    // End actions.
    // Others.
    $others = $this->configuration['others'];
    $form['others'] = [
      '#type' => 'details',
      '#title' => $this->t('Otras Configuraciones'),
      '#description' => $this->t('Otras configuraciones del módulo'),
      '#open' => FALSE,
    ];
    $form['others']['hide'] = [
      '#type' => 'details',
      '#title' => $this->t('Hide State'),
      '#open' => FALSE,
    ];
    $form['others']['hide']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ocultar siempre el card'),
      '#default_value' => isset($others['hide']['show']) ? $others['hide']['show'] : FALSE,
    ];
    $form['others']['hide']['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje cuando el el card esta oculto'),
      '#default_value' => isset($others['hide']['message']) ? $others['hide']['message'] : FALSE,
    ];
    // End others.

    // Actions.
    $form['messages'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => TRUE,
    ];
    $form['messages']['messages'] = [
      '#type' => 'table',
      '#header' => [t('Etiqueta'), t('Mensaje'), t('Mostrar'), t('Mostrar Condicional')],
      '#empty' => t('There are no items yet. Add an item.'),
    ];

    $diff_messages = array_diff_key($this->messages, $this->configuration['messages']);
    $messages = array_replace($this->configuration['messages'], $diff_messages);

    foreach ($messages as $id => $action) {
      $form['messages']['messages'][$id]['title'] = [
       // '#plain_text' => $action['title'],
        '#type' => 'hidden',
        '#default_value' => $action['title'],
        '#suffix' => $action['title'],
      ];

      $form['messages']['messages'][$id]['value'] = [
        '#type' => 'textfield',
        '#default_value' => $action['value'],
        '#size' => 30,
      ];


      if (isset($action['show'])) {
        $form['messages']['messages'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $action['show'],
        ];
      }
      if (!empty($states)) {
        foreach ($states as $status) {
          $value = $status['value'];
          $form['messages']['messages'][$id]['showConditional'][$value] = [
            '#type' => 'checkbox',
            '#title' => $status['label'],
            '#default_value' => isset($action['showConditional'][$value]) ? $action['showConditional'][$value] : FALSE,
          ];
        }
      }
    }


    $form_state->setCached(FALSE);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['fields'] = $form_state->getValue(['fields', 'fields']);
    $this->configuration['actions'] = $form_state->getValue(['actions', 'actions']);
    $this->configuration['others'] = $form_state->getValue('others');
    $this->configuration['messages'] = $form_state->getValue(['messages','messages']);
  }

}
