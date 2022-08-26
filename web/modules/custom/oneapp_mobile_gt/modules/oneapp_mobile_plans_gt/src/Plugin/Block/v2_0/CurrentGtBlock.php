<?php

namespace Drupal\oneapp_mobile_plans_gt\Plugin\Block\v2_0;

use Drupal\oneapp_mobile_plans\Plugin\Block\v2_0\CurrentBlock;
use Drupal\Core\Form\FormStateInterface;

/**
 * CurrentGtBlock class.
 */
class CurrentGtBlock extends CurrentBlock {
  /**
   * Property to store fields.
   *
   * @var mixed
   */
  protected $contentFields;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->contentFieldsConfig = [
      'help' => [
        'title' => $this->t('Monto última factura:'),
        'label' => $this->t('¿Por qué puede ser diferente el valor?'),
        'url' => '',
        'show' => 1,
      ],
      'updatePlanButton' => [
        'show' => FALSE,
        'title' => $this->t('MEJORA MI PLAN'),
        'label' => 'MEJORA MI PLAN',
        'url' => '',
        'type' => 'button',
      ],
      'additionalButton' => [
        'show' => FALSE,
        'title' => $this->t('IR A PREMIUM'),
        'label' => 'IR A PREMIUM',
        'url' => '',
        'type' => 'button',
      ],
      'imagePath' => [
        'url' => '/',
      ],
      'date' => [
        'format' => 'short',
      ],
      'messages' => [
        'free' => $this->t('Gratis'),
        'unlimited' => $this->t('Ilimitado ∞'),
        'empty' => $this->t('No se encontraron resultados.'),
        'error' => $this->t('En este momento no podemos obtener información del plan actual, intenta de nuevo más tarde.'),
      ],
    ];

    $this->additionalRecurrentOfferingList = [
      'additionalOfferingId' => [
        'title' => 'ID del producto',
        'label' => '',
        'show' => FALSE,
      ],
      'additionalOfferingName' => [
        'title' => 'Nombre del producto',
        'label' => '',
        'show' => TRUE,
      ],
      'offeringLegacyName' => [
        'title' => 'Descripción del producto',
        'label' => '',
        'show' => FALSE,
      ],
      'priceAmount' => [
        'title' => 'Precio del producto',
        'label' => '',
        'show' => TRUE,
      ],
      'validity' => [
        'title' => 'Tiempo del producto',
        'label' => '',
        'show' => FALSE,
      ],
      'tags' => [
        'title' => 'Imágenes',
        'label' => '',
        'show' => FALSE,
      ],
    ];

    $this->contentFields = [
      'planName' => [
        'title' => 'Nombre del plan',
        'label' => '',
        'show' => 1,
        'weight' => 1,
      ],
      'productOfferingList' => [
        'title' => 'Lista de productos',
        'label' => 'Default',
        'show' => 1,
        'weight' => 2,
      ],
      'billingCycle' => [
        'title' => 'Fecha de acreditación',
        'label' => 'Fecha de acreditación de beneficios:',
        'description' => 'de cada mes',
        'show' => 1,
        'weight' => 3,
      ],
      'endDate' => [
        'title' => 'Fecha de terminación',
        'label' => 'Fecha de terminación de contrato:',
        'show' => 1,
        'weight' => 4,
      ],
      'monthlyAmount' => [
        'title' => 'Cargo básico',
        'label' => 'Cargo Básico',
        'description' => 'No incluye IVA ni otros cargos',
        'show' => 1,
        'weight' => 5,
      ],
      'additionalRecurrentOfferingList' => [
        'title' => 'Servicios adicionales',
        'label' => 'Servicios adicionales',
        'show' => 1,
        'weight' => 2,
      ],
    ];

    $this->productOfferingList = [
      'data' => [
        'title' => 'DATA',
        'label' => '',
        'show' => TRUE,
      ],
      'vozOnnet' => [
        'title' => 'VOZ ONNET',
        'label' => '',
        'show' => TRUE,
      ],
      'vozOnnetQuetzales' => [
        'title' => 'VOZ ONNET - QUETZALES',
        'label' => '',
      ],
      'vozExnet' => [
        'title' => 'VOZ EXNET',
        'label' => '',
        'show' => TRUE,
      ],
      'sms' => [
        'title' => 'SMS',
        'label' => '',
        'show' => TRUE,
      ],
    ];

    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {
      return [
        'fields' => $this->contentFields,
        'additional' => $this->additionalRecurrentOfferingList,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {
    $utils = \Drupal::service('oneapp.utils');
    $fields = isset($this->configuration['fields']) ? $this->configuration['fields'] : $this->contentFields;
    $config = isset($this->configuration['config']) ? $this->configuration['config'] : $this->contentFieldsConfig;

    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Contenido'),
      '#open' => TRUE,
    ];
    $form['fields']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Description'),
        $this->t('Show'),
        $this->t('Weight'),
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
      $fields[$id]['weight'] = $entity['weight'];
    }

    uasort($fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    foreach ($fields as $id => $entity) {
      $form['fields']['fields'][$id]['#attributes']['class'][] = 'draggable';

      $form['fields']['fields'][$id]['field'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['fields']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 40,
      ];

      if (isset($entity['description'])) {
        $form['fields']['fields'][$id]['description'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['description'],
          '#size' => 40,
        ];
      }
      else {
        $form['fields']['fields'][$id]['description'] = [];
      }

      $form['fields']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['fields']['fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity['title']]),
        '#title_display' => 'invisible',
        '#default_value' => $entity['weight'],
        '#attributes' => [
          'class' => ['mytable-order-weight'],
        ],
      ];

      $form['fields']['fields'][$id]['title'] = [
        '#type' => 'hidden',
        '#default_value' => $entity['title'],
      ];
    }

    // Additional services.
    $form['additional'] = [
      '#type' => 'details',
      '#title' => $this->t('Servicios adicionales'),
      '#open' => FALSE,
    ];
    $form['additional']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Show'),
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    $additionals = isset($this->configuration["additional"]) ? $this->configuration["additional"] : $this->additionalRecurrentOfferingList;

    foreach ($additionals as $id => $entity) {
      $form['additional']['fields'][$id]['#attributes']['class'][] = 'draggable';

      $form['additional']['fields'][$id]['field'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['additional']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 40,
      ];

      $form['additional']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['additional']['fields'][$id]['title'] = [
        '#type' => 'hidden',
        '#default_value' => $entity['title'],
      ];
    }

    // Additional services.
    $form['productOfferingList'] = [
      '#type' => 'details',
      '#title' => $this->t('Lista de Productos'),
      '#open' => FALSE,
    ];
    $form['productOfferingList']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Show'),
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    $productOfferingList = isset($this->configuration["productOfferingList"]) ? $this->configuration["productOfferingList"] : $this->productOfferingList;

    foreach ($productOfferingList as $id => $entity) {
      $form['productOfferingList']['fields'][$id]['#attributes']['class'][] = 'draggable';

      $form['productOfferingList']['fields'][$id]['field'] = [
        '#plain_text' => $entity['title'],
      ];

      $form['productOfferingList']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
        '#size' => 40,
      ];

      if ($id != 'vozOnnetQuetzales') {
        $form['productOfferingList']['fields'][$id]['show'] = [
          '#type' => 'checkbox',
          '#default_value' => $entity['show'],
        ];
      }

      $form['productOfferingList']['fields'][$id]['title'] = [
        '#type' => 'hidden',
        '#default_value' => $entity['title'],
      ];
    }

    $form['config'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuración'),
      '#open' => TRUE,
    ];

    $form['config']['help'] = [
      '#type' => 'details',
      '#title' => $this->t('Ayuda'),
      '#open' => FALSE,
    ];
    $form['config']['help']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar "Ayuda"'),
      '#default_value' => $config['help']['show'],
    ];
    $form['config']['help']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config['help']['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][help][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['help']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto del enlace'),
      '#default_value' => $config['help']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][help][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['help']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $config['help']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][help][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Additional button.
    $form['config']['additionalButton'] = [
      '#type' => 'details',
      '#title' => $this->t('boton de servicios adicionales'),
      '#open' => FALSE,
    ];
    $form['config']['additionalButton']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar "servicios adicionales"'),
      '#default_value' => $config['additionalButton']['show'],
    ];
    $form['config']['additionalButton']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config['additionalButton']['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][additionalButton][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['additionalButton']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto del enlace'),
      '#default_value' => $config['additionalButton']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][additionalButton][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['additionalButton']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $config['additionalButton']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][additionalButton][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // updatePlanButton.
    $form['config']['updatePlanButton'] = [
      '#type' => 'details',
      '#title' => $this->t('boton actualizar plan'),
      '#open' => FALSE,
    ];
    $form['config']['updatePlanButton']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar "actualizar plan"'),
      '#default_value' => $config['updatePlanButton']['show'],
    ];
    $form['config']['updatePlanButton']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config['updatePlanButton']['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][updatePlanButton][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['updatePlanButton']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Texto del enlace'),
      '#default_value' => $config['updatePlanButton']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][updatePlanButton][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['updatePlanButton']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $config['additionalButton']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][additionalButton][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $webviewsListEntity = \Drupal::entityTypeManager()->getStorage('webviews_entity')->loadMultiple();
    $webviewsList[0] = 'Ninguno';
    foreach ($webviewsListEntity as $key => $value) {
      $webviewsList[$key] = $value->webview_title;
    }
    $form['config']['updatePlanButton']['externalUrl'] = [
      '#type' => 'select',
      '#title' => $this->t('Webview Url'),
      '#options' => $webviewsList,
      '#default_value' => isset($config['updatePlanButton']['externalUrl']) ? $config['updatePlanButton']['externalUrl'] : '',
    ];

    // Image path.
    $form['config']['imagePath'] = [
      '#type' => 'details',
      '#title' => $this->t('Ruta de las imágenes'),
      '#open' => FALSE,
    ];
    $form['config']['imagePath']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $config['imagePath']['url'],
    ];

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

    $form['config']['messages'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => FALSE,
    ];
    // Message for free package.
    $form['config']['messages']['free'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Gratis'),
      '#default_value' => $config['messages']['free'],
    ];
    // Message for unlimited package.
    $form['config']['messages']['unlimited'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ilimitado'),
      '#default_value' => $config['messages']['unlimited'],
    ];
    $form['config']['messages']['empty'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Vacio'),
      '#default_value' => $config['messages']['empty'],
    ];
    $form['config']['messages']['error'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error'),
      '#default_value' => $config['messages']['error'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['fields'] = $form_state->getValue(['fields', 'fields']);
    $this->configuration['additional'] = $form_state->getValue(['additional', 'fields']);
    $this->configuration['config'] = $form_state->getValue(['config']);
    $this->configuration["productOfferingList"] = $form_state->getValue(['productOfferingList', 'fields']);
  }

}
