<?php

namespace Drupal\oneapp_mobile_plans_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Drupal\adf_block_config\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'IcloudPromoBlock' block.
 *
 * @Block(
 *  id = "oneapp_mobile_plans_gt_v2_0_icloud_promo_block",
 *  admin_label = @Translation("iCloud Promo Config Block"),
 * )
 */
class IcloudPromoBlock extends BlockBase {

  /**
   * Fields.
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
   * Default Configs.
   *
   * @var
   */
  protected $defaultConfig;

  /**
   * @var \Drupal\oneapp_mobile_plans\Services\CurrentPlanServices
   */
  protected $mobilePlanService;

  /**
   * @var \Drupal\oneapp_mobile\Services\UtilsService
   */
  protected $mobileUtils;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->mobileUtils = $container->get('oneapp.mobile.utils');
    $instance->mobilePlanService = $container->get('oneapp_mobile_plans.current_plan_services');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $this->defaultConfig = [
      'data' => [
        'subtitle' => [
          'type' => 'textfield',
          'label' => $this->t('Subtítulo del card'),
          'description' => $this->t('Valor del subtítulo'),
          'value' => '3 Meses de almacenamiento en iCloud gratis!',
          'show' => TRUE,
        ],
        'image' => [
          'type' => 'managed_file',
          'label' => $this->t('Imagen/Banner del card'),
          'description' => NULL,
          'url' => NULL,
          'show' => TRUE,
        ],
      ],
      'actions' => [
        'see_more' => [
          'type' => 'link',
          'label' => $this->t('Más información'),
          'url' => '/',
          'description' => $this->t('Enlace para ver más información'),
          'show' => TRUE,
        ],
        'purchase' => [
          'type' => 'button',
          'label' => $this->t('Comprar'),
          'url' => '/',
          'description' => $this->t('Enlace para comprar'),
          'show' => TRUE,
        ],
      ]

    ];
    return $this->defaultConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockForm(array $form, FormStateInterface $form_state) {

    /**
     * Data
     */
    $form['data_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Otros datos del card'),
      '#open' => FALSE
    ];

    $form['data_details']['data']['#type'] = 'table';
    $headers = ['item' => t('Ítem'), 'show' => t('Show') ];
    $data = $this->configuration['data'] ?? $this->defaultConfig['data'];

    foreach ($data as $key => $field) {
      if ($field['type'] != 'managed_file') {
        $form['data_details']['data'][$key]['value'] = [
          '#title' => $field['label'],
          '#type' => $field['type'],
          '#default_value' => $field['value'],
          '#description' => $field['description']
        ];
      }
      else {
        $form['data_details']['data'][$key]['url'] = [
          '#title' => $field['label'],
          '#type' => $field['type'],
          '#default_value' => $field['url'],
          '#description' => $field['description'],
          '#upload_location' => 'public://' . $this->mobilePlanService::DIRECTORY_IMAGES,
          '#upload_validators' => ['file_validate_extensions' => ['png jpg svg']]
        ];
      }
      $form['data_details']['data'][$key]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => !empty($field['show']),
      ];
    }

    $form['data_details']['data']['#header'] = array_values($headers);

    /**
     * Actions
     */
    $form['actions_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Actions'),
      '#open' => FALSE
    ];

    $form['actions_details']['actions'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Acción/Etiqueta'),
        $this->t('Url'),
        $this->t('Show'),
      ]
    ];

    $actions = isset($this->configuration['actions']) ? $this->configuration['actions'] : $this->defaultConfig['actions'];

    foreach ($actions as $key => $action) {
      $form['actions_details']['actions'][$key]['label'] = [
        '#title' => $this->defaultConfig['actions'][$key]['label'],
        '#type' => 'textfield',
        '#default_value' => $action['label'],
        '#description' => $action['description'],
      ];
      $form['actions_details']['actions'][$key]['url'] = [
        '#type' => 'textfield',
        '#default_value' => $action['url'],
      ];
      $form['actions_details']['actions'][$key]['show'] = [
        '#type' => 'checkbox',
        '#default_value' => !empty($action['show']),
      ];
    }


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function adfBlockSubmit(array $form, FormStateInterface $form_state) {
    $this->configuration['data'] = $form_state->getValue(['data_details', 'data']);
    $this->configuration['actions'] = $form_state->getValue(['actions_details', 'actions']);
    foreach ($this->configuration['data'] as $data) {
      if (!empty($data['url'])) {
        $this->mobileUtils->setFileAsPermanent($data['url'][0]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#cache']['max-age'] = 0;
    return [];
  }

}
