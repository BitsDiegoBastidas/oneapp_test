<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\oneapp_convergent_upgrade_plan\Plugin\Block\v2_0\UpgradeMobileBlock;

class UpgradeMobileGtBlock extends UpgradeMobileBlock {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->defaultConfig = [
      "validationPlanMobile" => [
        "validationsPlansCodes" => "07,2000",
        "showMessagesCodes" => "02",
      ],
      "upgradePlanMobile" => [
        "fields" => [
          "title" => [
            "value" => "Un nuevo plan te está esperando",
            "show" => 1,
          ],
          "banner" => [
            "url" => [
              0 => "",
              1 => "",
            ],
            "show" => 1,
          ],
          "description" => [
            "value" => "Llamadas y mensajes ilimitados al mejor precio!",
          ],
        ],
        "actions" => [
          "card" => [
            "planUpgradeAction" => [
              "label" => "Mejora tu plan",
              "type" => "button",
              "url" => [
                "oneapp" => "URL Oneapp",
                "selfcare" => "url Selfcare",
              ],
              "show" => 1,
            ],
          ],
        ],
      ],
      "recommendedOffersMobile" => [
        "fields" => [
          "static" => [
            "plan" => [
              "label" => "Teléfono",
              "show" => 1,
            ],
            "iva" => [
              "label" => "Tarifa básica",
              "show" => 1,
            ],
            "mbpsFormatted" => [
              "value" => "Mb",
              "method" => 1,
            ],
          ],
          "dynamic" => [
            "products" => [
              "data0" => [
                "key" => "Internet",
                "label" => "Internet",
                "format" => "mbps",
                "class" => "net",
                "show" => "1",
                "icon" => 0,
              ],
            ],
            "addMobile" => "Agregar una dato adicional",
            "removeMobile" => "Eliminar un dato adicional",
          ],
        ],
        "verification" => [
          "fields" => [
            "title" => [
              "label" => "Revisa y confirma tu Solicitud",
              "show" => "1",
            ],
            "plan" => [
              "label" => "Tu nuevo Plan",
              "show" => "1",
              "description" => "Teléfono",
            ],
            "terms" => [
              "label" => "Términos & Condiciones",
              "show" => "1",
              "url" => "#",
            ],
            "termsDesc" => [
              "value" => "Al presionar ACEPTAR comenzaras a disfrutar tu plan y estarás aceptando los @termsConditions.",
            ],
            "termsModal" => [ ],
          ],
        ],
        "actions" => [
          "card" => [
            "recommendedOffersAction" => [
              "label" => "Mejora Ya",
              "type" => "button",
              "url" => [
                "oneapp" => "Oneapp",
                "selfcare" => "Selfcare",
              ],
              "show" => 1,
            ],
            "verificationActionAccept" => [
              "label" => "CONFIRMAR",
              "type" => "button",
              "url" => [
                "oneapp" => "Oneapp",
                "selfcare" => "Selfcare",
              ],
              "show" => 1,
            ],
            "verificationActionCancel" => [
              "label" => "CANCELAR",
              "type" => "button",
              "url" => [
                "oneapp" => "Oneapp",
                "selfcare" => "Selfcare",
              ],
              "show" => 1,
            ],
          ],
        ],
      ],
      "confirmationUpgradePlanMobile" => [
        "fieldErrorMsg" => [
          "identificationType" => 'El campo documento es requerido.',
          "identificationNumber" => 'El campo número de documento es requerido.',
        ],
        "cardConfirmation" => [
          "fields" => [
            "title" => [
              "label" => "¡Tu solicitud ha sido exitosa!",
              "show" => 1,
            ],
            "desc" => [
              "label" => "Se ha enviado un comprobante a",
              "value" => "",
            ],
          ],
        ],
        "cardDetail" => [
          "fields" => [
            "title" => [
              "label" => "Detalles de la transacción",
              "show" => "1",
            ],
            "plan" => [
              "label" => "Detalles del plan",
              "show" => "1",
            ],
            "account" => [
              "label" => "Cuenta",
              "show" => "1",
            ],
            "price" => [
              "label" => "Precio",
              "show" => "1",
            ],
            "activateDate" => [
              "label" => "Fecha de activación",
              "show" => "1",
              "formatDate" => "short",
            ],
            "changePlan" => [
              "inmediate" => "*Tu nuevo plan estará activo dentro de las próximas 24 horas.",
              "show" => 0,
              "notInmediate" => "*Tu nuevo plan estará activo desde el 1ro del mes siguiente.",
            ],
          ],
        ],
        "error" => [
          "fields" => [
            "title" => [
              "label" => "Actualización fallida.",
              "show" => 1,
            ],
            "desc" => [
              "label" => "¡La actualización al nuevo plan no ha sido realizada!.",
              "value" => "",
            ],
          ],
        ],
        "actions" => [
          "card" => [
            "seeProducts" => [
              "label" => "Ver mas Productos",
              "type" => "button",
              "url" => [
                "oneapp" => "Oneapp",
                "selfcare" => "Selfcare",
              ],
              "show" => "1",
            ],
            "seeDetail" => [
              "label" => "Ver Detalles",
              "type" => "button",
              "url" => "verDetalles",
              "show" => "1",
            ],
            "error" => [
              "label" => "Volver al inicio",
              "type" => "button",
              "url" => [
                "oneapp" => "Oneapp",
                "selfcare" => "Selfcare",
              ],
              "show" => "1",
            ],
          ],
        ],
      ],
      "emailSetting" => [
        "config" => [
          "from" => "tigo@id.tigo.com",
          "fromname" => "Tigo Bolivia - STG",
        ],
        "single" => [
          "subject" => "Solicitud Mejora de Plan",
          "body" => [
            "value" => "",
          ],
        ],
        "error" => [
          "subject" => "Solicitud Mejora de Plan",
          "body" => [
            "value" => "",
          ],
        ],
      ],
      "messages" => [
        'message_plan' => "Esta cuenta tiene un cambio de plan programado",
      ]
      ,
      "generalConfig" => [
        "decimal_numbers" => 2,
        "documentVerificationType" => "agent",
        "enableDocumentUpdateDAR" => 0,
        'orderPlansByAmount' => 'none',
        'orderPlansByFeatured' => 0,
        'activateIva' => 0,
        'iva' => '',
        'activateSufixInternet' => 0,
        'suffix' => '',
      ],
    ];

    if (!empty($this->adfDefaultConfiguration())) {
      return $this->adfDefaultConfiguration();
    } else {
      return $this->defaultConfig;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {

    $upgrade_utils_service = $this->upgradePlanUtils;
    $utils = $this->oneappUtils;

    $action_type = [
      'button' => $this->t('Boton'),
      'link' => $this->t('Link'),
    ];

    $form = [
      '#prefix' => '<div id="container-fields-wrapper">',
      '#suffix' => '</div>',
    ];

    // Fields - CARD MEJORA TU PLAN MOBILE.
    $grop_fields = 'upgradePlanMobile';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Card Mejora tu Plan - Mobile Paso 1'),
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
      '#upload_location' => 'public://' . $upgrade_utils_service::DIRECTORY_IMAGES_MOBILE,
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

    // Fields static - CARD PLANES RECOMENDADOS MOBILE.
    $grop_fields = 'recommendedOffersMobile';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Card Planes Recomendados - Mobile Paso 2'),
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

    $form[$grop_fields]['fields']['static']['mbpsFormatted']['method'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Usar metodo formatData()'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['mbpsFormatted']['method'], NULL, 0),
      '#description' => $this->t('Si se activa esta opcion el formato se visualizara dependiendo de la unidad que viene en la API
        <br> Ejemplo 10000 Kb -> 10 Mb'),
    ];

    // Fields dynamic - CARD PLANES RECOMENDADOS MOBILE.
    $form[$grop_fields]['fields']['dynamic'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion de Productos - Propiedades de los planes'),
      '#open' => TRUE,
      '#description' => $this->t('El Formato usar para: <br>mbps (Internet), currency (Price)'),
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

    $remove = ($form_state->get('removeMobile') != NULL) ? $form_state->get('removeMobile') : FALSE;
    $count = ($form_state->get('countMobile') != NULL) ? $form_state->get('countMobile') : 0;
    $config_actions = $upgrade_utils_service->getFieldConfigValue(
      $this->configuration[$grop_fields]['fields']['dynamic']['products'],
      NULL,
      []
    );
    if (count($config_actions) < $count) {
      $config_actions["data{$count}"] = [];
      $form_state->set('countMobile', $count);
    } else {
      if ($remove) {
        $count = 0;
        $form_state->set('countMobile', 0);
      } elseif ($count != NULL) {
        $form_state->set('countMobile', $count);
        $count = $count;
      } else {
        $form_state->set('countMobile', count($config_actions));
        $count = count($config_actions);
      }
    }

    for ($i = 0; $i < $count; $i++) {
      $product_id = "data{$i}";
      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['key'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['key'], NULL, ''),
        '#description' => $this->t(''),
        '#size' => 25,
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['label'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['label'], NULL, ''),
        '#description' => $this->t(''),
        '#size' => 25,
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['format'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['format'], NULL, ''),
        '#description' => $this->t(''),
        '#size' => 25,
      ];

      $form[$grop_fields]['fields']['dynamic']['products'][$product_id]['class'] = [
        '#type' => 'textfield',
        '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions[$product_id]['class'], NULL, ''),
        '#description' => $this->t(''),
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

    $form[$grop_fields]['fields']['dynamic']['addMobile'] = [
      '#type' => 'submit',
      '#value' => t('Agregar una dato adicional'),
      '#submit' => [
        [$this, 'addContainerMobileCallback'],
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
      $form[$grop_fields]['fields']['dynamic']['removeMobile'] = [
        '#type' => 'submit',
        '#value' => t('Eliminar un dato adicional'),
        '#submit' => [
          [$this, 'removeContainerMobileCallback'],
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
        '',
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

    $form[$grop_fields]['verification']['fields']['date']['formatDate'] = [
      '#type' => 'select',
      '#title' => $this->t('Formato de fecha'),
      '#description' => $this->t('Seleccione el formato en que se mostraran las fechas por defecto'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['date']['formatDate'], NULL, 'short'
      ),
      '#options' => $utils->getDateFormats(),
    ];

    $form[$grop_fields]['verification']['fields']['plan']['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['plan']['description'], NULL, ''),
      '#description' => $this->t('Label Description en Tu nuevo plan.'),
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

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields]['verification'], NULL, []);

    // Action - CARD PLANES RECOMENDADOS MOBILE.
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
        $config_actions['recommendedOffersAction']['url']['oneapp'],
        NULL,
        ''
      ),
    ];

    $form[$grop_fields]['actions']['card']['recommendedOffersAction']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['recommendedOffersAction']['url']['selfcare'],
        NULL,
        ''
      ),
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
        $config_actions['verificationActionAccept']['url']['oneapp'],
        NULL,
        ''
      ),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionAccept']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['verificationActionAccept']['url']['selfcare'],
        NULL,
        ''
      ),
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
        $config_actions['verificationActionCancel']['url']['oneapp'],
        NULL,
        ''
      ),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionCancel']['url']['selfcare'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Selfcare'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['verificationActionCancel']['url']['selfcare'],
        NULL,
        ''
      ),
    ];

    $form[$grop_fields]['actions']['card']['verificationActionCancel']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['verificationActionCancel']['show'], NULL, 0),
    ];

    // Confirmation upgrade Plan Mobile.
    $grop_fields = 'confirmationUpgradePlanMobile';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Card Confirmacion Upgrade Plan - Mobile Paso 3'),
      '#open' => FALSE,
    ];

    $form[$grop_fields]['fieldErrorMsg'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensaje de validacion de campos requeridos'),
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue(
      $this->configuration[$grop_fields]['fieldErrorMsg'],
      NULL,
      []
    );

    $form[$grop_fields]['fieldErrorMsg']['identificationType'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tipo de Documento'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['identificationType'], NULL, ''),
      '#description' => $this->t('En caso de que no indiquen el tipo de documento.'),
    ];

    $form[$grop_fields]['fieldErrorMsg']['identificationNumber'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Número de Documento'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['identificationNumber'], NULL, ''),
      '#description' => $this->t('En caso de que no indiquen el número de documento.'),
    ];

    $form[$grop_fields]['cardConfirmation'] = [
      '#type' => 'details',
      '#title' => $this->t('Confirmacion Exitosa'),
    ];

    $form[$grop_fields]['cardConfirmation']['fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Value'),
        t('show'),
        '',
      ],
    ];

    $config_actions = $upgrade_utils_service->getFieldConfigValue(
      $this->configuration[$grop_fields]['cardConfirmation']['fields'],
      NULL,
      []
    );

    $form[$grop_fields]['cardConfirmation']['fields']['title']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['label'], NULL, ''),
      '#description' => $this->t('¡Tu solicitud ha sido exitosa!'),
    ];
    $form[$grop_fields]['cardConfirmation']['fields']['title']['value'] = [
      '#type' => 'hidden',
    ];
    $form[$grop_fields]['cardConfirmation']['fields']['title']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardConfirmation']['fields']['desc']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtitle'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['desc']['label'], NULL, ''),
      '#description' => $this->t('Se ha enviado un comprobante a'),
    ];
    $form[$grop_fields]['cardConfirmation']['fields']['desc']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Descripción'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['desc']['value'], NULL, ''),
      '#description' => $this->t(''),
    ];

    $form[$grop_fields]['cardDetail'] = [
      '#type' => 'details',
      '#title' => $this->t('Detalle para actualización exitosa'),
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
      '#default_value' => $upgrade_utils_service->getFieldConfigValue(
        $config_actions['activateDate']['formatDate'],
        NULL,
        'short'
      ),
      '#options' => $utils->getDateFormats(),
    ];

    $form[$grop_fields]['cardDetail']['fields']['changePlan']['inmediate'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Texto cambio de plan inmediato'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['changePlan']['inmediate'], NULL, ''),
      '#description' => $this->t('* Tu nuevo plan estará activo dentro de las próximas 24 horas.'),
    ];

    $form[$grop_fields]['cardDetail']['fields']['changePlan']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['changePlan']['show'], NULL, 0),
    ];

    $form[$grop_fields]['cardDetail']['fields']['changePlan']['notInmediate'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Texto cambio de plan próximo ciclo'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['changePlan']['notInmediate'], NULL, ''),
      '#description' => $this->t('* Tu nuevo plan estará activo desde el 1ro del mes siguiente.'),
    ];

    $form[$grop_fields]['error'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensaje de Error para actualización fallida'),
    ];

    $form[$grop_fields]['error']['fields'] = [
      '#type' => 'table',
      '#header' => [
        t('Field'),
        t('Value'),
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
    $form[$grop_fields]['error']['fields']['title']['value'] = [
      '#type' => 'hidden',
    ];
    $form[$grop_fields]['error']['fields']['title']['show'] = [
      '#type' => 'checkbox',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['title']['show'], NULL, 0),
    ];

    $form[$grop_fields]['error']['fields']['desc']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtitle'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['desc']['label'], NULL, ''),
      '#description' => $this->t('El proceso a fallado'),
    ];
    $form[$grop_fields]['error']['fields']['desc']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Descripción'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['desc']['value'], NULL, ''),
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

    // CONFIG EMAIL
    $grop_fields = 'emailSetting';

    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields], NULL, []);

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Configuracion de Correo'),
      '#open' => FALSE,
    ];

    $form[$grop_fields]['config'] = [
      '#type' => 'details',
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

    $form[$grop_fields]['single'] = [
      '#type' => 'details',
      '#title' => $this->t('Correo en caso de éxito'),
      '#open' => FALSE,
      '#weight' => 0,
      '#collapsible' => TRUE,
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
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['single']['body']['value'], NULL, ''),
      '#description' => $this->t('<br><strong>Tokens:</strong><br>
        [oneapp_upgrade:userName] <br>
        [oneapp_upgrade:newPlan] <br>
        [oneapp_upgrade:textChangePlan] <br><br>
        <strong>Valores Detalle de Confirmación</strong><br>
        [oneapp_upgrade:textConfirmationTitle] <br>
        [oneapp_upgrade:textPlanLabel] <br>
        [oneapp_upgrade:textPlanValue] <br>
        [oneapp_upgrade:textPlanFormatted] <br>
        [oneapp_upgrade:textAccountLabel] <br>
        [oneapp_upgrade:textAccountValue] <br>
        [oneapp_upgrade:textPriceLabel] <br>
        [oneapp_upgrade:textPriceValue] <br>
        [oneapp_upgrade:textActivateDateLabel] <br>
        [oneapp_upgrade:textActivateDateValue] <br>
        '),
    ];

    $form[$grop_fields]['error'] = [
      '#type' => 'details',
      '#title' => $this->t('Correo en caso de error'),
      '#open' => FALSE,
      '#weight' => 0,
      '#collapsible' => TRUE,
    ];

    $form[$grop_fields]['error']['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Asunto"),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['error']['subject'], NULL, ''),
      '#maxlength' => 128,
    ];

    $form[$grop_fields]['error']['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Cuerpo'),
      '#format' => 'full_html',
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['error']['body']['value'], NULL, ''),
      '#description' => $this->t('<br><strong>Tokens:</strong><br>
        [oneapp_upgrade:userName] <br>
        [oneapp_upgrade:newPlan] <br>
        [oneapp_upgrade:textChangePlan] <br><br>
        <strong>Valores Detalle de Confirmación</strong><br>
        [oneapp_upgrade:textConfirmationTitle] <br>
        [oneapp_upgrade:textPlanLabel] <br>
        [oneapp_upgrade:textPlanValue] <br>
        [oneapp_upgrade:textPlanFormatted] <br>
        [oneapp_upgrade:textAccountLabel] <br>
        [oneapp_upgrade:textAccountValue] <br>
        '),
    ];

    // Mensajes
    $grop_fields = 'messages';

    $form[$grop_fields] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => FALSE,
    ];
    $config_actions = $upgrade_utils_service->getFieldConfigValue($this->configuration[$grop_fields], NULL, []);
    $form[$grop_fields]['message_plan'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para plan contratado'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['message_plan'], NULL, ''),
      '#description' => $this->t('Mensaje de error para cuando exista plan contratado.'),
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

    $form[$grop_fields]['enableDocumentUpdateDAR'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Activar documentVerified en el DAR"),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['enableDocumentUpdateDAR'], NULL, 0),
      '#description' => $this->t('Si esta desactivado el valor del documentVerified = FALSE, si se activa dependera del getSecLogin'),
    ];


    $form[$grop_fields]['documentVerificationType'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Valor del documentVerificationType en el DAR"),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['documentVerificationType'], NULL, ''),
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

    //Configuracion de iva

    $form[$grop_fields]['activateIva'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Activar Iva'),
      '#description' => $this->t('Activa el uso del iva'),
      '#default_value' =>
        $upgrade_utils_service->getFieldConfigValue($config_actions['activateIva'], NULL, 0),
    ];
    $form[$grop_fields]['iva'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IVA'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['iva'], NULL, ''),
      '#description' => $this->t('Impuesto.'),
    ];

    //Configuracion de sufijo para Internet

    $form[$grop_fields]['activateSufixInternet'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Activa el sufijo de Internet'),
      '#description' => $this->t('Activa que se muestre el sufijo para las ofertas de Internet'),
      '#default_value' =>
        $upgrade_utils_service->getFieldConfigValue($config_actions['activateSufixInternet'], NULL, 0),
    ];

    $form[$grop_fields]['suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sufijo'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['suffix'], NULL, ''),
      '#description' => $this->t('Sufijo para producto Internet.'),
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

    $form[$grop_fields]['enableDocumentUpdateDAR'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Activar documentVerified en el DAR"),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['enableDocumentUpdateDAR'], NULL, 0),
      '#description' => $this->t('Si esta desactivado el valor del documentVerified = FALSE, si se activa dependera del getSecLogin'),
    ];


    $form[$grop_fields]['documentVerificationType'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Valor del documentVerificationType en el DAR"),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['documentVerificationType'], NULL, ''),
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

    //Configuracion de iva

    $form[$grop_fields]['activateIva'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Activar Iva'),
      '#description' => $this->t('Activa el uso del iva'),
      '#default_value' =>
      $upgrade_utils_service->getFieldConfigValue($config_actions['activateIva'], NULL, 0),
    ];
    $form[$grop_fields]['iva'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IVA'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['iva'], NULL, ''),
      '#description' => $this->t('Impuesto.'),
    ];

    //Configuracion de sufijo para Internet

    $form[$grop_fields]['activateSufixInternet'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Activa el sufijo de Internet'),
      '#description' => $this->t('Activa que se muestre el sufijo para las ofertas de Internet'),
      '#default_value' =>
      $upgrade_utils_service->getFieldConfigValue($config_actions['activateSufixInternet'], NULL, 0),
    ];

    $form[$grop_fields]['suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sufijo'),
      '#default_value' => $upgrade_utils_service->getFieldConfigValue($config_actions['suffix'], NULL, ''),
      '#description' => $this->t('Sufijo para producto Internet.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    $upgrade_utils_service = $this->upgradePlanUtils;
    $this->configuration['validationPlanMobile'] = $form_state->getValue('validationPlanMobile');
    $this->configuration['recommendedOffersMobile'] = $form_state->getValue('recommendedOffersMobile');
    $this->configuration['upgradePlanMobile'] = $form_state->getValue('upgradePlanMobile');
    $this->configuration['confirmationUpgradePlanMobile'] = $form_state->getValue('confirmationUpgradePlanMobile');
    $this->configuration['emailSetting'] = $form_state->getValue('emailSetting');
    $this->configuration['messages'] = $form_state->getValue('messages');
    $this->configuration['generalConfig'] = $form_state->getValue('generalConfig');
    $get_value_image = ['upgradePlanMobile', 'fields', 'banner'];
    $image_banner = $form_state->getValue($get_value_image);
    if (isset($image_banner['url']) && $image_banner['url'] != []) {
      $upgrade_utils_service->setFileAsPermanent($image_banner['url']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $build['#cache']['max-age'] = 0;
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldSubmit(array &$form, FormStateInterface $form_state)
  {
    return $form['settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function addContainerMobileCallback(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('countMobile') + 1;
    $form_state->set('countMobile', $count);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function removeContainerMobileCallback(array &$form, FormStateInterface $form_state)
  {
    $count = $form_state->get('countMobile');
    if ($count > 0) {
      $count = $count - 1;
      $form_state->set('countMobile', $count);
      if ($count == 0) {
        $form_state->set('removeMobile', TRUE);
      }
    }
    $form_state->setRebuild();
  }
}
