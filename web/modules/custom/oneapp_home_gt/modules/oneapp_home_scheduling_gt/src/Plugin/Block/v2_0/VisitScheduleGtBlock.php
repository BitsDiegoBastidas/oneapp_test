<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oneapp_home_scheduling\Plugin\Block\v2_0\VisitScheduleBlock;

/**
 * Provides a 'Visit Schedule' block.
 *
 * @Block(
 *  id = "oneapp_home_scheduling_gt_v2_0_visit_schedule_block",
 *  admin_label = @Translation("OneApp Home Visit Schedule Gt V2.0"),
 *  group = "oneapp_home_scheduling"
 * )
 */
class VisitScheduleGtBlock extends VisitScheduleBlock {
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
  protected $technician;

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
      'scheduleDate' => [
        'title' => $this->t("Fecha agendamiento"),
        'show' => 1,
        'label' => "Seleccionar fecha",
        'type' => 'date',
        'placeholder' => '',
        'format' => 'dd/mm/yyyyy',
        'required' => 1,
      ],
      'scheduleJourney' => [
        'title' => $this->t("Hora agendamiento"),
        'show' => 0,
        'label' => "Seleccionar Jornada",
        'type' => 'select',
        'placeholder' => '',
        'format' => '',
        'required' => 1,
      ],
      'appointmentPhoneConfirm' => [
        'title' => $this->t("Telefono de confirmacion"),
        'show' => 0,
        'label' => "Telefono para confirmar visitas",
        'type' => 'text',
        'placeholder' => '',
        'format' => '',
        'required' => 1,
        'minLength' => 8,
        'maxLength' => 8,
      ],
      'appointmentEmail' => [
        'title' => $this->t("Correo electronico"),
        'show' => 0,
        'label' => "Correo electronico",
        'type' => 'email',
        'placeholder' => 'ejemplo@domain.com',
        'format' => '',
        'required' => 1,
        'minLength' => 0,
        'maxLength' => 0,
      ],
      'appointmentMessage' => [
        'title' => $this->t("Mensaje para el instalador"),
        'show' => 1,
        'label' => "Mensaje para el instalador",
        'type' => 'text',
        'placeholder' => '',
        'format' => '',
        'required' => 1,
        'minLength' => 0,
        'maxLength' => 0,
      ],
    ];
    $this->actions = [
      'rescheduleVisit' => [
        'label' => 'Confirmar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => TRUE,
      ],
      'confirmCalendarDate' => [
        'label' => 'Guardar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => TRUE,
      ],
      'cancelCalendarDate' => [
        'label' => 'Cancelar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => TRUE,
      ],
    ];
    $this->others = [
      'appointmentAddress' => [
        'title' => $this->t("Direccion de visita"),
        'show' => 1,
        'label' => "Direccion de visita",
      ],
      'appointmentId' => [
        'title' => $this->t("Id de Visita"),
        'show' => 0,
        'label' => "",
      ],
      'calendarLabel' => [
        'title' => $this->t("Etiqueta para Calendario de Seleccionar fecha"),
        'show' => 1,
        'label' => "Seleccionar fecha",
      ],
      'dateTimeForRescheduling' => [
        'title' => $this->t("Formato de fecha y tiempo para enviar al servicio de agendamiento"),
        'show' => 0,
        'format' => "Y/m/d\TH:i:s",
        'label' => "",
      ],
      'appointmentDateTime' => [
        'title' => $this->t("Formato y Zona horaria para procesar las fechas desde apigee"),
        'show' => 0,
        'timeZone' => "-05:00",
        'format' => "ga",
      ],
      'appointmentDate' => [
        'title' => $this->t("Formato de Fecha para enviar al formulario de agendamiento"),
        'show' => 0,
        'format' => "d/m/Y",
      ],
    ];
    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {
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
    $form_options = [
      'text' => 'Texto',
      'number' => 'Numérico',
      'date' => 'Fecha',
      'password' => 'Contraseña',
      'checkbox' => 'Opcion',
      'select' => 'Select',
      'tel' => 'tel',
      'email' => 'Correo electrónico',
    ];
    // Data.
    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Formulario de agendamiento'),
      '#open' => TRUE,
    ];

    $diff_fields = array_diff_key($this->fields, $this->configuration['fields']);
    $fields = array_replace($this->configuration['fields'], $diff_fields);
    foreach ($fields as $id => $entity) {
      // Some table columns containing raw markup.
      // Data.
      $form['fields'][$id] = [
        '#type' => 'details',
        '#title' => $this->fields[$id]['title'],
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
      $form['fields'][$id]['type'] = [
        '#title' => t('Seleccionar Tipo de campo'),
        '#type' => 'select',
        '#options' => $form_options,
        '#default_value' => isset($entity['type']) ? $entity['type'] : '',
      ];
      $form['fields'][$id]['placeholder'] = [
        '#title' => t('Placeholder'),
        '#type' => 'textfield',
        '#default_value' => isset($entity['placeholder']) ? $entity['placeholder'] : '',
      ];
      $form['fields'][$id]['format'] = [
        '#title' => t('Formato'),
        '#type' => 'textfield',
        '#default_value' => isset($entity['format']) ? $entity['format'] : '',
      ];
      $form['fields'][$id]['required'] = [
        '#title' => t('Es requerido'),
        '#type' => 'checkbox',
        '#default_value' => isset($entity['required']) ? $entity['required'] : '',
      ];
      if (isset($entity['minLength'])) {
        $form['fields'][$id]['minLength'] = [
          '#title' => t('Cantidad minima de caracteres para el campo'),
          '#type' => 'number',
          '#default_value' => isset($entity['minLength']) ? $entity['minLength'] : '',
        ];
      }
      if (isset($entity['maxLength'])) {
        $form['fields'][$id]['maxLength'] = [
          '#title' => t('Cantidad maxima de caracteres para el campo'),
          '#type' => 'number',
          '#default_value' => isset($entity['maxLength']) ? $entity['maxLength'] : '',
        ];
      }
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

    $diff_actions = array_diff_key($this->actions, $this->configuration['actions']);
    $actions = array_replace($this->configuration['actions'], $diff_actions);
    foreach ($actions as $id => $action) {
      if (isset($action['label'])) {
        $form['actions']['actions'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $action['label'],
          '#size' => 10,
        ];
      }
      else {
        $form['actions']['actions'][$id]['label'] = [
          '#plain_text' => t('Dato no configurable'),
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
          '#maxlength' => 512,
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
          '#maxlength' => 512,
        ];
      }
      else {
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
    $diff_others = array_diff_key($this->others, $this->configuration['others']);
    $others = array_replace($this->configuration['others'], $diff_others);
    foreach ($others as $id => $entity) {
      $form['others'][$id] = [
        '#type' => 'details',
        '#title' => $this->others[$id]['title'],
        '#open' => TRUE,
      ];
      $form['others'][$id]['show'] = [
        '#title' => t('Mostrar'),
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];
      if (isset($entity['label'])) {
        $form['others'][$id]['label'] = [
          '#title' => t('Etiqueta'),
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
          '#size' => 30,
        ];
      }
      if (isset($entity['timeZone'])) {
        $form['others'][$id]['timeZone'] = [
          '#title' => t('Zona Horaria'),
          '#type' => 'textfield',
          '#default_value' => $entity['timeZone'],
          '#size' => 30,
        ];
      }
      if (isset($entity['format'])) {
        $form['others'][$id]['format'] = [
          '#title' => t('Formato de Tiempo'),
          '#type' => 'textfield',
          '#default_value' => $entity['format'],
          '#description' => date($entity['format'], time()),
          '#size' => 30,
        ];
      }

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
