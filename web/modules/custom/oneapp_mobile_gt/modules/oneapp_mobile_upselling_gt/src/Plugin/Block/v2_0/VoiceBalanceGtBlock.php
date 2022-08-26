<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\oneapp_mobile_upselling\Plugin\Block\v2_0\VoiceBalanceBlock;


class VoiceBalanceGtBlock extends VoiceBalanceBlock {

  /**
   * List fields voice balance default configuration.
   *
   * @var mixed
   */
  protected $contentFields;

  /**
   * Config default configuration.
   *
   * @var mixed
   */
  protected $contentFieldsConfig;

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
      'buttons' => [
        'showDetail' => [
          'title' => $this->t('VER DETALLE:'),
          'label' => $this->t('VER DETALLE'),
          'url' => '',
          'type' => 'link',
        ],
        'purchase' => [
          'title' => $this->t('Pagar:'),
          'label' => $this->t('Pagar'),
          'url' => '',
          'type' => 'button',
        ],
      ],
      'messages' => [
        'unlimitedBucket' => $this->t('Ilimitado ∞'),
        'empty' => $this->t('No se encontraron resultados.'),
        'error' => $this->t('En este momento no podemos obtener el historial de minutos, intenta de nuevo más tarde.'),
      ],
    ];

    $this->contentFields = [
      'bucketsId' => [
        'title' => $this->t('BucketId'),
        'label' => $this->t('BucketId'),
        'show' => 1,
        'weight' => 1,
      ],
      'friendlyName' => [
        'title' => $this->t('Nombre del paquete'),
        'label' => $this->t('Nombre del paquete:'),
        'show' => 1,
        'weight' => 2,
      ],
      'remainingValue' => [
        'title' => $this->t('Valor restante'),
        'label' => $this->t('Valor restante:'),
        'show' => 1,
        'description' => 'MIN',
        'weight' => 3,
      ],
      'reservedAmount' => [
        'title' => $this->t('Cantidad reservada'),
        'label' => $this->t('Cantidad reservada:'),
        'show' => 1,
        'description' => 'MIN',
        'weight' => 4,
      ],
      'reserveUsed' => [
        'title' => $this->t('Utilizados'),
        'label' => $this->t('Utilizados:'),
        'show' => 1,
        'weight' => 5,
      ],
      'endDateTime' => [
        'title' => $this->t('Fecha y hora de finalización'),
        'label' => $this->t('Fecha y hora de finalización:'),
        'prefix' => 'Vence en',
        'show' => 1,
        'weight' => 6,
      ],
    ];

    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {
      return [
        'voiceBalance' => $this->contentFields,
        'config' => $this->contentFieldsConfig,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {

    $this->configVoiceBalance($form);
    $this->configActionButtons($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function configActionButtons(array &$form) {
    $config = isset($this->configuration['config']) ? $this->configuration['config'] : $this->contentFieldsConfig;

    // Configs section.
    $form['config'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones de Botones, Formato de Fechas y Mensajes'),
      '#open' => TRUE,
    ];

    // Limit configs.
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
        $this->t('Type'),
        $this->t('Show'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($config['buttons'] as $key => $field) {
      $form['config']['buttons']['fields'][$key]['field'] = [
        '#plain_text' => $field['label'],
      ];

      $form['config']['buttons']['fields'][$key]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $field['label'],
        '#size' => 30,
      ];

      $form['config']['buttons']['fields'][$key]['url'] = [
        '#type' => 'url',
        '#size' => 30,
        '#default_value' => (isset($field['url'])) ? $field['url'] : '',
      ];

      $form['config']['buttons']['fields'][$key]['type'] = [
        '#type' => 'select',
        '#options' => [
          'link' => $this->t('Enlace'),
          'button' => $this->t('Boton'),
        ],
        '#default_value' => (isset($field['type'])) ? $field['type'] : NULL,
      ];

      $form['config']['buttons']['fields'][$key]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => (isset($field['show'])) ? $field['show'] : TRUE,
      ];
    }


    // Messagges config.
    $form['config']['messages'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => FALSE,
    ];

    $form['config']['messages']['unlimitedBucket'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para bonos ilimitados'),
      '#default_value' => $config['messages']['unlimitedBucket'],
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

    // Messagges config.
    $form['config']['postpaid'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones Postpago'),
      '#open' => FALSE,
    ];

    $form['config']['postpaid']['formattedValueExnet'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para friendlyName->formattedValue  ONNET'),
      '#default_value' => $config['postpaid']['formattedValueExnet'],
    ];

    $form['config']['postpaid']['formattedValueOnnet'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para friendlyName->formattedValue  EXNET'),
      '#default_value' => $config['postpaid']['formattedValueOnnet'],
    ];

    $form['config']['postpaid']['formattedValueRoamIn'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para friendlyName->formattedValue Roaming-In'),
      '#default_value' => $config['postpaid']['formattedValueRoamIn'] ?? '',
    ];

    $form['config']['postpaid']['formattedValueRoamOut'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para friendlyName->formattedValue Roaming-Out'),
      '#default_value' => $config['postpaid']['formattedValueRoamOut'] ?? '',
    ];

    $form['config']['postpaid']['limitOnnet'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor para limite Postpago ONNET'),
      '#default_value' => $config['postpaid']['limitOnnet'],
    ];

    $form['config']['postpaid']['limitExnet'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor para limite Postpago EXNET'),
      '#default_value' => $config['postpaid']['limitExnet'],
    ];

    $form['config']['postpaid']['limitRoamIn'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor para limite Roaming-In'),
      '#default_value' => $config['postpaid']['limitRoamIn'] ?? '',
    ];

    $form['config']['postpaid']['limitRoamOut'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor para limite Roaming-Out'),
      '#default_value' => $config['postpaid']['limitRoamOut'] ?? '',
    ];

    $form['config']['postpaid']['showBtnBuy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón de compra'),
      '#default_value' => $config['postpaid']['showBtnBuy'],
    ];

  }

  /**
   * {@inheritdoc}
   */
  private function configVoiceBalance(array &$form) {
    $fields = isset($this->configuration['voiceBalance']) ? $this->configuration['voiceBalance'] : $this->contentFields;

    $form['voiceBalance'] = [
      '#type' => 'details',
      '#title' => $this->t('Datos del balance de minutos'),
      '#open' => FALSE,
    ];
    $form['voiceBalance']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Prefijo'),
        $this->t('Descripción'),
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

    uasort($fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    foreach ($fields as $id => $entity) {
      $form['voiceBalance']['fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['voiceBalance']['fields'][$id]['field'] = [
        '#plain_text' => $entity['label'],
      ];

      // Label.
      $form['voiceBalance']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 25,
      ];

      // Prefix.
      if (isset($entity['prefix'])) {
        $form['voiceBalance']['fields'][$id]['prefix'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['prefix'],
          '#size' => 10,
        ];
      }
      else {
        $form['voiceBalance']['fields'][$id]['prefix'] = [];
      }

      // Description.
      if (isset($entity['description'])) {
        $form['voiceBalance']['fields'][$id]['description'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['description'],
          '#size' => 7,
        ];
      }
      else {
        $form['voiceBalance']['fields'][$id]['description'] = [];
      }

      // Show.
      $form['voiceBalance']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      // Weigth.
      $form['voiceBalance']['fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity['label']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        '#attributes' => [
          'class' => ['mytable-order-weight'],
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['voiceBalance'] = $form_state->getValue(['voiceBalance', 'fields']);
    $this->configuration['config']['buttons'] = $form_state->getValue([
      'config',
      'buttons',
      'fields',
    ]);
    $this->configuration['config']['messages'] = $form_state->getValue(['config', 'messages']);
    $this->configuration['config']['postpaid'] = $form_state->getValue(['config', 'postpaid']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

}
