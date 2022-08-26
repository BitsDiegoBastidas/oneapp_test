<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Plugin\Block\v2_0;

use Drupal\adf_block_config\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\oneapp_convergent_upgrade_plan\Plugin\Block\v2_0\UpgradeBlock;

class UpgradeGtBlock extends UpgradeBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    }
    else {

      // Card Upgrade Plan Config.
      $config_block['upgradePlan'] = [
        'fields' => [
          'title' => [
            'value' => '',
            'show' => 1,
          ],
          'banner' => [
            'url' => '',
            'show' => 1,
          ],
          'description' => [
            'value' => '',
          ],
        ],
        'actions' => [
          'card' => [
            'planUpgradeAction' => [
              'label' => '',
              'show' => 1,
            ],
          ],
        ],
      ];

      // Card Recommended Offers Config.
      $config_block['recommendedOffers'] = [
        'fields' => [
          'static' => [
            'plan' => [
              'label' => t('Plan'),
              'show' => 1,
            ],
            'iva' => [
              'label' => t('Cargo basico IVA incluido'),
              'show' => 1,
            ],
          ],
          'dynamic' => [
            'products' => [],
          ],
          'verification' => [
            'fields' => [
              'title' => [
                'label' => t('Revisa y confirma la mejora de tu plan!'),
                'show' => 1,
              ],
              'detail' => [
                'label' => t('Detalle'),
                'show' => 1,
              ],
              'plan' => [
                'label' => t('Tu nuevo plan'),
                'show' => 1,
              ],
              'bill' => [
                'label' => t('Cuenta'),
                'show' => 1,
              ],
              'quota' => [
                'label' => t('Quota del plan'),
                'show' => 1,
              ],
              'date' => [
                'label' => t('Se renovará el día dias de MES'),
                'show' => 1,
                'formatDate' => 'short',
              ],
              'terms' => [
                'label' => t('Términos y Condiciones'),
                'show' => 1,
              ],
              'termsDesc' => [
                'value' => t('Al presionar CONFIRMAR estás aceptando los @termsConditions.'),
              ],
            ],
          ],
        ],
        'actions' => [
          'card' => [
            'recommendedOffersAction' => [
              'label' => t('Mejora Ya'),
              'type' => 'button',
              'url' => [
                'oneapp' => '',
                'selfcare' => '',
              ],
              'show' => TRUE,
            ],
            'verificationActionAccept' => [
              'label' => t('Confirmar'),
              'type' => 'button',
              'url' => [
                'oneapp' => '',
                'selfcare' => '',
              ],
              'show' => TRUE,
            ],
            'verificationActionCancel' => [
              'label' => t('Cancelar'),
              'type' => 'button',
              'url' => [
                'oneapp' => '',
                'selfcare' => '',
              ],
              'show' => TRUE,
            ],
          ],
        ],
      ];

      // Card Recommended Offers Config.
      $config_block['confirmationUpgradePlan'] = [
        'cardConfirmation' => [
          'fields' => [
            'title' => [
              'label' => t('¡Servicio activado con exito!'),
              'show' => 1,
            ],
            'desc' => [
              'label' => t('Se ha enviado un comprovante a'),
            ],
          ],
        ],
        'cardDetail' => [
          'fields' => [
            'title' => [
              'label' => t('Detalles de la transacción'),
              'show' => 1,
            ],
            'plan' => [
              'label' => t('Detalles del plan'),
              'show' => 1,
            ],
            'account' => [
              'label' => t('Cuenta'),
              'show' => 1,
            ],
            'price' => [
              'label' => t('Precio'),
              'show' => 1,
            ],
            'activateDate' => [
              'label' => t('Fecha de activación'),
              'show' => 1,
              'formatDate' => 'short',
            ],
            'footer' => [
              'label' => t('* Precio incluye IVA, pero no incluye servicios adicionales contratados en tu plan.'),
              'show' => 1,
            ],
            'installationDate' => [
              'label' => t('Fecha instalación'),
              'show' => 1,
            ],
            'installationTime' => [
              'label' => t('Hora instalación'),
              'show' => 1,
            ],
            'appointmentId' => [
              'label' => t('Nº de Orden'),
              'show' => 1,
            ],
            'installationNotice' => [
              'label' => t(''),
              'show' => 1,
            ],
          ],
        ],
        'actions' => [
          'card' => [
            'seeProducts' => [
              'label' => t('Ver mas Productos'),
              'type' => 'button',
              'url' => [
                'oneapp' => '',
                'selfcare' => '',
              ],
              'show' => 1,
            ],
            'seeDetail' => [
              'label' => t('Ver Detalles'),
              'type' => 'button',
              'url' => [
                'oneapp' => '',
                'selfcare' => '',
              ],
              'show' => 1,
            ],
          ],
        ],
      ];

      $config_block['generalConfig'] = [
        "decimal_numbers" => 2,
        'orderPlansByAmount' => 'none',
        'orderPlansByFeatured' => 0,
      ];

      return $config_block;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {

    $upgrade_utils_service = \Drupal::service('oneapp_convergent_upgrade_plan.utils');
    $days = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];

    $action_type = [
      'button' => $this->t('Boton'),
      'link' => $this->t('Link'),
    ];

    $utils = \Drupal::service('oneapp.utils');

    $form = [
      '#prefix' => '<div id="container-fields-wrapper">',
      '#suffix' => '</div>',
    ];

    // Fields - CARD MEJORA TU PLAN HOME.
    $grop_fields = 'upgradePlan';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Card Mejora tu Plan - Home Paso 1'),
      '#open' => FALSE,
    ];

    $form[$grop_fields]['fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['fields'], NULL, []);

    $form[$grop_fields]['fields']['title']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['value'], NULL, ''),
    ];

    $form[$grop_fields]['fields']['title']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['fields']['banner']['url'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Imagen del card'),
      '#upload_location' => 'public://' . $upgrade_utils_service::DIRECTORY_IMAGES,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['banner']['url'], NULL, 0),
      '#description' => $this->t('png jpg svg.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg svg'],
      ],
    ];

    $form[$grop_fields]['fields']['banner']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['banner']['show'], NULL, 0),
    ];

    $form[$grop_fields]['fields']['description']['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descricción'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['description']['value'], NULL, ''),
    ];

    // Action - CARD MEJORA TU PLAN HOME.
    $form[$grop_fields]['actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Actions'),
    ];

    $form[$grop_fields]['actions']['card'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Type'),
        t('Url'),
        t('Show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['actions']['card'], NULL, []);

    $form[$grop_fields]['actions']['card']['planUpgradeAction']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mejora tu plan'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['planUpgradeAction']['label'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['planUpgradeAction']['type'] = [
      '#type' => 'select',
      '#options' => $action_type,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['planUpgradeAction']['type'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['planUpgradeAction']['url']['oneapp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oneapp'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['planUpgradeAction']['url']['oneapp'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['planUpgradeAction']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['planUpgradeAction']['url']['selfcare'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['planUpgradeAction']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['planUpgradeAction']['show'], NULL, 0),
    ];

    // Fields static - CARD PLANES RECOMENDADOS.
    $grop_fields = 'recommendedOffers';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Card Planes Recomendados - Home Paso 2'),
      '#open' => FALSE,
    ];

    $form[$grop_fields]['fields']['static'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['fields']['static'], NULL, []);

    $form[$grop_fields]['fields']['static']['plan']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Plan recomendado'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['plan']['label'], NULL, ''),
      '#description' => $this->t('Texto antes del nombre del plan recomendado en los cards.'),
    ];

    $form[$grop_fields]['fields']['static']['plan']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['plan']['show'], NULL, 0),
    ];

    $form[$grop_fields]['fields']['static']['iva']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Especificar si el Precio tiene IVA'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['iva']['label'], NULL, ''),
      '#description' => $this->t('Texto donde se especifica el IVA.'),
    ];

    $form[$grop_fields]['fields']['static']['iva']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['iva']['show'], NULL, 0),
    ];

    // Fields dynamic - CARD PLANES RECOMENDADOS.
    $form[$grop_fields]['fields']['dynamic'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion de Productos - Propiedades de los planes'),
      '#open' => TRUE,
    ];

    $form[$grop_fields]['fields']['dynamic']['products'] = [
      '#type' => 'table',
      '#header' => [
        t('key'),
        t('Label'),
        t('Format'),
        t('Classes'),
        t('Icon Comparative'),
        t('Show'),
      ],
      '#empty' => t('There are no items.'),
    ];

    $remove = ($form_state->get('remove') != NULL) ? $form_state->get('remove') : FALSE;
    $count = ($form_state->get('count') != NULL) ? $form_state->get('count') : 0;
    $config_actions = $upgrade_utils_service->getFieldConfigValue(
      $this->configuration[$grop_fields]['fields']['dynamic']['products'], NULL, []);
    if (count($config_actions) < $count) {
      $config_actions["data{$count}"] = [];
      $form_state->set('count', $count);
    }
    else {
      if ($remove) {
        $count = 0;
        $form_state->set('count', 0);
      }
      elseif ($count != NULL) {
        $form_state->set('count', $count);
        $count = $count;
      }
      else {
        $form_state->set('count', count($config_actions));
        $count = count($config_actions);
      }
    }

    for ($i = 0; $i < $count; $i++) {
      $product_id = "data{$i}";
      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['key'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['key'], NULL, ''),
        '#description' => $this->t('Internet, Price, TV, TV ANALOGA, TV DIGITAL'),
        '#size' => 25,
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['label'], NULL, ''),
        '#description' => $this->t('Velocidad, Precio, Canales Total'),
        '#size' => 25,
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['format'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['format'], NULL, ''),
        '#description' => $this->t('mbps (Internet), currency (Price), Canales (HD, DG, Analogo)'),
        '#size' => 25,
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['class'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['class'], NULL, ''),
        '#description' => $this->t('tv net'),
        '#size' => 25,
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['icon'] = [
        '#type' => 'checkbox',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['icon'], NULL, 0),
        '#description' => $this->t('(√ o X)'),
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['show'], NULL, 0),
      ];
    }

    $form[$grop_fields]['fields']['dynamic']['add'] = [
      '#type' => 'submit',
      '#value' => t('Agregar una dato adicional'),
      '#submit' => [
        [$this, 'addContainerCallback'],
      ],
      '#ajax' => [
        'callback' => [$this, 'addFieldSubmit'],
        'wrapper' => 'container-fields-wrapper',
      ],
      '#attributes' => [
        'data-link-action' => ['Add service to portfolio'],
      ],
    ];

    if ($count > 0) {
      $form[$grop_fields]['fields']['dynamic']['remove'] = [
        '#type' => 'submit',
        '#value' => t('Eliminar un dato adicional'),
        '#submit' => [
          [$this, 'removeContainerCallback'],
        ],
        '#ajax' => [
          'callback' => [$this, 'addFieldSubmit'],
          'wrapper' => 'container-fields-wrapper',
        ],
        '#attributes' => [
          'data-link-action' => ['Delete service to portfolio'],
        ],
      ];
    }

    // Field Verification - CARD PLANES RECOMENDADOS.

    $form[$grop_fields]['verification'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion Card de Verificacion'),
      '#open' => FALSE,
    ];

    $form[$grop_fields]['verification']['fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('show'),
        t('config'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['verification']['fields'], NULL, []);

    $form[$grop_fields]['verification']['fields']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['label'], NULL, ''),
      '#description' => $this->t('Titulo del card de verificacion.'),
    ];

    $form[$grop_fields]['verification']['fields']['title']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['detail']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Detalle'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['detail']['label'], NULL, ''),
      '#description' => $this->t('Label Detalle del card de verificacion.'),
    ];

    $form[$grop_fields]['verification']['fields']['detail']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['plan']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tu nuevo plan'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['plan']['label'], NULL, ''),
      '#description' => $this->t('Label tu nuevo plan.'),
    ];

    $form[$grop_fields]['verification']['fields']['plan']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['plan']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['bill']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cuenta'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['bill']['label'], NULL, ''),
      '#description' => $this->t('Label cuenta del card de verificacion.'),
    ];

    $form[$grop_fields]['verification']['fields']['bill']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['bill']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['quota']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Quota plan'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['quota']['label'], NULL, ''),
      '#description' => $this->t('Label quota del plan de verificacion.'),
    ];

    $form[$grop_fields]['verification']['fields']['quota']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['quota']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['date']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fecha Activacion'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['date']['label'], NULL, ''),
      '#description' => $this->t('Label Fecha activacion del card de verificacion.'),
    ];

    $form[$grop_fields]['verification']['fields']['date']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['date']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['date']['days'] = [
      '#type' => 'select',
      '#title' => $this->t('Seleccione día'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['activateDate']['days'], NULL, ''),
      '#options' => $days,
    ];

    $form[$grop_fields]['verification']['fields']['date']['formatDate'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de fecha'),
      '#description' => $this->t('Seleccione el formato en que se mostraran las fechas por defecto'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['date']['formatDate'], NULL, 'short'
      ),
      '#options' => $utils->getDateFormats(),
    ];

    $form[$grop_fields]['verification']['fields']['terms']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label Terminos & Condiciones'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['terms']['label'], NULL, ''),
      '#description' => $this->t('Texto de los Terminos & Condiciones.'),
    ];

    $form[$grop_fields]['verification']['fields']['terms']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['terms']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['terms']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url Terminos & Condiciones'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['terms']['url'], NULL, ''),
      '#description' => $this->t('Url de los Terminos & Condiciones.'),
    ];

    $form[$grop_fields]['verification']['fields']['termsDesc']['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Descripción Terminos & Condiciones'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['termsDesc']['value'], NULL, ''),
      '#description' => $this->t('Texto de Confirmacion del plan. Usar el token @termsConditions para hacer referencia a la URL.'),
    ];

    $form[$grop_fields]['verification']['fields']['termsModal']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Modal T&C Título'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['termsModal']['title'], NULL, ''),
    ];

    $form[$grop_fields]['verification']['fields']['termsModal']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['termsModal']['show'], NULL, 0),
    ];

    $form[$grop_fields]['verification']['fields']['termsModal']['content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Modal T&C Contenido'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['termsModal']['content'], NULL, ''),
    ];

    $form[$grop_fields]['verification']['fields']['termsModal']['button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Modal T&C Botón etiqueta'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['termsModal']['button'], NULL, ''),
    ];

    // Action - CARD PLANES RECOMENDADOS.
    $form[$grop_fields]['actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Actions'),
    ];

    $form[$grop_fields]['actions']['card'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Type'),
        t('Url'),
        t('Show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['actions']['card'], NULL, []);

    $form[$grop_fields]['actions']['card']['recommendedOffersAction']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mejora Ya'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['recommendedOffersAction']['label'], NULL, ''),
      '#description' => $this->t('Botón mejora Ya'),
    ];

    $form[$grop_fields]['actions']['card']['recommendedOffersAction']['type'] = [
      '#type' => 'select',
      '#options' => $action_type,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['recommendedOffersAction']['type'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['recommendedOffersAction']['url']['oneapp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oneapp'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['recommendedOffersAction']['url']['oneapp'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['recommendedOffersAction']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['recommendedOffersAction']['url']['selfcare'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['recommendedOffersAction']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['recommendedOffersAction']['show'], NULL, 0),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionAccept']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Aceptar'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['verificationActionAccept']['label'], NULL, ''),
      '#description' => $this->t('Botón ACEPTAR en el card de Verificación'),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionAccept']['type'] = [
      '#type' => 'select',
      '#options' => $action_type,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['verificationActionAccept']['type'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionAccept']['url']['oneapp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oneapp +'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['verificationActionAccept']['url']['oneapp'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionAccept']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['verificationActionAccept']['url']['selfcare'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionAccept']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['verificationActionAccept']['show'], NULL, 0),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionCancel']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cancelar'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['verificationActionCancel']['label'], NULL, ''),
      '#description' => $this->t('Botón CANCELAR en el card de Verificación'),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionCancel']['type'] = [
      '#type' => 'select',
      '#options' => $action_type,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['verificationActionCancel']['type'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionCancel']['url']['oneapp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oneapp'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['verificationActionCancel']['url']['oneapp'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionCancel']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['verificationActionCancel']['url']['selfcare'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionCancel']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['verificationActionCancel']['show'], NULL, 0),
    ];

    // Fields static - CARD PLANES RECOMENDADOS.
    $grop_fields = 'confirmationUpgradePlan';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Card Confirmacion Upgrade Plan - Home Paso 3'),
      '#open' => FALSE,
    ];

    $form[$grop_fields]['cardConfirmation'] = [
      '#type' => 'details',
      '#title' => $this->t('Confirmacion'),
    ];

    $form[$grop_fields]['cardConfirmation']['fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue(
      $this->configuration[$grop_fields]['cardConfirmation']['fields'], NULL, []);

    $form[$grop_fields]['cardConfirmation']['fields']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['label'], NULL, ''),
      '#description' => $this->t('¡Servicio activado con exito!'),
    ];

    $form[$grop_fields]['cardConfirmation']['fields']['title']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardConfirmation']['fields']['desc']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Descripción'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['desc']['label'], NULL, ''),
      '#description' => $this->t('Se ha enviado un comprobante a'),
    ];

    $form[$grop_fields]['cardConfirmation']['fields']['zendeskTitle']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title Zendesk'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['zendeskTitle']['label'], NULL, ''),
      '#description' => $this->t('¡Solicitud enviada con éxito!'),
    ];

    $form[$grop_fields]['cardConfirmation']['fields']['zendeskTitle']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['zendeskTitle']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardConfirmation']['fields']['zendeskDesc']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Descripción Zendesk'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['zendeskDesc']['label'], NULL, ''),
    ];

    $form[$grop_fields]['cardDetail'] = [
      '#type' => 'details',
      '#title' => $this->t('Detalle'),
    ];

    $form[$grop_fields]['cardDetail']['fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('show'),
        t('config'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['cardDetail']['fields'], NULL, []);

    $form[$grop_fields]['cardDetail']['fields']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['label'], NULL, ''),
      '#description' => $this->t('Detalles de la transaccion'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['title']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['plan']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Detalles del plan'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['plan']['label'], NULL, ''),
      '#description' => $this->t('Detalles del plan'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['plan']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['plan']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['account']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cuenta'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['account']['label'], NULL, ''),
      '#description' => $this->t('Cuenta'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['account']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['account']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['price']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Precio'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['price']['label'], NULL, ''),
      '#description' => $this->t('Precio'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['price']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['price']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['activateDate']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Date'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['activateDate']['label'], NULL, ''),
      '#description' => $this->t('Fecha de activación'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['activateDate']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['activateDate']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['activateDate']['formatDate'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de fecha'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['activateDate']['formatDate'], NULL, 'short'
      ),
      '#options' => $utils->getDateFormats(),
    ];

    $form[$grop_fields]['cardDetail']['fields']['footer']['label'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Texto Informativo'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['footer']['label'], NULL, ''),
      '#description' => $this->t('Footer del card de detalles. Ej: * Precio incluye IVA ...'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['footer']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['footer']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['installationDate']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fecha de instalación'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['installationDate']['label'], NULL, ''),
      '#description' => $this->t('Etiqueta para fecha de instalación'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['installationDate']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['installationDate']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['installationDate']['formatDate'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de fecha'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['installationDate']['formatDate'], NULL, 'short'
      ),
      '#options' => $utils->getDateFormats(),
    ];

    $form[$grop_fields]['cardDetail']['fields']['installationTime']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hora de instalación'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['installationTime']['label'], NULL, ''),
      '#description' => $this->t('Etiqueta para hora de instalación'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['installationTime']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['installationTime']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['appointmentId']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nº de orden de instalación'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['appointmentId']['label'], NULL, ''),
      '#description' => $this->t('Etiqueta para id de orden de instalación'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['appointmentId']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['appointmentId']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['installationNotice']['label'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Aviso de instalación'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['installationNotice']['label'], NULL, ''),
      '#description' => $this->t('Footer de datos de instalación'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['installationNotice']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['installationNotice']['show'], NULL, 0),
    ];

    $form[$grop_fields]['error'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensaje de Error'),
    ];

    $form[$grop_fields]['error']['fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['error']['fields'], NULL, []);

    $form[$grop_fields]['error']['fields']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['label'], NULL, ''),
      '#description' => $this->t('Servicio a fallado'),
    ];

    $form[$grop_fields]['error']['fields']['title']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['error']['fields']['desc']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Descripción'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['desc']['label'], NULL, ''),
      '#description' => $this->t('El proceso a fallado'),
    ];


    $form[$grop_fields]['actions'] = [
      '#type' => 'details',
      '#title' => $this->t('Actions'),
    ];

    $form[$grop_fields]['actions']['card'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Type'),
        t('Url'),
        t('Show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['actions']['card'], NULL, []);

    $form[$grop_fields]['actions']['card']['seeProducts']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ver mas Productos'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeProducts']['label'], NULL, ''),
      '#description' => $this->t('Botón Ver mas Productos'),
    ];

    $form[$grop_fields]['actions']['card']['seeProducts']['type'] = [
      '#type' => 'select',
      '#options' => $action_type,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeProducts']['type'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['seeProducts']['url']['oneapp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oneapp'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeProducts']['url']['oneapp'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['seeProducts']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeProducts']['url']['selfcare'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['seeProducts']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeProducts']['show'], NULL, 0),
    ];

    $form[$grop_fields]['actions']['card']['seeDetail']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ver Detalles'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeDetail']['label'], NULL, ''),
      '#description' => $this->t('Link Ver Detalles'),
    ];

    $form[$grop_fields]['actions']['card']['seeDetail']['type'] = [
      '#type' => 'select',
      '#options' => $action_type,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeDetail']['type'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['seeDetail']['url'] = [
      '#type' => 'textfield',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeDetail']['url'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['seeDetail']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['seeDetail']['show'], NULL, 0),
    ];

    $form[$grop_fields]['actions']['card']['error']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Volver al inicio'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['error']['label'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['error']['type'] = [
      '#type' => 'select',
      '#options' => $action_type,
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['error']['type'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['error']['url']['oneapp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oneapp'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['error']['url']['oneapp'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['error']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['error']['url']['selfcare'], NULL, ''),
    ];

    $form[$grop_fields]['actions']['card']['error']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['error']['show'], NULL, 0),
    ];

    // Configs Zendesk.
    $grop_fields = 'zendesk';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones Zendesk'),
      '#open' => FALSE,
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields], NULL, []);

    $form[$grop_fields]['subject'] = [
      '#type' => 'textfield',
      '#title' => 'Subject',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['subject'], NULL, ''),
      '#description' => 'Use @plan para indicar el nombre del plan a cambiar',
    ];
    $form[$grop_fields]['tags'] = [
      '#type' => 'textfield',
      '#title' => 'tags',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['tags'], NULL, ''),
      '#description' => 'Separar por coma',
    ];
    $form[$grop_fields]['brand_id'] = [
      '#type' => 'textfield',
      '#title' => 'Brand id',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['brand_id'], NULL, ''),
    ];
    $form[$grop_fields]['ticket_form_id'] = [
      '#type' => 'textfield',
      '#title' => 'Ticket form id',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['ticket_form_id'], NULL, ''),
    ];
    $form[$grop_fields]['custom_fields'] = [
      '#type' => 'textfield',
      '#title' => 'Cantidad de custom fields',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['custom_fields'], NULL, 3),
    ];

    $custom_fields = intval($upgrade_utils_service->getFieldConfigValue($config_actions['custom_fields'], NULL, 3));

    $custom_fields_type = [
      'home' => 'Campos custom Home',
    ];

    $form[$grop_fields]['fields'] = [
      '#type' => 'details',
      '#title' => 'Campos custom',
      '#open' => FALSE,
      '#description' => 'los valores que vienen del callback deben ir entre {}',
    ];

    foreach ($custom_fields_type as $key => $title) {
      $form[$grop_fields]['fields'][$key] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => FALSE,
      ];
    }

    for ($i = 1; $i <= $custom_fields; ++$i) {
      foreach ($custom_fields_type as $key => $title) {
        $form[$grop_fields]['fields'][$key][$i]['id'] = [
          '#type' => 'textfield',
          '#title' => 'Id para el campo #' . $i,
          '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['fields'][$key][$i]['id'], NULL, ''),
        ];
        $form[$grop_fields]['fields'][$key][$i]['value'] = [
          '#type' => 'textfield',
          '#title' => 'Value para el campo #' . $i,
          '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['fields'][$key][$i]['value'], NULL, ''),
        ];
      }
    }

    $grop_fields = 'emailSetting';

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields], NULL, []);

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion de Correo'),
      '#open' => FALSE,
    ];

    $form[$grop_fields]['config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('E-mail options'),
      '#open' => TRUE,
      '#weight' => 0,
      '#collapsible' => TRUE,
    ];

    $form[$grop_fields]['config']['from'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Dirección de correo electrónico'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['config']['from'], NULL, ''),
      '#description' => $this->t('Dirección de correo electrónico desde donde se envia el correo.'),
    ];

    $form[$grop_fields]['config']['fromname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del Remitente'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['config']['fromname'], NULL, ''),
      '#description' => $this->t('Nombre de quien envia los correos.'),
    ];

    $form[$grop_fields]['config']['cc_to'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enviar copia a'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['config']['cc_to'], NULL, ''),
      '#description' => $this->t('Cuenta de correo para enviar copia.'),
    ];

    $form[$grop_fields]['single'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Correo'),
      '#open' => TRUE,
      '#weight' => 0,
      '#collapsible' => TRUE,
      '#description' => $this->t('Tokens: <br> [oneapp_upgrade:userName] <br> [oneapp_upgrade:newPlan]'),
    ];

    $form[$grop_fields]['single']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Asunto"),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['single']['subject'], NULL, ''),
      '#maxlength' => 128,
    ];

    $form[$grop_fields]['single']['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Cuerpo'),
      '#format' => 'full_html',
      '#default_value' =>
      $upgrade_utils_service->getFieldConfigValue($config_actions['single']['body']['value'], NULL, ''),
    ];

    // CONFIG GENERAL
    $grop_fields = 'generalConfig';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion General'),
      '#open' => FALSE,
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields], NULL, []);

    $form[$grop_fields]['decimal_numbers'] = [
      '#type' => 'select',
      '#title' => $this->t('Decimales'),
      '#description' => $this->t('Número máximo de decimales a mostrar para información de moneda.'),
      '#default_value' =>
      $upgrade_utils_service->getFieldConfigValue($config_actions['decimal_numbers'], NULL, 2),
      '#options' => [0, 1, 2],
      '#required' => TRUE,
    ];

    $options = [
      'none' => $this->t('None'),
      'desc' => $this->t('Desc'),
      'asc' => $this->t('Asc'),
    ];

    $form[$grop_fields]['orderPlansByAmount'] = [
      '#type' => 'select',
      '#title' => $this->t('Ordenar Planes por Monto'),
      '#description' => $this->t('Ordenar Planes por Monto Desc/Asc.'),
      '#default_value' =>
        $upgrade_utils_service->getFieldConfigValue($config_actions['orderPlansByAmount'], NULL, 'none'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form[$grop_fields]['orderPlansByFeatured'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ubicar plan estrella de primero'),
      '#description' => $this->t('Ordena el plan estrella de primero entre los planes recomendados.'),
      '#default_value' =>
        $upgrade_utils_service->getFieldConfigValue($config_actions['orderPlansByFeatured'], NULL, 0),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $upgrade_utils_service = $this->upgradePlanUtils ?? \Drupal::service('oneapp_convergent_upgrade_plan.utils');
    $this->configuration['upgradePlan'] = $form_state->getValue('upgradePlan');
    $get_value_image = ['upgradePlan', 'fields', 'banner'];
    $image_banner = $form_state->getValue($get_value_image);
    if ($image_banner) {
      $upgrade_utils_service->setFileAsPermanent($image_banner['url']);
    }
    $this->configuration['recommendedOffers'] = $form_state->getValue('recommendedOffers');
    $this->configuration['confirmationUpgradePlan'] = $form_state->getValue('confirmationUpgradePlan');
    $this->configuration['zendesk'] = $form_state->getValue('zendesk');
    $this->configuration['emailSetting'] = $form_state->getValue('emailSetting');
    $this->configuration['generalConfig'] = $form_state->getValue('generalConfig');
  }

  /**
   * Method to save file permanently in the database.
   *
   * @param string $fid
   *   File id.
   */
  public function setFileAsPermanent($fid) {
    if (is_array($fid)) {
      $fid = array_shift($fid);
    }
    $file = File::load($fid);

    if (!is_object($file)) {
      return;
    }

    $file->setPermanent();
    $file->save();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#cache']['max-age'] = 0;
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldSubmit(array &$form, FormStateInterface $form_state) {
    return $form['settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function addContainerCallback(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('count') + 1;
    $form_state->set('count', $count);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function removeContainerCallback(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('count');
    if ($count > 0) {
      $count = $count - 1;
      $form_state->set('count', $count);
      if ($count == 0) {
        $form_state->set('remove', TRUE);
      }
    }
    $form_state->setRebuild();
  }

}
