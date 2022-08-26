<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oneapp_home_scheduling\Plugin\Block\v2_0\VisitDetailsBlock;

/**
 * Provides a 'Visit Details' block.
 *
 * @Block(
 *  id = "oneapp_home_scheduling_gt_v2_0_visit_details_block",
 *  admin_label = @Translation("OneApp Home Visit Details Gt V2.0"),
 *  group = "oneapp_home_scheduling"
 * )
 */
class VisitDetailsGtBlock extends VisitDetailsBlock {
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
      'appointmentId' => [
        'title' => $this->t("ID de agendamiento"),
        'show' => 0,
        'label' => "ID de agendamiento",
      ],
      'subAppointmentId' => [
        'title' => $this->t("SubId de agendamiento"),
        'show' => 0,
        'label' => "SubId de agendamiento",
      ],
      'scheduleDate' => [
        'title' => $this->t("Fecha agendamiento"),
        'show' => 1,
        'label' => "Fecha",
        'format' => '',
      ],
      'scheduleJourney' => [
        'title' => $this->t("Hora agendamiento"),
        'show' => 1,
        'label' => "Hora",
        'format' => '',
      ],
      'appointmentType' => [
        'title' => $this->t("Tipo de visita"),
        'show' => 0,
        'label' => "Tipo de visita",
      ],
      'appointmentServices' => [
        'title' => $this->t("Servicios"),
        'show' => 0,
        'label' => "Servicios",
      ],
      'appointmentAddress' => [
        'title' => $this->t("Direccion de visita"),
        'show' => 0,
        'label' => "Direccion de visita",
      ],
      'appointmentStatus' => [
        'title' => $this->t("Estado agendamiento"),
        'show' => 1,
        'label' => "Estado agendamiento",
      ],
      "appointmentContractId" => [
        'title' => $this->t("Id de contrato de la cita"),
        "value" => "",
        "formattedValue"=> "",
        "show" => 0,
        'label' => "Requerir contacto del tecnico",
      ],
      "requestContact" => [
        'title' => $this->t("Requerir contacto del tecnico"),
        "value" => "Llama al {phone} si necesitas ponerte en contacto con el técnico",
        "formattedValue"=> "Llama al {phone} si necesitas ponerte en contacto con el técnico",
        "show" => 0,
        'label' => "equerir contacto del tecnico",
      ],
      "requestCall" => [
        'title' => $this->t("Llamada a agente"),
        "value" => "Necesitas ayuda?",
        "formattedValue"=> "Necesitas ayuda?",
        "show" => 0,
        'label' => "Llamada a agente",
      ],
    ];
    $this->actions = [
      'confirmVisit' => [
        'label' => 'Confirmar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => TRUE,
      ],
      'cancelVisit' => [
        'label' => 'Cancelar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => TRUE,
      ],
      'rescheduleVisit' => [
        'label' => 'Reagendar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => TRUE,
      ],
      'locateTechnician' => [
        'label' => 'Localizar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => TRUE,
      ],
      'callVisit' => [
        'label' => 'Llamar',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'button',
        'show' => FALSE,
      ],
      'requestContact' => [
        'label' => 'Solicitar Contacto',
        'url' => [
          'oneapp' => '',
          'selfcare' => ''
        ],
        'type' => 'link',
        'show' => FALSE,
      ],
    ];
    $this->technician = [
      'technicianName' => [
        'title' => $this->t("Nombre del tecnico"),
        'show' => 1,
        'label' => "Nombre del tecnico",
      ],
      'technicianDocumentId' => [
        'title' => $this->t("Numero de documento del Tecnico"),
        'show' => 1,
        'label' => "Numero de documento del Tecnico",
      ],
      'technicianContractorCompany' => [
        'title' => $this->t("Compañia contratista"),
        'show' => 1,
        'label' => "Compañia contratista",
      ],
      'technicianPicture' => [
        'title' => $this->t("Imagen"),
        'show' => 1,
        'label' => "Imagen",
      ],
      'technicianPhone' => [
        'title' => $this->t("Telefono"),
        'show' => 1,
        'label' => "Telefono",
      ],
    ];
    $this->others = [
      'appointmentSuspendTitle' => [
        'title' => $this->t("Titulo de Visita cancelada"),
        'show' => 0,
        'label' => "Tu visita ha sido cancelada",
      ],
      'appointmentSuspendDetail' => [
        'title' => $this->t("Descripcion de Visita cancelada"),
        'show' => 0,
        'label' => "Por motivos tecnicos ",
      ],
      'appointmentSuspendOrder' => [
        'title' => $this->t("Etiqueta de Orden de Visita cancelada"),
        'show' => 0,
        'label' => "Orden",
      ],
      'appointmentSuspendIconClass' => [
        'title' => $this->t("Clase para el Icono de Visita cancelada"),
        'show' => 0,
        'label' => "error",
      ],
      'appointmentSuspendStatus' => [
        'title' => $this->t("Estado de apigee para saber si es cancelada la visita"),
        'show' => 0,
        'label' => "Suspend",
      ],
      'unassignedTechnician' => [
        'title' => $this->t("Texto para determinar tecnico no asignado"),
        'show' => 0,
        'label' => "Aun no asignado",
      ],
      'successMessage' => [
        'title' => $this->t("Mensaje de exito"),
        'show' => TRUE,
        'label' => "Un agente se pondra en contacto con usted",
      ],
      'failedMessage' => [
        'title' => $this->t("Mensaje de error"),
        'show' => TRUE,
        'label' => "No se ha podido obtener informacion relacionada al tecnico",
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
        'technician' => $this->technician,
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
      '#open' => FALSE,
    ];
    $form['fields']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Campo'),
        $this->t('Etiqueta'),
        $this->t('Formato'),
        $this->t('Mostrar'),
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];


    $utils = \Drupal::service('oneapp.utils');
    $options_format = [];
    $options_format += ['Formato Fecha' => $utils->getDateFormats()];
    foreach ($this->fields as $id => $entity) {
      $form['fields']['fields'][$id]['label_default'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['fields']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $this->configuration["fields"][$id]["label"] ?? $entity['label'],
        '#size' => 30,
      ];

      if (isset($entity['format'])) {
        $form['fields']['fields'][$id]['format'] = [
          '#type' => 'select',
          '#options' => $options_format,
          '#default_value' => $this->configuration["fields"][$id]["format"] ?? $entity['format'],
          '#attributes' => ['style' => 'width:125px'],
        ];
      }
      else {
        $form['fields']['fields'][$id]['format'] = [
          '#type' => 'label',
          '#default_value' => '',
        ];
      }

      $form['fields']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $this->configuration["fields"][$id]["show"] ?? $entity['show'],
      ];
    }

    // Actions.
    $form['actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Actions'),
      '#open' => FALSE,
    ];

    $form['actions']['actions'] = [
      '#type' => 'table',
      '#header' => [
        t('Etiqueta'),
        t('Mostrar'),
        t('Tipo'),
        t('URL'),
        t('Estado'),
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
          '#size' => 30,
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

      if (isset($action['url']['oneapp'])) {
        $form['actions']['actions'][$id]['url']['oneapp'] = [
          '#type' => 'textfield',
          '#title' => t('OneApp'),
          '#default_value' => $action['url']['oneapp'],
          '#maxlength' => 512,
        ];
      }
      else {
        $form['actions']['actions'][$id]['url']['oneapp'] = [
          '#plain_text' => $this->t('Dato no configurable'),
        ];
      }

      if (isset($action['url']['selfcare'])) {
        $form['actions']['actions'][$id]['url']['selfcare'] = [
          '#type' => 'textfield',
          '#title' => t('SelfCare'),
          '#default_value' => $action['url']['selfcare'],
          '#maxlength' => 512,
        ];
      }
      else {
        $form['actions']['actions'][$id]['url']['selfcare'] = [
          '#plain_text' => $this->t('Dato no configurable'),
        ];
      }

      if (!empty($states)) {
        foreach ($states as $status) {
          $value = $status['value'];
          $form['actions']['actions'][$id]['showConditional'][$value] = [
            '#type' => 'checkbox',
            '#title' => $value,
            '#default_value' => isset($action['showConditional'][$value]) ? $action['showConditional'][$value] : FALSE,
          ];
        }
      }
    }
    // End actions.
    // technician.
    $form['technician'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion Tecnico'),
      '#open' => FALSE,
    ];
    $form['technician']['fields'] = [
      '#type' => 'table',
      '#header' => [$this->t('Campo'), $this->t('Etiqueta'), $this->t('Mostrar')],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($this->technician as $id => $entity) {
      $form['technician']['fields'][$id]['label_default'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['technician']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $this->configuration["technician"][$id]["label"] ?? $entity['label'],
        '#size' => 30,
      ];

      $form['technician']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $this->configuration["technician"][$id]["show"] ?? $entity['show'],
      ];
    }

    // End technician.
    //others
    $form['others'] = [
      '#type' => 'details',
      '#title' => $this->t('Otras configuraciones'),
      '#open' => FALSE,
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
      if ($id == 'appointmentSuspendDetail') {
        $form['others'][$id]['label'] = [
          '#title' => t('Descripcion'),
          '#type' => 'textarea',
          '#default_value' => $entity['label'],
          '#size' => 30,
        ];
      }
      else {
        $form['others'][$id]['label'] = [
          '#title' => t('Etiqueta'),
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
          '#size' => 30,
        ];
      }
    }
    // End others.
    $form_state->setCached(FALSE);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['fields'] = $form_state->getValue(['fields', 'fields']);
    $this->configuration['actions'] = $form_state->getValue(['actions', 'actions']);
    $this->configuration['technician'] = $form_state->getValue(['technician', 'fields']);
    $this->configuration['others'] = $form_state->getValue(['others']);
  }

}
