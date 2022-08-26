<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\oneapp_mobile_upselling\Plugin\Block\v2_0\SmsBalanceBlock;


class SmsBalanceGtBlock extends SmsBalanceBlock
{
  /**
   * List fields sms default configuration.
   *
   * @var mixed
   */
  protected $contentFields;


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {

    $this->contentFields = [
      'actions' => [
        'fields' => [
          'showDetail' => [
            'title' => $this->t('VER DETALLE:'),
            'label' => $this->t('VER DETALLE'),
            'type' => 'link',
          ],
          'purchase' => [
            'title' => $this->t('Pagar:'),
            'label' => $this->t('Pagar'),
            'type' => 'button',
          ],
        ],
      ],
      'smsBalance' => [
        'fields' => [
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
          'friendlyNameRoam' => [
            'title' => $this->t('Nombre del paquete'),
            'label' => $this->t('Nombre del paquete:'),
            'show' => 1,
            'weight' => 2,
          ],
          'remainingValue' => [
            'title' => $this->t('Valor restante'),
            'label' => $this->t('Valor restante:'),
            'description' => 'SMS',
            'show' => 1,
            'weight' => 3,
          ],
          'reservedAmount' => [
            'title' => $this->t('Cantidad reservada'),
            'label' => $this->t('Cantidad reservada:'),
            'description' => 'SMS',
            'show' => 1,
            'weight' => 4,
          ],
          'reserveUsed' => [
            'title' => $this->t('Utilizados'),
            'label' => $this->t('Utilizados'),
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
        ],
      ],
      'messages' => [
        'unlimitedBucket' => $this->t('Ilimitado ∞'),
        'empty' => $this->t('No se encontraron resultados.'),
        'error' => $this->t('En este momento no podemos obtener el historial de sms, intenta de nuevo más tarde.'),
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
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state)
  {
    // Config buttons.
    $form['actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuración de botones y enlaces'),
      '#open' => FALSE,
    ];

    $form['actions']['fields'] = [
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

    $this->configActionButtons($form);

    $form['smsBalance'] = [
      '#type' => 'details',
      '#title' => $this->t('Datos del balance de mensajes'),
      '#open' => FALSE,
    ];
    $form['smsBalance']['fields'] = [
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

    $form['smsBalance'] = [
      '#type' => 'details',
      '#title' => $this->t('Datos del balance de mensajes'),
      '#open' => FALSE,
    ];
    $form['smsBalance']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Prefix'),
        $this->t('Description'),
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

    $this->configSmsBalance($form);

    $messagesConfig = isset($this->configuration['messages']) ? $this->configuration['messages'] : $this->contentFields['messages'];
    $postpaidConfig = isset($this->configuration['postpaid']) ? $this->configuration['postpaid'] : $this->contentFields['postpaid'];


    $form['messages'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => FALSE,
    ];
    $form['messages']['unlimitedBucket'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para bonos ilimitados'),
      '#default_value' => $messagesConfig['unlimitedBucket'],
    ];

    $form['messages']['empty'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje cuando no retorna datos'),
      '#default_value' => $messagesConfig['empty'],
    ];

    $form['messages']['error'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje de error'),
      '#default_value' => $messagesConfig['error'],
    ];

    $form['postpaid'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones Postpago'),
      '#open' => FALSE,
    ];

    $form['postpaid']['limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Configuración de limite'),
      '#default_value' => $postpaidConfig['limit'],
    ];

    $form['postpaid']['formattedValue'] = [
      '#type' => 'textfield',
      '#title' => $this->t('friendlyName->formattedValue'),
      '#default_value' => $postpaidConfig['formattedValue'],
    ];

    $form['postpaid']['formattedValueRoam'] = [
      '#type' => 'textfield',
      '#title' => $this->t('friendlyName->formattedValueRoam'),
      '#default_value' => $postpaidConfig['formattedValueRoam'] ?? $this->contentFields['smsBalance']['fields']['friendlyNameRoam']['title'],
    ];

    $form['postpaid']['showBtnBuy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón de compra'),
      '#default_value' =>  $postpaidConfig['showBtnBuy'],
    ];

    return $form;
  }

  /**
   * Add configuration buttons at form.
   *
   * @param array $form
   *   Config Form.
   */
  private function configActionButtons(array &$form)
  {
    $actionButtonConfig = isset($this->configuration['actions']) ? $this->configuration['actions'] : $this->contentFields['actions'];
    foreach ($actionButtonConfig['fields'] as $key => $field) {
      $form['actions']['fields'][$key]['field'] = [
        '#plain_text' => $field['label'],
      ];

      // Label.
      $form['actions']['fields'][$key]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $field['label'],
      ];

      $form['actions']['fields'][$key]['url'] = [
        '#type' => 'url',
        '#size' => 30,
        '#default_value' => (isset($field['url'])) ? $field['url'] : '',
      ];

      $form['actions']['fields'][$key]['type'] = [
        '#type' => 'select',
        '#options' => [
          'link' => $this->t('Enlace'),
          'button' => $this->t('Boton'),
        ],
        '#default_value' => (isset($field['type'])) ? $field['type'] : NULL,
      ];

      $form['actions']['fields'][$key]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => (isset($field['show'])) ? $field['show'] : TRUE,
      ];
    }
  }

  /**
   * Add configuration sms fields at form config.
   *
   * @param array $form
   *   Config Form.
   */
  private function configSmsBalance(array &$form)
  {
    $smsConfig = isset($this->configuration['smsBalance']) ? $this->configuration['smsBalance'] : $this->contentFields['smsBalance'];
    uasort($smsConfig, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    foreach ($smsConfig['fields'] as $id => $entity) {
      $form['smsBalance']['fields'][$id]['#attributes']['class'][] = 'draggable';
      $form['smsBalance']['fields'][$id]['field'] = [
        '#plain_text' => $entity['label'],
      ];

      // Label.
      $form['smsBalance']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      ];

      // Prefix.
      if (isset($entity['prefix'])) {
        $form['smsBalance']['fields'][$id]['prefix'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['prefix'],
          '#size' => 10,
        ];
      } else {
        $form['smsBalance']['fields'][$id]['prefix'] = [];
      }

      // Description.
      if (isset($entity['description'])) {
        $form['smsBalance']['fields'][$id]['description'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['description'],
          '#size' => 7,
        ];
      } else {
        $form['smsBalance']['fields'][$id]['description'] = [];
      }

      // Show.
      $form['smsBalance']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      // Weigth.
      $form['smsBalance']['fields'][$id]['weight'] = [
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
  public function adfBlockSubmit($form, FormStateInterface $form_state)
  {
    $this->configuration['smsBalance'] = $form_state->getValue('smsBalance');
    $this->configuration['actions'] = $form_state->getValue('actions');
    $this->configuration['messages'] = $form_state->getValue('messages');
    $this->configuration['postpaid'] = $form_state->getValue('postpaid');
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    return [];
  }
}
