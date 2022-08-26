<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Block\v2_0;

use Drupal\oneapp_mobile_upselling\Plugin\Block\v2_0\PacketsOrderDetailsBlock;
use Drupal\Core\Form\FormStateInterface;

class PacketsOrderDetailsGtBlock extends PacketsOrderDetailsBlock {

  /**
   * List default configuration.
   *
   * @var mixed
   */
  protected $defaultConfig;

  /**
   * array of actions id blocked  for given roles
   */
  protected $actionsRoles;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->actionsRoles = [
      'coreBalance',
      'invoiceCharge',
      'creditCard',
      'Loan_Packets',
      'emergencyLoan',
      'freePacket',
      'tigoMoney',
    ];
    $this->defaultConfig = [
      'data' => [
        'fields' => [
          'title' => [
            'title' => $this->t('Título para Pantalla de Métodos de Pago'),
            'label' => $this->t('Detalles de compra'),
            'show' => 1,
            'weight' => 1,
          ],
          'msisdn' => [
            'title' => $this->t('msisdn para Pantalla de Métodos de Pago'),
            'label' => $this->t('Número de línea:'),
            'show' => 1,
            'weight' => 1,
          ],
          'description' => [
            'title' => $this->t('Descripción para Pantalla de Métodos de Pago'),
            'label' => $this->t('Detalle compra:'),
            'show' => 1,
            'weight' => 1,
          ],
          'price' => [
            'title' => $this->t('Valor para Pantalla de Métodos de Pago'),
            'label' => $this->t('Valor:'),
            'show' => 1,
            'weight' => 1,
          ],
          'period' => [
            'title' => $this->t('Vigencia'),
            'label' => $this->t('Vigencia:'),
            'show' => 1,
            'weight' => 1,
          ],
          'creditPackagePrice' => [
            'title' => $this->t('Valor a enviar para pago de paquetes "creditPackagePrice"'),
            'label' => $this->t('Valor:'),
            'show' => 1,
            'weight' => 1,
          ],
          'creditPackagePromotion' => [
            'title' => $this->t('Promoción para método de pago tarjeta de crédito "creditPackagePromotion"'),
            'label' => "",
            'show' => 1,
            'weight' => 1,
          ],
        ],
      ],
      'config' => [
        'actions' => [
          'changeMsisdn' => [
            'title' => $this->t('Label Cambiar Línea'),
            'label' => $this->t('Cambiar Línea'),
            'url' => '/',
            'type' => 'button',
            'show' => TRUE,
          ],
          'fulldescription' => [
            'title' => $this->t('Label Ver más'),
            'label' => $this->t('ver más'),
            'url' => '/',
            'type' => 'button',
            'show' => TRUE,
          ],
          'paymentMethodsTitle' => [
            'title' => $this->t('Label Escoge tu forma de pago'),
            'value' => $this->t('Escoge tu forma de pago'),
            'show' => 1,
          ],
          'coreBalance' => [
            'title' => $this->t('coreBalance'),
            'label' => $this->t('Mi saldo:'),
            'url' => '/',
            'type' => 'link',
            'show' => 1,
          ],
          'coreBalanceSumary' => [
            'title' => $this->t('Tu saldo:'),
            'show' => 1,
          ],
          'loanPacketSumary' => [
            'title' => $this->t('Tigo te presta'),
            'show' => 1,
          ],
          'invoiceCharge' => [
            'title' => $this->t('invoiceCharge'),
            'label' => $this->t('Cargo a Factura'),
            'url' => '/',
            'type' => 'link',
            'show' => 1,
          ],
          'creditCard' => [
            'title' => $this->t('creditCard'),
            'label' => $this->t('Tarjeta de Débito/Crédito'),
            'url' => '/',
            'type' => 'link',
            'show' => 1,
          ],
          'Loan_Packets' => [
            'title' => $this->t('Loan_Packets'),
            'label' => $this->t('Préstamo'),
            'url' => '/',
            'type' => 'link',
            'show' => 1,
          ],
          'emergencyLoan' => [
            'title' => $this->t('emergencyLoan'),
            'label' => $this->t('Préstamos de emergencia'),
            'url' => '/',
            'type' => 'link',
            'show' => 1,
          ],
          'freePacket' => [
            'title' => $this->t('freePacket'),
            'label' => $this->t('Activar'),
            'url' => '/',
            'type' => 'button',
            'show' => 1,
          ],
          'tigoMoney' => [
            'title' => $this->t('tigoMoney'),
            'label' => $this->t('tigoMoney'),
            'url' => '/',
            'type' => 'link',
            'show' => 1,
          ],
        ],
        'actions_roles' => [
          'coreBalance',
          'invoiceCharge',
          'creditCard',
          'Loan_Packets',
          'emergencyLoan',
          'freePacket',
          'tigoMoney',
        ],  
        'messages' => [
          'package_error' => [
            'title' => $this->t('Compra Inválida'),
            'label' => $this->t('No tienes saldo suficiente para realizar esta compra.'),
            'show' => TRUE,
          ],
          'number_error' => [
            'title' => $this->t('Número Inválido'),
            'label' => $this->t('El número que ingresaste no es un número Tigo. Por favor inténtelo de nuevo.'),
            'show' => TRUE,
          ],
          'offer_error' => [
            'title' => $this->t('Oferta Inválida'),
            'label' => $this->t('La oferta solicitada no existe. Por favor inténtelo de nuevo.'),
            'show' => TRUE,
          ],
          'gift_invalid' => [
            'title' => $this->t('Regalo Inválido'),
            'label' => $this->t('El número de línea/oferta no es válido para regalo. Inténtelo de nuevo.'),
            'show' => TRUE,
          ],
          'gift_not_allowed' => [
            'title' => $this->t('Regalo no permitido'),
            'label' => $this->t('No puedes realizar compras de paquetes a alguien más, únicamente a tu línea.'),
            'show' => TRUE,
          ],
          'verifyCoreBalance' => [
            'title' => $this->t('Mensaje Pantalla de Verificación método de pago "Mi saldo"'),
            'label' => $this->t('Se descontará @amount de tu saldo de recargas para realizar la compra del paquete:'),
            'show' => TRUE,
          ],
          'verifyinvoiceCharge' => [
            'title' => $this->t('Mensaje Pantalla de Verificación método de pago "Cargo a factura"'),
            'label' => $this->t('Se cargarán @amount en tu siguiente factura'),
            'show' => TRUE,
          ],
          'verifyLoanPackets' => [
            'title' => $this->t('Mensaje Pantalla de Verificación método de pago "Prestamos"'),
            'label' => $this->t('Tu próxima recarga debe ser igual o superior al valor de préstamo: @amount'),
            'show' => TRUE,
          ],
        ],
        'response' => [
          'getInfo' => [
            'notFound' => $this->t('No se encontraron resultados.'),
            'error' => $this->t('En este momento no podemos obtener información de la oferta, intenta de nuevo más tarde.'),
          ],
          'invoiceChargeVerify' => [
            'title' => [
              'label' => $this->t('Resumen'),
              'show' => 1,
            ],
            'invoiceChargeVerify' => [
              'label' => $this->t('Datos de pago:'),
              'value' => '',
              'show' => 1,
            ],
            'productType' => [
              'label' => $this->t('Tipo de producto:'),
              'value' => '',
              'show' => 1,
            ],
            'paymentMethodTitle' => [
              'label' => $this->t('Forma de pago:'),
              'value' => '',
              'show' => 1,
            ],
            'paymentMethod' => [
              'label' => $this->t('Método de pago:'),
              'value' => $this->t('Cargo a factura'),
              'show' => 1,
            ],
            'cancelButtons' => [
              'label' => $this->t('CANCELAR'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'purchaseButtons' => [
              'label' => $this->t('COMPRAR'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'termsAndConditions' => [
              'label' => $this->t('Al presionar COMPRAR estás aceptando los términos y condiciones.'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
          ],
          'coreBalanceVerify' => [
            'title' => [
              'label' => $this->t('Resumen'),
              'show' => 1,
            ],
            'coreBalanceVerify' => [
              'label' => $this->t('Datos de pago:'),
              'value' => '',
              'show' => 1,
            ],
            'productType' => [
              'label' => $this->t('Tipo de producto:'),
              'value' => '',
              'show' => 1,
            ],
            'paymentMethodTitle' => [
              'label' => $this->t('Forma de pago:'),
              'value' => '',
              'show' => 1,
            ],
            'paymentMethod' => [
              'label' => $this->t('Método de pago:'),
              'value' => $this->t('Saldo de recargas'),
              'show' => 1,
            ],
            'coreBalance' => [
              'label' => $this->t('Saldo actual:'),
              'value' => '',
              'show' => 1,
            ],
            'changeButtons' => [
              'label' => $this->t('CAMBIAR'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'cancelButtons' => [
              'label' => $this->t('CANCELAR'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'purchaseButtons' => [
              'label' => $this->t('COMPRAR'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'termsAndConditions' => [
              'label' => $this->t('Al presionar COMPRAR estás aceptando los términos y condiciones.'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
          ],
          'loanPacketsVerify' => [
            'title' => [
              'label' => $this->t('Préstamo'),
              'show' => 1,
            ],
            'loanPacketsVerify' => [
              'label' => $this->t('Datos de pago:'),
              'value' => '',
              'show' => 1,
            ],
            'targetAccountNumber' => [
              'label' => $this->t('Número: '),
              'value' => '',
              'show' => 1,
            ],
            'purchaseDetail' => [
              'label' => $this->t('Detalle de compra: '),
              'show' => 1,
            ],
            'loanAmount' => [
              'label' => $this->t('Valor: '),
              'value' => '',
              'show' => 1,
            ],
            'feeAmount' => [
              'label' => $this->t('Valor servicio:'),
              'value' => '',
              'show' => 1,
            ],
            'paymentMethodTitle' => [
              'label' => $this->t('Forma de pago:'),
              'value' => '',
              'show' => 1,
            ],
            'paymentMethod' => [
              'label' => $this->t('Método de pago:'),
              'value' => $this->t('Préstamo'),
              'show' => 1,
            ],
            'cancelButtons' => [
              'label' => $this->t('CANCELAR'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'purchaseButtons' => [
              'label' => $this->t('CONFIRMAR'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'termsAndConditions' => [
              'label' => $this->t('Al presionar COMPRAR estás aceptando los términos y condiciones.'),
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
          ],
          'postSuccess' => [
            'title' => [
              'label' => $this->t('¡Compra realizada con éxito!'),
              'show' => 1,
            ],
            'message' => [
              'label' => $this->t('Se ha enviado un comprobante a:'),
              'show' => 1,
            ],
            'paymentMethod' => [
              'label' => $this->t('Método de pago:'),
              'value' => $this->t('Saldo de Recarga'),
              'show' => 1,
            ],
            'details' => [
              'label' => $this->t('VER DETALLES'),
              'type' => 'link',
              'show' => 1,
            ],
            'home' => [
              'label' => $this->t('VOLVER AL INICIO'),
              'labelForFavorite' => $this->t('VOLVER AL INICIO'),
              'urlForFavorite' => '/',
              'type' => 'link',
              'url' => '/',
              'show' => 1,
            ],
            'transactionDetailsTitle' => [
              'label' => 'Label Detalles de transacción',
              'value' => 'Detalles de la transacción',
              'show' => 1,
            ],
            'transactionDetailsId' => [
              'label' => 'Label Id de Transacción',
              'value' => 'Id de Transacción',
              'show' => 1,
            ],
            'transactionDetailsDetail' => [
              'label' => 'Label Detalle de compra',
              'value' => 'Detalle de compra',
              'show' => 1,
            ],
            'transactionDetailsMSISDN' => [
              'label' => 'Label Número de linea:',
              'value' => 'Número de linea:',
              'show' => 1,
            ],
            'transactionDetailsValidity' => [
              'label' => 'Label Vigencia',
              'value' => 'Vigencia',
              'show' => 1,
            ],
            'transactionDetailsPrice' => [
              'label' => 'Label Precio',
              'value' => 'Precio',
              'show' => 1,
            ],
          ],
          'postFailed' => [
            'title' => [
              'label' => $this->t('¡Pago no realizado!'),
              'show' => 1,
            ],
            'message' => [
              'label' => $this->t('No se pudo realizar la compra, intentelo más tarde nuevamente.'),
              'show' => 1,
            ],
            'home' => [
              'label' => $this->t('VOLVER AL INICIO'),
              'labelForFavorite' => $this->t('Configurar'),
              'type' => 'link',
              'url' => '/',
              'urlForFavorite' => '/movil/servicios',
              'show' => 1,
              'setupFavorite' => false,
            ],
          ],
          'postSuccessLoan' => [
            'title' => [
              'label' => $this->t('¡Préstamo realizado con éxito!'),
              'show' => 1,
            ],
            'message' => [
              'label' => $this->t('Se ha enviado un sms con los detalles de la transaccion a:'),
              'show' => 1,
            ],
            'paymentMethod' => [
              'label' => $this->t('Método de pago:'),
              'value' => $this->t('Préstamo'),
              'show' => 1,
            ],
          ],
          'postFailedLoan' => [
            'title' => [
              'label' => $this->t('¡Préstamo no realizado!'),
              'show' => 1,
            ],
            'message' => [
              'label' => $this->t('No se pudo realizar el préstamo, intentelo más tarde nuevamente.'),
              'show' => 1,
            ],
          ],
          'deleteSubscribeSuccess' => [
            'title' => $this->t('Texto para Eliminacion exitosa de una suscripcion'),
            'label' => $this->t('Tu suscripción ha sido eliminada con éxito.'),
            'show' => TRUE,
          ],
          'deleteSubscribeFailed' => [
            'title' => $this->t('Texto para Eliminacion fallida de una suscripcion'),
            'label' => $this->t('La solicitud no pudo ser procesa. Por favor intente de nuevo mas tarde.'),
            'show' => TRUE,
          ],
        ],
      ],
    ];

    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {
      return [
        'fields' => $this->defaultConfig['data']['fields'],
        'config' => $this->defaultConfig['config'],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {
    $form['fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Contenido'),
      '#open' => FALSE,
    ];
    $form['config'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuración'),
      '#open' => TRUE,
    ];

    $this->configDataFields($form);
    $this->configConfigPaymentMethods($form);
    $this->configConfigResponse($form);
    $this->configMessageResponse($form);
    // $this->configOthers($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configMessageResponse(&$form) {

    $messages = $this->configuration['config']['messages'];

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
      ],
      '#empty' => $this->t('There are no items yet. Add an item.'),
    ];

    foreach ($messages as $id => $entity) {

      $form['messages']['properties'][$id]['title'] = [
        '#type' => 'hidden',
        '#default_value' => $entity['title'],
        '#suffix' => $entity['title'],
      ];
      if ($id == 'verifyCoreBalance' || $id == 'verifyinvoiceCharge') {
        $form['messages']['properties'][$id]['label'] = [
          '#type' => 'textfield',
          '#default_value' => $entity['label'],
          '#description' => $this->t('Debe introducir en el mensaje @amount para obtener el monto'),
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
   * {@inheritdoc}
   */
  public function configDataFields(&$form) {
    $fields = $this->configuration['fields'];

    $form['fields']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
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
    ];

    foreach ($fields as $id => $entity) {
      $fields[$id]['weight'] = $entity['weight'];
    }

    uasort($fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    foreach ($fields as $id => $entity) {
      $form['fields']['fields'][$id]['#attributes']['class'][] = 'draggable';

      $form['fields']['fields'][$id]['field'] = [
        '#plain_text' => $this->defaultConfig['data']['fields'][$id]['title'],
      ];

      $form['fields']['fields'][$id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $entity['label'],
      ];

      $form['fields']['fields'][$id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $entity['show'],
      ];

      $form['fields']['fields'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $this->defaultConfig['data']['fields'][$id]['title']]),
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
  public function configConfigPaymentMethods(&$form) {
    $form['config']['actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones Pantalla Métodos de Pago y Botones'),
      '#open' => FALSE,
    ];
    $coreBalance = $this->configuration['config']['actions']['coreBalance'];
    $form['config']['actions']['coreBalance'] = [
      '#type' => 'details',
      '#title' => $this->t('Saldo principal'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['coreBalance']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago "Saldo principal"'),
      '#default_value' => $coreBalance['show'],
    ];
    $form['config']['actions']['coreBalance']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Método de pago'),
      '#default_value' => $coreBalance['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][coreBalance][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['coreBalance']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $coreBalance['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][coreBalance][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['coreBalance']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $coreBalance['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][coreBalance][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['coreBalance']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $coreBalance['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][coreBalance][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $invoiceCharge = $this->configuration['config']['actions']['invoiceCharge'];
    $form['config']['actions']['invoiceCharge'] = [
      '#type' => 'details',
      '#title' => $this->t('Cargo a Factura'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['invoiceCharge']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago "Cargo a Factura"'),
      '#default_value' => $invoiceCharge['show'],
    ];
    $form['config']['actions']['invoiceCharge']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Método de pago'),
      '#default_value' => $invoiceCharge['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][invoiceCharge][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['invoiceCharge']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $invoiceCharge['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][invoiceCharge][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['invoiceCharge']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $invoiceCharge['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][invoiceCharge][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['invoiceCharge']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $invoiceCharge['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][invoiceCharge][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $creditCardAction = $this->configuration['config']['actions']['creditCard'];
    $form['config']['actions']['creditCard'] = [
      '#type' => 'details',
      '#title' => $this->t('Tarjeta de Débito o Crédito'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['creditCard']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago "Tarjeta de Débito o Crédito"'),
      '#default_value' => $creditCardAction['show'],
    ];
    $form['config']['actions']['creditCard']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Método de pago'),
      '#default_value' => $creditCardAction['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][creditCard][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['creditCard']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $creditCardAction['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][creditCard][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['creditCard']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $creditCardAction['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][creditCard][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['creditCard']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $creditCardAction['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][creditCard][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $loanPackets = $this->configuration['config']['actions']['Loan_Packets'];
    $form['config']['actions']['Loan_Packets'] = [
      '#type' => 'details',
      '#title' => $this->t('Prestamo'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['Loan_Packets']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago "Prestamo"'),
      '#default_value' => $loanPackets['show'],
    ];
    $form['config']['actions']['Loan_Packets']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Método de pago'),
      '#default_value' => $loanPackets['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][Loan_Packets][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['Loan_Packets']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $loanPackets['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][Loan_Packets][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['Loan_Packets']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $loanPackets['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][Loan_Packets][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['Loan_Packets']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $loanPackets['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][Loan_Packets][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $emergencyLoan = $this->configuration['config']['actions']['emergencyLoan'];
    $form['config']['actions']['emergencyLoan'] = [
      '#type' => 'details',
      '#title' => $this->t('Prestamos de emergencia'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['emergencyLoan']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago "Prestamos de emergencia"'),
      '#default_value' => $emergencyLoan['show'],
    ];
    $form['config']['actions']['emergencyLoan']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Método de pago'),
      '#default_value' => $emergencyLoan['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][emergencyLoan][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['emergencyLoan']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $emergencyLoan['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][emergencyLoan][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['emergencyLoan']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $emergencyLoan['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][emergencyLoan][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['emergencyLoan']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $emergencyLoan['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][emergencyLoan][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $freePacket = $this->configuration['config']['actions']['freePacket'];
    $form['config']['actions']['freePacket'] = [
      '#type' => 'details',
      '#title' => $this->t('Free Packet'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['freePacket']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago "Free Packet"'),
      '#default_value' => $freePacket['show'],
    ];
    $form['config']['actions']['freePacket']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Método de pago'),
      '#default_value' => $freePacket['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][freePacket][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['freePacket']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $freePacket['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][freePacket][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['freePacket']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $freePacket['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][freePacket][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['freePacket']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $freePacket['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][freePacket][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $paymentMethodsTitle = $this->configuration['config']['actions']['paymentMethodsTitle'];
    $form['config']['actions']['paymentMethodsTitle'] = [
      '#type' => 'details',
      '#title' => $this->t('Titulo de Metodos de Pago'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['paymentMethodsTitle']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar título Pantalla Métodos de pago'),
      '#default_value' => $paymentMethodsTitle['show'],
    ];
    $form['config']['actions']['paymentMethodsTitle']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $paymentMethodsTitle['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][paymentMethodsTitle][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $coreBalanceSumary = $this->configuration['config']['actions']['coreBalanceSumary'];
    $form['config']['actions']['coreBalanceSumary'] = [
      '#type' => 'details',
      '#title' => $this->t('Mi saldo'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['coreBalanceSumary']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar label "Tu Saldo"'),
      '#default_value' => $coreBalanceSumary['show'],
    ];
    $form['config']['actions']['coreBalanceSumary']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label Tu saldo'),
      '#default_value' => $coreBalanceSumary['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][coreBalanceSumary][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $loanPacketSumary = $this->configuration['config']['actions']['loanPacketSumary'];
    $form['config']['actions']['loanPacketSumary'] = [
      '#type' => 'details',
      '#title' => $this->t('Tigo te presta'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['loanPacketSumary']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar label "Tigo te presta"'),
      '#default_value' => $loanPacketSumary['show'],
    ];
    $form['config']['actions']['loanPacketSumary']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label Tigo te presta'),
      '#default_value' => $loanPacketSumary['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][loanPacketSumary][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $changeMsisdnAction = $this->configuration['config']['actions']['changeMsisdn'];
    $form['config']['actions']['changeMsisdn'] = [
      '#type' => 'details',
      '#title' => $this->t('Cambiar Línea'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['changeMsisdn']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar enlace: "Cambiar Línea"'),
      '#default_value' => $changeMsisdnAction['show'],
    ];
    $form['config']['actions']['changeMsisdn']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $changeMsisdnAction['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][changeMsisdn][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['changeMsisdn']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $changeMsisdnAction['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][changeMsisdn][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['changeMsisdn']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $changeMsisdnAction['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][changeMsisdn][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['changeMsisdn']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $changeMsisdnAction['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][changeMsisdn][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $fulldescriptionAction = $this->configuration['config']['actions']['fulldescription'];
    $form['config']['actions']['fulldescription'] = [
      '#type' => 'details',
      '#title' => $this->t('ver mas'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['fulldescription']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar enlace: "ver mas"'),
      '#default_value' => $fulldescriptionAction['show'],
    ];
    $form['config']['actions']['fulldescription']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $fulldescriptionAction['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][fulldescription][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['fulldescription']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $fulldescriptionAction['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][fulldescription][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['fulldescription']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $fulldescriptionAction['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][fulldescription][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['fulldescription']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $fulldescriptionAction['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][fulldescription][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $tigo_money = $this->configuration['config']['actions']['tigoMoney'];
    $form['config']['actions']['tigoMoney'] = [
      '#type' => 'details',
      '#title' => $this->t('TigoMoney'),
      '#open' => FALSE,
    ];
    $form['config']['actions']['tigoMoney']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago "TigoMoney"'),
      '#default_value' => $tigo_money['show'],
    ];
    $form['config']['actions']['tigoMoney']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Método de pago'),
      '#default_value' => $tigo_money['title'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][tigoMoney][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['tigoMoney']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label del botón'),
      '#default_value' => $tigo_money['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][tigoMoney][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['tigoMoney']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $tigo_money['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][tigoMoney][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['actions']['tigoMoney']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Tipo'),
      '#default_value' => $tigo_money['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][actions][tigoMoney][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

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
          '#title' => $this->t('Roles restringidos para ' . $this->configuration['config']['actions'][$action_role]['title']),
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
  public function configConfigResponse(&$form) {

    $form['config']['response'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones Pantallas de Verificación'),
      '#open' => FALSE,
    ];

    $response = $this->configuration['config']['response'] ? $this->configuration['config']['response'] : $this->defaultConfig['config']['response'];

    $form['config']['response']['getInfo'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Obtener datos'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['config']['response']['getInfo']['notFound'] = [
      '#type' => 'textfield',
      '#title' => $this->t('No se encontraron datos'),
      '#default_value' => $response['getInfo']['notFound'],
    ];
    $form['config']['response']['getInfo']['error'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje por defecto de error'),
      '#default_value' => $response['getInfo']['error'],
    ];
    //-------------- Cargo a Factura----------------------//
    $form['config']['response']['invoiceChargeVerify'] = [
      '#type' => 'details',
      '#title' => $this->t('Pantalla Verificación Método de Pago "Cargo a factura"'),
      '#open' => FALSE,
    ];
    $form['config']['response']['invoiceChargeVerify']['title']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar título'),
      '#default_value' => $response['invoiceChargeVerify']['title']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['title']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][title][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['paymentMethod']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago'),
      '#default_value' => $response['invoiceChargeVerify']['paymentMethod']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['paymentMethod']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['paymentMethod']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['paymentMethod']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['invoiceChargeVerify']['paymentMethod']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['invoiceChargeVerify']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Datos de pago"'),
      '#default_value' => $response['invoiceChargeVerify']['invoiceChargeVerify']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['invoiceChargeVerify']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['invoiceChargeVerify']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][invoiceChargeVerify][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['productType']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Tipo de producto'),
      '#default_value' => $response['invoiceChargeVerify']['productType']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['productType']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['productType']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][productType][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['paymentMethodTitle']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Método de Pago'),
      '#default_value' => $response['invoiceChargeVerify']['paymentMethodTitle']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['paymentMethodTitle']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['paymentMethodTitle']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][paymentMethodTitle][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['cancelButtons']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Cancelar'),
      '#default_value' => $response['invoiceChargeVerify']['cancelButtons']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['cancelButtons']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['cancelButtons']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['cancelButtons']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['invoiceChargeVerify']['cancelButtons']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['cancelButtons']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['invoiceChargeVerify']['cancelButtons']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['purchaseButtons']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Comprar'),
      '#default_value' => $response['invoiceChargeVerify']['purchaseButtons']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['purchaseButtons']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['purchaseButtons']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['purchaseButtons']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['invoiceChargeVerify']['purchaseButtons']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['purchaseButtons']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['invoiceChargeVerify']['purchaseButtons']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['termsAndConditions']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Terminos y Condiciones'),
      '#default_value' => $response['invoiceChargeVerify']['termsAndConditions']['show'],
    ];
    $form['config']['response']['invoiceChargeVerify']['termsAndConditions']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['invoiceChargeVerify']['termsAndConditions']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['termsAndConditions']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['invoiceChargeVerify']['termsAndConditions']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['invoiceChargeVerify']['termsAndConditions']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['invoiceChargeVerify']['termsAndConditions']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][invoiceChargeVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    //------------------------Prestamo------------------------------//
    $form['config']['response']['loanPacketsVerify'] = [
      '#type' => 'details',
      '#title' => $this->t('Pantalla Verificación Método de Pago "Prestamo"'),
      '#open' => FALSE,
    ];
    $form['config']['response']['loanPacketsVerify']['title']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar título'),
      '#default_value' => $response['loanPacketsVerify']['title']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['title']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][title][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['paymentMethod']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago'),
      '#default_value' => $response['loanPacketsVerify']['paymentMethod']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['paymentMethod']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['paymentMethod']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['paymentMethod']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['loanPacketsVerify']['paymentMethod']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['loanPacketsVerify']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Datos de pago"'),
      '#default_value' => $response['loanPacketsVerify']['loanPacketsVerify']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['loanPacketsVerify']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['loanPacketsVerify']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][loanPacketsVerify][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['targetAccountNumber']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Target Account Number'),
      '#default_value' => $response['loanPacketsVerify']['targetAccountNumber']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['targetAccountNumber']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['targetAccountNumber']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][targetAccountNumber][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['purchaseDetail']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Detalle de compra'),
      '#default_value' => $response['loanPacketsVerify']['purchaseDetail']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['purchaseDetail']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['purchaseDetail']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][purchaseDetail][targetAccountNumber][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['loanAmount']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Valor'),
      '#default_value' => $response['loanPacketsVerify']['loanAmount']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['loanAmount']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['loanAmount']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][loanAmount][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['feeAmount']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Valor del Servicio'),
      '#default_value' => $response['loanPacketsVerify']['feeAmount']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['feeAmount']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['feeAmount']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][feeAmount][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['paymentMethodTitle']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Método de Pago'),
      '#default_value' => $response['loanPacketsVerify']['paymentMethodTitle']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['paymentMethodTitle']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['paymentMethodTitle']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][paymentMethodTitle][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['cancelButtons']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Cancelar'),
      '#default_value' => $response['loanPacketsVerify']['cancelButtons']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['cancelButtons']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['cancelButtons']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['cancelButtons']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['loanPacketsVerify']['cancelButtons']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['cancelButtons']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['loanPacketsVerify']['cancelButtons']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['purchaseButtons']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Comprar'),
      '#default_value' => $response['loanPacketsVerify']['purchaseButtons']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['purchaseButtons']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['purchaseButtons']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['purchaseButtons']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['loanPacketsVerify']['purchaseButtons']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['purchaseButtons']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['loanPacketsVerify']['purchaseButtons']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['termsAndConditions']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Terminos y Condiciones'),
      '#default_value' => $response['loanPacketsVerify']['termsAndConditions']['show'],
    ];
    $form['config']['response']['loanPacketsVerify']['termsAndConditions']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['loanPacketsVerify']['termsAndConditions']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['termsAndConditions']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['loanPacketsVerify']['termsAndConditions']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['loanPacketsVerify']['termsAndConditions']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['loanPacketsVerify']['termsAndConditions']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][loanPacketsVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    //--------------------Saldo-------------------------------------//
    $form['config']['response']['coreBalanceVerify'] = [
      '#type' => 'details',
      '#title' => $this->t('Pantalla Verificación Método de Pago "Saldo de recargas"'),
      '#open' => FALSE,
    ];
    $form['config']['response']['coreBalanceVerify']['title']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago'),
      '#default_value' => $response['coreBalanceVerify']['title']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['title']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][title][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['paymentMethod']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago'),
      '#default_value' => $response['coreBalanceVerify']['paymentMethod']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['paymentMethod']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['paymentMethod']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['paymentMethod']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['coreBalanceVerify']['paymentMethod']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['coreBalanceVerify']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Datos de pago"'),
      '#default_value' => $response['coreBalanceVerify']['coreBalanceVerify']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['coreBalanceVerify']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['coreBalanceVerify']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][coreBalanceVerify][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['productType']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Tipo de producto'),
      '#default_value' => $response['coreBalanceVerify']['productType']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['productType']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['productType']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][productType][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['paymentMethodTitle']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Método de Pago'),
      '#default_value' => $response['coreBalanceVerify']['paymentMethodTitle']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['paymentMethodTitle']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['paymentMethodTitle']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][paymentMethodTitle][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['coreBalance']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Saldo actual'),
      '#default_value' => $response['coreBalanceVerify']['coreBalance']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['coreBalance']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['coreBalance']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][coreBalance][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['cancelButtons']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Cancelar'),
      '#default_value' => $response['coreBalanceVerify']['cancelButtons']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['cancelButtons']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['cancelButtons']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['cancelButtons']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['coreBalanceVerify']['cancelButtons']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['cancelButtons']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['coreBalanceVerify']['cancelButtons']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][cancelButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['purchaseButtons']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Comprar'),
      '#default_value' => $response['coreBalanceVerify']['purchaseButtons']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['purchaseButtons']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['purchaseButtons']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['purchaseButtons']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['coreBalanceVerify']['purchaseButtons']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['purchaseButtons']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['coreBalanceVerify']['purchaseButtons']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][purchaseButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['termsAndConditions']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Terminos y Condiciones'),
      '#default_value' => $response['coreBalanceVerify']['termsAndConditions']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['termsAndConditions']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['termsAndConditions']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['termsAndConditions']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['coreBalanceVerify']['termsAndConditions']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['termsAndConditions']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['coreBalanceVerify']['termsAndConditions']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][termsAndConditions][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['config']['response']['coreBalanceVerify']['changeButtons']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón Cambiar'),
      '#default_value' => $response['coreBalanceVerify']['changeButtons']['show'],
    ];
    $form['config']['response']['coreBalanceVerify']['changeButtons']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['coreBalanceVerify']['changeButtons']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][changeButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['changeButtons']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['coreBalanceVerify']['changeButtons']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][changeButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['coreBalanceVerify']['changeButtons']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['coreBalanceVerify']['changeButtons']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][coreBalanceVerify][changeButtons][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Response postSuccess.
    $form['config']['response']['postSuccess'] = [
      '#type' => 'details',
      '#title' => $this->t('Compra exitosa'),
      '#open' => FALSE,
    ];
    $form['config']['response']['postSuccess']['title']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Título'),
      '#default_value' => $response['postSuccess']['title']['show'],
    ];
    $form['config']['response']['postSuccess']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postSuccess']['title']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][title][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['message']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Mensaje'),
      '#default_value' => $response['postSuccess']['message']['show'],
    ];
    $form['config']['response']['postSuccess']['message']['label'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postSuccess']['message']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][message][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['paymentMethod']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar método de pago'),
      '#default_value' => $response['postSuccess']['paymentMethod']['show'],
    ];
    $form['config']['response']['postSuccess']['paymentMethod']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postSuccess']['paymentMethod']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['paymentMethod']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['paymentMethod']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['details']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón "Ver detalles"'),
      '#default_value' => $response['postSuccess']['details']['show'],
    ];
    $form['config']['response']['postSuccess']['details']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postSuccess']['details']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][details][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['details']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['details']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][details][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['home']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón "VOLVER AL INICIO"'),
      '#default_value' => $response['postSuccess']['home']['show'],
    ];
    $form['config']['response']['postSuccess']['home']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postSuccess']['home']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['home']['labelForFavorite'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta para redireccion a configurar favoritos'),
      '#default_value' => $response['postSuccess']['home']['labelForFavorite'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['home']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['home']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['home']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['postSuccess']['home']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['home']['urlForFavorite'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL para redireccion a configurar Favoritos'),
      '#default_value' => $response['postSuccess']['home']['urlForFavorite'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsTitle']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Detalles de transacción'),
      '#default_value' => $response['postSuccess']['transactionDetailsTitle']['show'],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsTitle']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['transactionDetailsTitle']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][transactionDetailsTitle][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsId']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Id de transacción'),
      '#default_value' => $response['postSuccess']['transactionDetailsId']['show'],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsId']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['transactionDetailsId']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][transactionDetailsId][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsDetail']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Detalle de compra'),
      '#default_value' => $response['postSuccess']['transactionDetailsDetail']['show'],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsDetail']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['transactionDetailsDetail']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][transactionDetailsDetail][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsMSISDN']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Número de Línea'),
      '#default_value' => $response['postSuccess']['transactionDetailsMSISDN']['show'],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsMSISDN']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['transactionDetailsMSISDN']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][transactionDetailsMSISDN][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsValidity']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Vigencia'),
      '#default_value' => $response['postSuccess']['transactionDetailsValidity']['show'],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsValidity']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['transactionDetailsValidity']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][transactionDetailsValidity][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsPrice']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Precio'),
      '#default_value' => $response['postSuccess']['transactionDetailsPrice']['show'],
    ];
    $form['config']['response']['postSuccess']['transactionDetailsPrice']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postSuccess']['transactionDetailsPrice']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccess][transactionDetailsPrice][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Response postFailed.
    $form['config']['response']['postFailed'] = [
      '#type' => 'details',
      '#title' => $this->t('Compra fallida'),
      '#open' => FALSE,
    ];
    $form['config']['response']['postFailed']['title']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Título'),
      '#default_value' => $response['postFailed']['title']['show'],
    ];
    $form['config']['response']['postFailed']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postFailed']['title']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postFailed][title][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postFailed']['message']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Mensaje'),
      '#default_value' => $response['postFailed']['message']['show'],
    ];
    $form['config']['response']['postFailed']['message']['label'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postFailed']['message']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postFailed][message][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postFailed']['home']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón "Volver al inicio"'),
      '#default_value' => $response['postFailed']['home']['show'],
    ];
    $form['config']['response']['postFailed']['home']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $response['postFailed']['home']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postFailed][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postFailed']['home']['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Valor'),
      '#default_value' => $response['postFailed']['home']['type'],
      '#options' => ['button' => 'Button', 'link' => 'Link'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postFailed][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postFailed']['home']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $response['postFailed']['home']['url'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postFailed][home][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccessLoan'] = [
      '#type' => 'details',
      '#title' => $this->t('Compra exitosa - Prestamo'),
      '#open' => FALSE,
    ];
    $form['config']['response']['postSuccessLoan']['title']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Titulo'),
      '#default_value' => $response['postSuccessLoan']['title']['show'],
    ];
    $form['config']['response']['postSuccessLoan']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta del titulo'),
      '#default_value' => $response['postSuccessLoan']['title']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccessLoan][title][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccessLoan']['message']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Mensaje'),
      '#default_value' => $response['postSuccessLoan']['message']['show'],
    ];
    $form['config']['response']['postSuccessLoan']['message']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta del Mensaje'),
      '#default_value' => $response['postSuccessLoan']['message']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccessLoan][message][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccessLoan']['paymentMethod']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Metodo'),
      '#default_value' => $response['postSuccessLoan']['paymentMethod']['show'],
    ];
    $form['config']['response']['postSuccessLoan']['paymentMethod']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta del Metodo de Pago'),
      '#default_value' => $response['postSuccessLoan']['paymentMethod']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccessLoan][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postSuccessLoan']['paymentMethod']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valor del Metodo de Pago'),
      '#default_value' => $response['postSuccessLoan']['paymentMethod']['value'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postSuccessLoan][paymentMethod][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postFailedLoan'] = [
      '#type' => 'details',
      '#title' => $this->t('Compra fallida - Prestamo'),
      '#open' => FALSE,
    ];
    $form['config']['response']['postFailedLoan']['title']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Titulo'),
      '#default_value' => $response['postFailedLoan']['title']['show'],
    ];
    $form['config']['response']['postFailedLoan']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta del titulo'),
      '#default_value' => $response['postFailedLoan']['title']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postFailedLoan][title][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['config']['response']['postFailedLoan']['message']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Mensaje'),
      '#default_value' => $response['postFailedLoan']['message']['show'],
    ];
    $form['config']['response']['postFailedLoan']['message']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta del Mensaje'),
      '#default_value' => $response['postFailedLoan']['message']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][postFailedLoan][message][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['config']['response']['deleteSubscribeSuccess'] = [
      '#type' => 'details',
      '#title' => $this->t('Eliminar suscripcion exitosa'),
      '#open' => FALSE,
    ];
    $form['config']['response']['deleteSubscribeSuccess']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Etiqueta'),
      '#default_value' => $response['deleteSubscribeSuccess']['show'],
    ];
    $form['config']['response']['deleteSubscribeSuccess']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta del mensaje'),
      '#default_value' => $response['deleteSubscribeSuccess']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][deleteSubscribeSuccess][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['config']['response']['deleteSubscribeFailed'] = [
      '#type' => 'details',
      '#title' => $this->t('Eliminar suscripcion fallida'),
      '#open' => FALSE,
    ];
    $form['config']['response']['deleteSubscribeFailed']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar Etiqueta'),
      '#default_value' => $response['deleteSubscribeFailed']['show'],
    ];
    $form['config']['response']['deleteSubscribeFailed']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Etiqueta del mensaje'),
      '#default_value' => $response['deleteSubscribeFailed']['label'],
      '#states' => [
        'visible' => [
          ':input[name="settings[config][response][deleteSubscribeFailed][show]"]' => ['checked' => TRUE],
        ],
      ],
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['fields'] = $form_state->getValue(['fields', 'fields']);
    $this->configuration['config'] = $form_state->getValue(['config']);
    $this->configuration['config']['messages'] = $form_state->getValue(['messages', 'properties']);
    $this->configuration['actions_roles'] = $form_state->getValue('actions_roles');
  }

}
