<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\Block\v2_0;

use Drupal\adf_block_config\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oneapp_home_scheduling\Plugin\Block\v2_0\VisitRescheduleBlock;

/**
 * Provides a 'Visit Reschedule' block.
 *
 * @Block(
 *  id = "oneapp_home_scheduling_gt_v2_0_visit_reschedule_block",
 *  admin_label = @Translation("OneApp Home Visit Reschedule Gt V2.0"),
 *  group = "oneapp_home_scheduling"
 * )
 */
class VisitRescheduleGtBlock extends VisitRescheduleBlock {

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
        'title' => $this->t("Formato de fecha y tiempo para enviar al servicio de reagendamiento"),
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
        'title' => $this->t("Formato de Fecha para enviar al formulario de reagendamiento"),
        'show' => 0,
        'format' => "d/M/Y",
      ],
      'confReschedule' => [
        'title' => $this->t("Formatos, mensajes y rango de d??as para enviar al formulario de reagendamiento"),
        'show' => 1,
        'formatReschedule' => "Y-m-d\TH:i:s",
        'formatDat' => "d/M/Y",
        'labelSuccess' => "Respuesta exitosa del reagendamiento de visitas",
        'labelFail' => "Respuesta en caso de fallar el reagendamiento de visitas",
        'days' => 0,
        'journaly' => 0,
        'labelForm' => "Seleccionar fecha",
        'labelFormJor' => "Seleccionar una jornada",
        'timeZone' => "-06:00",
        'format' => "g:i",

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
      'number' => 'Num??rico',
      'date' => 'Fecha',
      'password' => 'Contrase??a',
      'checkbox' => 'Opcion',
      'select' => 'Select',
      'tel' => 'tel',
      'email' => 'Correo electr??nico',
    ];
    $day = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];
    $journal = [1,2,3,4,5,6,7,8,9,10,11,12];
    $utils = \Drupal::service('oneapp.utils');
    $options_format = [
      '' => 'Ninguno',
      'Formato Moneda' => [
        'globalCurrency' => 'Moneda',
        'localCurrency' => 'Moneda Local',
      ],
    ];
    $options_format += ['Formato Fecha' => $utils->getDateFormats()];

    // Data.
    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Formulario de reagendamiento'),
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
      if (isset($entity['days'])) {
      $form['fields'][$id]['days'] = [
        '#title' => t('Fechas m??ximas'),
        '#type' => 'select',
        '#options' => $day,
        '#default_value' => isset($entity['days']) ? $entity['days'] : '',
      ];
      }
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

      if (isset($entity['labelForm'])) {
        $form['others'][$id]['labelForm'] = [
          '#title' => t('T??tulo del label fecha'),
          '#type' => 'textfield',
          '#default_value' => $entity['labelForm'],
          '#size' => 30,
        ];
      }
      if (isset($entity['labelFormJor'])) {
        $form['others'][$id]['labelFormJor'] = [
          '#title' => t('T??tulo del label del jornada'),
          '#type' => 'textfield',
          '#default_value' => $entity['labelFormJor'],
          '#size' => 30,
        ];
      }

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
      if (isset($entity['startDate'])) {
        $form['others'][$id]['startDate'] = [
          '#title' => t('Fecha Inicial'),
          '#type' => 'textfield',
          '#default_value' => $entity['startDate'],
          '#size' => 30,
        ];
      }
      if (isset($entity['endDate'])) {
        $form['others'][$id]['endDate'] = [
          '#title' => t('Fecha Final'),
          '#type' => 'textfield',
          '#default_value' => $entity['endDate'],
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
      if (isset($entity['formatDat'])) {
        $form['others'][$id]['formatDat'] = [
          '#title' => t('Formato de fecha para el formulario'),
          '#type' => 'textfield',
          '#default_value' => $entity['formatDat'],
          '#description' => date($entity['formatDat'], time()),
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
      if (isset($entity['labelSuccess'])) {
        $form['others'][$id]['labelSuccess'] = [
          '#title' => t('Mensaje Satisfactorio'),
          '#type' => 'textfield',
          '#default_value' => $entity['labelSuccess'],
          '#size' => 30,
        ];
      }
      if (isset($entity['labelFail'])) {
        $form['others'][$id]['labelFail'] = [
          '#title' => t('Mensaje de error'),
          '#type' => 'textfield',
          '#default_value' => $entity['labelFail'],
          '#size' => 30,
        ];
      }
      if (isset($entity['days'])) {
        $form['others'][$id]['days'] = [
          '#title' => t('Fechas m??ximas'),
          '#type' => 'select',
          '#options' => $day,
          '#default_value' => isset($entity['days']) ? $entity['days'] : '',
        ];
      }
      if (isset($entity['journaly'])) {
        $form['others'][$id]['journaly'] = [
          '#title' => t('L??mite de jornadas por fecha'),
          '#type' => 'select',
          '#options' => $journal,
          '#default_value' => isset($entity['journaly']) ? $entity['journaly'] : '',
        ];
      }
      if (isset($entity['formatReschedule'])) {
      $form['others'][$id]['formatReschedule'] = [
        '#title' => t('Formato de fecha para el api de reagendar'),
        '#type' => 'select',
        '#options' => $options_format,
        '#default_value' => $entity['formatReschedule'],
        '#attributes' => ['style' => 'width:125px'],
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
