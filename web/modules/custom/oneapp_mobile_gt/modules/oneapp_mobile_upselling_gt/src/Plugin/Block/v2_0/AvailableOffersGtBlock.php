<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Block\v2_0;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oneapp_mobile_upselling\Plugin\Block\v2_0\AvailableOffersBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;


class AvailableOffersGtBlock extends AvailableOffersBlock {
  /**
   * List fields history default configuration.
   *
   * @var mixed
   */
  protected $contentFields;

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
    $this->contentFields = [
      'offersList' => [
        'fields' => [
          'offerId' => [
            'title' => $this->t('Oferta'),
            'label' => '',
            'show' => 0,
            'weight' => 0,
          ],
          'offerName' => [
            'title' => $this->t('Nombre:'),
            'label' => $this->t('Nombre'),
            'show' => 1,
            'weight' => 1,
          ],
          'description' => [
            'title' => $this->t('Description:'),
            'label' => $this->t('Description'),
            'show' => 1,
            'weight' => 2,
          ],
          'tags' => [
            'title' => $this->t('Etiqueta:'),
            'label' => '',
            'show' => 0,
            'weight' => 3,
          ],
          'validity' => [
            'title' => $this->t('Vigencia:'),
            'label' => $this->t('Vigencia'),
            'show' => 1,
            'weight' => 4,
          ],
          'validityPostpaid' => [
            'title' => $this->t('Vigencia (Postpaid):'),
            'label' => $this->t('Vigencia Hasta el @date o se cumpla el límite de navegación'),
            'show' => 1,
            'weight' => 4,
          ],
          'price' => [
            'title' => $this->t('Precio:'),
            'label' => $this->t('Precio'),
            'show' => 1,
            'weight' => 5,
          ],
        ],
      ],
      'acquiredOffers' => [
        'show' => 1,
        'url' => '/',
      ],
      'messages' => [
        'offerFree' => $this->t('Gratis.'),
        'error' => $this->t('En este momento no podemos obtener los productos disponibles, por favor intentelo más tarde.'),
        'offerError' => $this->t('No se encontrarón ofertas relacionadas con el número consultado.'),
      ],
      'config' => [
        'imagePath' => [
          'url' => '/',
        ],
        'orderList' => [
          'asc' => 1,
        ]
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
   * Build configuration form.
   *
   * {@inheritdoc}
   */
  public function adfBlockForm($form, FormStateInterface $form_state) {
    // Add config products list section.
    $form['offersList'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuración de productos'),
      '#open' => FALSE,
    ];
    $form['offersList']['fields'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field'),
        $this->t('label'),
        $this->t('Show'),
        $this->t('Weight'),

      ],
      '#responsive' => TRUE,
      '#empty' => $this->t('There are no items yet. Add an item.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'mytable-order-weight',
        ],
      ],
    ];

    $this->addOffersListFields($form);

    // Config acquired offers.
    $config = $this->configuration['acquiredOffers'];
    $acquiredOffersConfig = isset($config) ? $config : $this->contentFields['acquiredOffers'];
    $form['acquiredOffers'] = [
      '#type' => 'details',
      '#title' => $this->t('Adquirir oferta'),
      '#open' => FALSE,
    ];
    $form['acquiredOffers']['show'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Mostrar botón "Adquirir oferta"'),
      '#default_value' => $acquiredOffersConfig['show'],
    ];
    $form['acquiredOffers']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $acquiredOffersConfig['url'],
    ];

    // Config messages.
    $config = $this->configuration['messages'];
    $messagesConfig = isset($config) ? $config : $this->contentFields['messages'];
    $form['messages'] = [
      '#type' => 'details',
      '#title' => $this->t('Mensajes'),
      '#open' => FALSE,
    ];
    $form['messages']['offerFree'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje cuando el precio de la oferta es 0.'),
      '#default_value' => $messagesConfig['offerFree'],
    ];
    $form['messages']['error'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje de error'),
      '#default_value' => $messagesConfig['error'],
    ];
    $form['messages']['offerError'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mensaje para Oferta no encontrada'),
      '#default_value' => $messagesConfig['offerError'],
    ];
    // Config imagePath.
    $form['config'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones adicionales.'),
      '#open' => FALSE,
    ];
    $form['config']['imagePath'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones del camino de los archivos.'),
      '#open' => FALSE,
    ];
    $configImgPath = $this->configuration['config'];
    $form['config']['imagePath']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path de los archivos.'),
      '#default_value' => isset($configImgPath) ? $configImgPath['imagePath']['url'] : $this->contentFields['config']['imagePath']['url'],
    ];
    $form['config']['orderList'] = [
      '#type' => 'details',
      '#title' => $this->t('Configuraciones del listado de productos.'),
      '#open' => FALSE,
    ];
    $configImgPath = $this->configuration['config'];
    $form['config']['orderList']['asc'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Ordenar ascendente / descendente'),
      '#description' => $this->t('Si marca la casilla  se ordenara ascendentemente los productos, si no descendentemente'),
      '#default_value' => isset($configImgPath) ? $configImgPath['orderList']['asc'] : $this->contentFields['config']['orderList']['asc'],
    ];

    return $form;
  }

}
