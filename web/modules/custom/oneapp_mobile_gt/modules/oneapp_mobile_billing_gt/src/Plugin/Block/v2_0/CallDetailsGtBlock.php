<?php

namespace Drupal\oneapp_mobile_billing_gt\Plugin\Block\v2_0;

use Drupal\oneapp_mobile_billing\Plugin\Block\v2_0\CallDetailsBlock;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CallDetailsGtBlock.
 */
class CallDetailsGtBlock extends CallDetailsBlock {

  /**
   * Config default configuration.
   *
   * @var mixed
   */
  protected $contentFieldsConfig;

  /**
   * List fields call details default configuration.
   *
   * @var mixed
   */
  protected $contentFieldsCallDetails;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->contentFieldsConfig = [
      'limit' => [
        'limit' => 6,
      ],
      'slack' => [
        'slack' => 10,
      ],
      'date' => [
        'format' => 'short',
      ],
      'dateInput' => [
        'format' => 'short',
        'formatValue' => 'short',
      ],
      'eventType' => [
        'settings' => [
          'label' => '',
          'show' => TRUE,
          'require' => TRUE,
        ],
        'options' => [
          'all' => [
            'variable' => '',
            'defaultVariable' => '',
            'label' => '',
            'defaultLabel' => $this->t('Todos'),
            'show' => TRUE,
          ],
          'incoming' => [
            'variable' => '',
            'defaultVariable' => 'E',
            'label' => '',
            'defaultLabel' => $this->t('Entrantes'),
            'show' => TRUE,
          ],
          'outgoing' => [
            'variable' => '',
            'defaultVariable' => 'S',
            'label' => '',
            'defaultLabel' => $this->t('Salientes'),
            'show' => TRUE,
          ],
        ],
      ],
      'formatServices' => [
        'startDate' => [
          'field' => $this->t('Start Date'),
          'defaultVariable' => 'start_date',
          'defaultLabel' => $this->t('Desde'),
        ],
        'endDate' => [
          'field' => $this->t('End Date'),
          'defaultVariable' => 'end_date',
          'defaultLabel' => $this->t('Hasta'),
        ],
      ],
      'buttons' => [
        'backButton' => [
          'label' => $this->t('Regresar'),
          'title' => $this->t('Regresar'),
          'url' => '',
          'show' => 1,
        ],
      ],
      'messages' => [
        'empty' => $this->t('No se encontraron resultados.'),
        'error' => $this->t('En este momento no podemos obtener el detalle de llamdas, por favor intentelo más tarde.'),
      ],
    ];

    $this->contentFieldsCallDetails = [
      'destination' => [
        'title' => $this->t('Destino:'),
        'label' => $this->t('Destino'),
        'show' => 1,
        'weight' => 1,
      ],
      'dateTimeStart' => [
        'title' => $this->t('Fecha:'),
        'label' => $this->t('Fecha'),
        'show' => 1,
        'weight' => 2,
      ],
      'duration' => [
        'title' => $this->t('Duración:'),
        'label' => $this->t('Duración'),
        'show' => 1,
        'weight' => 3,
      ],
    ];

    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    } else {
      return [
        'fieldsCallDetails' => $this->contentFieldsCallDetails,
        'config' => $this->contentFieldsConfig,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {
    $utils = \Drupal::service('oneapp.utils');
    $fields = isset($this->configuration['callDetails']) ? $this->configuration['callDetails'] : $this->contentFieldsCallDetails;
    $config = isset($this->configuration['config']) ? $this->configuration['config'] : $this->contentFieldsConfig;

    $form['callDetails'] = [
      '#type' => 'details',
      '#title' => $this->t('Detalles de llamadas'),
      '#open' => FALSE,
    ];
    $form['callDetails']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Show'),
        $this->t('Weight'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'mytable-order-weight',
        ],
      ],
      '#suffix' => description_responsive_weight(),
    ];

    foreach ($fields as $id => $entity) {
      $form['callDetails']['fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['callDetails']['fields'][$id]['field'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['callDetails']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      ];

      $form['callDetails']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['callDetails']['fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        '#attributes' => [
          'class' => ['mytable-order-weight'],
        ],
      ];

      $form['callDetails']['fields'][$id]['title'] = [
        '#type' => 'hidden',
        '#value' => $entity['title'],
      ];
    }

    // Others Config Section.
    $form['config'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones adicionales.'),
      '#open' => FALSE,
    ];

    $text_validation = $this->t('El valor ingresado es incorrecto, como mínimo 1.');
    $validation_text = [
      'class' => ['validate'],
      'oninvalid' => "this.setCustomValidity('" . $text_validation . "')",
      'oninput' => " this.setCustomValidity('')",
      'onchange' => "var sms = document.getElementById('edit-line-number').validity.valid ? '':'" .
        $text_validation . "'; this.setCustomValidity(sms);",
      'onkeypress' => "var sms = document.getElementById('edit-line-number').validity.valid ? '':'" .
        $text_validation . "'; this.setCustomValidity(sms);",
      'onkeyup' => "var sms = document.getElementById('edit-line-number').validity.valid ? '':'" .
        $text_validation . "'; this.setCustomValidity(sms);",
    ];

    // Limit configs.
    $form['config']['limit'] = [
      '#type' => 'details',
      '#title' => $this->t('Cantidad maxima de registros'),
      '#open' => FALSE,
    ];
    $form['config']['limit']['limit'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Limite de resultados a mostrar'),
      '#default_value' => $config['limit']['limit'],
      '#required' => TRUE,
      '#attributes' => $validation_text,
    ];

    // Days configs.
    $form['config']['slack'] = [
      '#type' => 'details',
      '#title' => $this->t('Cantidad de dias'),
      '#open' => FALSE,
    ];
    $form['config']['slack']['slack'] = [
      '#type' => 'number',
      '#min' => 1,
      '#title' => $this->t('Cantidad de dias'),
      '#default_value' => $config['slack']['slack'],
      '#required' => TRUE,
      '#attributes' => $validation_text,
    ];

    // Date format configs.
    $form['config']['date'] = [
      '#type' => 'details',
      '#title' => $this->t('Formato de fecha'),
      '#open' => FALSE,
    ];
    $form['config']['date']['format'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de fechas'),
      '#description' => $this->t('Seleccione el formato en que se mostraran las fechas por defecto'),
      '#default_value' => $config['date']['format'],
      '#options' => $utils->getDateFormats(),
    ];

    // Date format configs.
    $form['config']['dateInput'] = [
      '#type' => 'details',
      '#title' => $this->t('Formato de fecha para el servicio'),
      '#open' => FALSE,
    ];
    $form['config']['dateInput']['format'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de fechas (Input)'),
      '#description' => $this->t('Seleccione el formato de la fecha con el que se consume el servicio'),
      '#default_value' => $config["dateInput"]["format"],
      '#options' => $utils->getDateFormats(),
    ];
    $form['config']['dateInput']['formatValue'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de fechas (formatValue)'),
      '#description' => $this->t('Seleccione el formato de la fecha para el GUI en la fecha de inicio y final'),
      '#default_value' => $config["dateInput"]["formatValue"],
      '#options' => $utils->getDateFormats(),
    ];

    // Format Services config.
    $this->configFormatServices($form, $config);

    // Date format configs.
    $this->configEventType($form, $config);

    // Back button configs.
    $this->configBackButton($form, $config);

    // Message configs.
    $form['config']['messages'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => FALSE,
    ];
    $form['config']['messages']['empty'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje cuando no retorna datos'),
      '#default_value' => $config['messages']['empty'],
    ];
    $form['config']['messages']['error'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje de error'),
      '#default_value' => $config['messages']['error'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function configBackButton(&$form, $config) {
    $configs_buttons = isset($config['buttons']) ? $config['buttons'] : $this->contentFieldsConfig['buttons'];

    $form['config']['buttons'] = [
      '#type' => 'details',
      '#title' => $this->t('Botones'),
      '#open' => FALSE,
    ];

    $form['config']['buttons']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Url'),
        $this->t('Show'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($configs_buttons as $id => $entity) {
      $form['config']['buttons']['fields'][$id]['field'] = [
        '#plain_text' => $entity['label'],
      ];

      $form['config']['buttons']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      ];

      $form['config']['buttons']['fields'][$id]['url'] = [
        '#type' => 'url',
        '#size' => 30,
        '#default_value' => $entity['url'],
      ];

      $form['config']['buttons']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => (isset($entity['show'])) ? $entity['show'] : TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function configFormatServices(&$form, $config) {
    $configs_format_services = isset($config['dateInput']['formatServices']['fields']) ?
      $config['dateInput']['formatServices']['fields'] : $this->contentFieldsConfig['formatServices'];

    $form['config']['dateInput']['formatServices']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('Variable'),
        $this->t('label'),
        $this->t('Show'),
        $this->t('Require'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($configs_format_services as $id => $entity) {
      $form['config']['dateInput']['formatServices']['fields'][$id]['field'] = [
        '#plain_text' => $this->contentFieldsConfig['formatServices'][$id]['field'],
      ];

      $form['config']['dateInput']['formatServices']['fields'][$id]['variable'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['variable'] ?? $entity['defaultVariable'],
      ];

      $form['config']['dateInput']['formatServices']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'] ?? $entity['defaultLabel'],
      ];

      $form['config']['dateInput']['formatServices']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => (isset($entity['show'])) ? $entity['show'] : TRUE,
      ];

      $form['config']['dateInput']['formatServices']['fields'][$id]['require'] = [
        '#type' => 'checkbox',
        '#default_value' => (isset($entity['require'])) ? $entity['require'] : TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function configEventType(&$form, $config) {

    $configs_even_type = isset($config['eventType']) ? $config['eventType'] : $this->contentFieldsConfig['eventType'];

    $form['config']['eventType'] = [
      '#type' => 'details',
      '#title' => $this->t('Filtros para el Event Type'),
      '#open' => FALSE,
    ];

    $form['config']['eventType']['settings'] = [
      '#type' => 'table',
      '#caption' => $this->t('Configuraciones generales para el filtro de Event Type'),
      '#header' => [
        $this->t('Label'),
        $this->t('Show'),
        $this->t('Require'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    $form['config']['eventType']['options'] = [
      '#type' => 'table',
      '#caption' => $this->t('Configuraciones para las opciones del filtro de Event Type'),
      '#header' => [
        $this->t('Field'),
        $this->t('Variable'),
        $this->t('Label'),
        $this->t('Show'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    $setting = $configs_even_type['settings'];

    if (isset($configs_even_type['settings']['fields'])) {
      $setting = $configs_even_type['settings']['fields'];
    }

    foreach ($setting as $id => $value) {
      if ($id == 'label') {
        $form['config']['eventType']['settings']['fields'][$id] = [
          '#type' => 'textfield',
          '#default_value' => $value,
        ];
      } else {
        $form['config']['eventType']['settings']['fields'][$id] = [
          '#type' => 'checkbox',
          '#default_value' => (isset($value)) ? $value : TRUE,
        ];
      }
    }

    foreach ($configs_even_type['options'] as $id => $value) {
      $form['config']['eventType']['options'][$id]['field'] = [
        '#plain_text' => $this->t(ucfirst($id)),
      ];
      $form['config']['eventType']['options'][$id]['variable'] = [
        '#type' => 'textfield',
        '#default_value' => (!empty($value['variable'])) ? $value['variable'] : $value['defaultVariable'],
      ];
      $form['config']['eventType']['options'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => (!empty($value['label'])) ? $value['label'] : $value['defaultLabel'],
      ];
      $form['config']['eventType']['options'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => (isset($value['show'])) ? $value['show'] : TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['callDetails'] = $form_state->getValue(['callDetails', 'fields']);
    $this->configuration['config'] = $form_state->getValue('config');
    $this->configuration['config']['buttons'] = $form_state->getValue([
      'config',
      'buttons',
      'fields',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }
}
