<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Block\v2_0;

use Drupal\oneapp_mobile_upselling\Plugin\Block\v2_0\RechargeOrderDetailsBlock;
use Drupal\Core\Form\FormStateInterface;

/**
 * RechargeOrderDetailsGtBlock.
 */
class RechargeOrderDetailsGtBlock extends RechargeOrderDetailsBlock {

  /**
   * Property to store fields.
   *
   * @var mixed
   */
  protected $contentFields;

  /**
   * array of actions id blocked  for given roles
   */
  protected $actionsRoles;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->actionsRoles = [
      'creditCard',
      'tigoMoney',
    ];
    $this->contentFields = [
      'fields' => [
        'title' => [
          'field' => $this->t('Label Detalles'),
          'title' => $this->t('Detalles'),
          'show' => TRUE,
        ],
        'msisdn' => [
          'field' => $this->t('MSISDN'),
          'title' => $this->t('Número de línea:'),
          'show' => TRUE,
        ],
        'amount' => [
          'field' => $this->t('Monto'),
          'title' => $this->t('Valor:'),
          'show' => TRUE,
        ],
        'titlePaymentMethods' => [
          'field' => $this->t('Label Título Métodos de Pago'),
          'title' => $this->t('Escoge tu forma de pago:'),
          'show' => TRUE,
        ],
        'type' => [
          'field' => $this->t('Acción'),
          'title' => $this->t('Tipo de producto:'),
          'label' => $this->t('Recarga'),
          'show' => TRUE,
        ],
        'promotionalTextCreditCard' => [
          'field' => $this->t('Label Texto Promocional'),
          'title' => $this->t('creditRechargePromotion'),
          'label' => 'Paga con tarjeta y recibe Cuádruple Saldo',
          'show' => TRUE,
        ],
      ],
      'buttons' => [
        'changeMsisdn' => [
          'title' => $this->t('Label Cambiar Línea'),
          'label' => $this->t('Cambiar Línea'),
          'url' => '/',
          'type' => 'button',
          'show' => TRUE,
        ],
        'creditCard' => [
          'title' => $this->t('creditCard'),
          'label' => $this->t('Tarjeta de Crédito/Débito'),
          'description' => $this->t('Recibe de REGALO el % más de cŕedito'),
          'url' => '/',
          'type' => 'button',
          'show' => TRUE,
        ],
        'tigoMoney' => [
          'title' => $this->t('tigoMoney'),
          'label' => $this->t('TigoMoney'),
          'url' => '/',
          'type' => 'link',
          'show' => 1,
        ],
      ],
      'actions_roles' => [
        'creditCard',
        'tigoMoney',
      ], 
      'messages' => [
        'recharge_error' => [
          'title' => $this->t('Recarga Inválida'),
          'label' => $this->t('El número que ingresaste no aplica para realizar la recarga. Por favor inténtelo de nuevo..'),
          'show' => TRUE,
        ],
        'recharge_suscess' => [
          'title' => $this->t('Recarga Exitosa'),
          'label' => $this->t('Ahora tu nuevo número para recarga es el'),
          'show' => TRUE,
        ],
        'number_error' => [
          'title' => $this->t('Numero Inválido'),
          'label' => $this->t('El número que ingresaste no es un número Tigo. Por favor inténtelo de nuevo.'),
          'show' => TRUE,
        ],
        'monto_error' => [
          'title' => $this->t('Monto Inválido'),
          'label' => $this->t('El monto a recargar debe ser mayor o igual a @amount'),
          'show' => TRUE,
        ],
        'monto_max_error' => [
          'title' => $this->t('Monto Maximo Inválido'),
          'label' => $this->t('El monto a recargar no debe ser mayor de @amount'),
          'show' => TRUE,
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
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {
    $this->addFieldsTable($form);
    $this->addFieldsButtonsTable($form);
    $this->configMessageFields($form);
    // $this->configOthers($form);

    return $form;
  }

  /**
   * Msisdn configurations.
   *
   * @param array $form
   *   Form to add configuration.
   */
  public function addFieldsTable(array &$form) {
    $fields = $this->configuration['fields'];

    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Contenido'),
      '#open' => FALSE,
    ];

    $form['fields']['properties'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('Titulo'),
        $this->t('label'),
        $this->t('Show'),
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($fields as $id => $entity) {
      $form['fields']['properties'][$id]['field'] = [
        '#type' => 'hidden',
        '#default_value' => $entity['field'],
        '#suffix' => $entity['field'],
      ];

      $form['fields']['properties'][$id]['title'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['title'],
      ];

      if (isset($entity['label'])) {
        $form['fields']['properties'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
        ];
      }
      else {
        $form['fields']['properties'][$id]['label'] = [];
      }
      $form['fields']['properties'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function configMessageFields(&$form) {
    $messages = $this->configuration['messages'];

    $form['messages'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => FALSE,
    ];

    $form['messages']['properties'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Show'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($messages as $id => $entity) {
      $form['messages']['properties'][$id]['title'] = [
        '#type' => 'hidden',
        '#default_value' => $entity['title'],
        '#suffix' => $entity['title'],
      ];

      if ($id == 'monto_error' || $id == 'monto_max_error') {
        $form['messages']['properties'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
          '#description' => $this->t('Debe escribir en el mensaje @amount para obtener el monto.'),
        ];
      }
      else {
        $form['messages']['properties'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
        ];
      }
      $form['messages']['properties'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];
    }
  }

  /**
   * Buttons configurations.
   *
   * @param array $form
   *   Form to add configuration.
   */
  public function addFieldsButtonsTable(array &$form) {
    $buttons = $this->configuration['buttons'];

    $form['buttons'] = [
      '#type' => 'details',
      '#title' => $this->t('Botones y métodos de pago'),
      '#open' => FALSE,
    ];

    $form['buttons']['properties'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('description'),
        $this->t('Url'),
        $this->t('Type'),
        $this->t('Show'),
        '',
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($buttons as $id => $entity) {
      if ($id == 'creditCard' || $id == 'tigoMoney') {
        $form['buttons']['properties'][$id]['title'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['title'],
          '#suffix' => "Métodos de pago",
        ];
      }
      else {
        $form['buttons']['properties'][$id]['title'] = [
          '#type' => 'hidden',
          '#default_value' => $entity['title'],
          '#suffix' => $entity['title'],
        ];
      }

      $form['buttons']['properties'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      ];

      $form['buttons']['properties'][$id]['description'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['description'],
        '#size' => 20,
      ];

      if (isset($entity['url'])) {
        $form['buttons']['properties'][$id]['url'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['url'],
        ];
      }
      else {
        $form['buttons']['properties'][$id]['url'] = [];
      }
      if (isset($entity['type'])) {
        $form['buttons']['properties'][$id]['type'] = [
          '#type' => 'select',
          '#default_value' => $entity['type'],
          '#options' => ['button' => 'Button', 'link' => 'Link'],
        ];
      }
      else {
        $form['buttons']['properties'][$id]['type'] = [];
      }
      $form['buttons']['properties'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];
    }
    $actions_roles = $this->actionsRoles;
    $config_actions_roles = $this->configuration['actions_roles'] ?? [];
    $access_roles = $this->getAvailableAccessRoles();
    if (!empty($access_roles)) {
      $form['actions_roles'] = [
        '#type' => 'details',
        '#title' => $this->t('Roles con restriccion de acciones'),
        '#open' => FALSE,
      ];
      foreach ($actions_roles as $action_role) {
        $form['actions_roles'][$action_role] = [
          '#type' => 'details',
          '#title' => $this->t('Roles restringidos para ' . $this->configuration['buttons'][$action_role]['title']),
          '#open' => FALSE,
        ];
        foreach ($access_roles as $id_access_role => $access_role) {
          $form['actions_roles'][$action_role][$id_access_role]['blocked'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Accion bloqueada para ' . $access_role['label']),
            '#default_value' =>  $config_actions_roles[$action_role][$id_access_role]['blocked'] ?? FALSE,
          ];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    parent::adfBlockSubmit($form, $form_state);
    $this->configuration['fields'] = array_merge($this->configuration['fields'], $form_state->getValue(['fields', 'properties']));
    $this->configuration['messages'] = array_merge($this->configuration['messages'], $form_state->getValue(['messages', 'properties']));
    $this->configuration['buttons'] = array_merge($this->configuration['buttons'], $form_state->getValue(['buttons', 'properties']));
    $this->configuration['actions_roles'] = $form_state->getValue('actions_roles');
  }

}
