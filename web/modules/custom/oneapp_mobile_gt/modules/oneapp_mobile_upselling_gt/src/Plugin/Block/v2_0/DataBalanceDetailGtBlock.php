<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Block\v2_0;

use Drupal\adf_block_config\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\oneapp_mobile_upselling\Plugin\Block\v2_0\DataBalanceDetailBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;


class DataBalanceDetailGtBlock extends DataBalanceDetailBlock {



  /**
   * Default configuration to view detail.
   *
   * @return array
   *   Configurations to view detail
   */
  public function defaultConfigurationDetail() {
    return [
      'general' => [
        'title' => [
          'title' => $this->t('Titulo'),
          'value' => $this->t('Detalle de Internet'),
        ],
        'showTitle' => [
          'title' => $this->t('Mostrar Titulo'),
          'type' => 'checkbox',
          'value' => TRUE,
        ],
        'urlIconHeader' => [
          'title' => $this->t('Url Icono en encabezado'),
          'value' => 'https://via.placeholder.com/10x20/025/fff.png',
        ],
        'showIconHeader' => [
          'title' => $this->t('Mostrar Icono en encabezado'),
          'type' => 'checkbox',
          'value' => TRUE,
        ],
        'urlImageLocation' => [
          'title' => $this->t('Url donde se almacenan las imagenes'),
          'value' => 'http://micuenta2-tigo-com-gt-stg.tigocloud.net/sites/default/files/availableOffers',
        ],
        'showHideResponse' => [
          'title' => $this->t('Mostrar Respuesta hide para cuando no hay datos'),
          'type' => 'checkbox',
          'value' => TRUE,
        ],

      ],
      'fields' => [
        'bucketsId' => [
          'title' => $this->t('BucketsId'),
          'label' => $this->t('BucketsId'),
          'show' => FALSE,
        ],
        'name' => [
          'title' => $this->t('Name'),
          'show' => TRUE,
          'label' => '',
        ],
        'remainingValue' => [
          'title' => $this->t('Quota Restante'),
          'show' => TRUE,
          'label' => '',
        ],
        'reservedAmount' => [
          'title' => $this->t('Quota Reservada'),
          'show' => TRUE,
          'label' => $this->t('disponibles de'),
        ],
        'validFor' => [
          'title' => $this->t('Vigencia (Paquetes en espera)'),
          'show' => TRUE,
          'label' => 'En espera',
        ],
        'validForLabel' => [
          'title' => $this->t('Vencimiento (Apps Ilimitadas)'),
          'show' => TRUE,
          'label' => 'Vence en',
        ],
        'name_apps_unlimited_prepaid' => [
          'title' => $this->t('Apps Ilimitadas Prepago'),
          'show' => TRUE,
          'label' => 'Ilimitados',
        ],
        'name_apps_unlimited_postpaid' => [
          'title' => $this->t('Apps Ilimitadas Postpago'),
          'show' => TRUE,
          'label' => 'Contratados',
        ],
        'reserveUsed' => [
          'title' => $this->t('Utilizados'),
          'label' => $this->t('Utilizados'),
          'show' => 1,
        ],
      ],
      'buttons' => [
        'backSummaryButton' => [
          'title' => $this->t('Boton regresar a vista resumen'),
          'show' => TRUE,
          'label' => $this->t('Regresar'),
          'url' => '/',
          'type' => 'link',
        ],
        'buyButton' => [
          'title' => $this->t('Boton Comprar'),
          'show' => TRUE,
          'label' => $this->t('Comprar'),
          'url' => '/',
          'type' => 'button',
        ],

      ],
      'messages' => [
        'free' => $this->t('Gratis'),
        'unlimited' => $this->t('Ilimitado ∞'),
        'empty' => $this->t('No se encontraron resultados.'),
        'error' => $this->t('En este momento no podemos obtener el balance de los datos de internet, por favor intente de nuevo más tarde.'),
      ],
    ];
  }

  /**
   * Submit handler.
   *
   * {@inheritdoc}
   */
  public function adfBlockSubmit($form, FormStateInterface $form_state) {
    parent::adfBlockSubmit($form, $form_state);
    $this->configuration['detail']['general']['urlImageLocation']['value'] = $form_state->getValue(
      ['detail', 'general', 'urlImageLocation']
    );
  }

}
