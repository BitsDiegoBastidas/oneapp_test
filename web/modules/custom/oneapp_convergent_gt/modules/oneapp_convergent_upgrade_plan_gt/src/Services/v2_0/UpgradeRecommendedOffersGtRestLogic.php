<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0;

use Drupal\oneapp_convergent_upgrade_plan\Services\v2_0\UpgradeRecommendedOffersRestLogic;
use Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradePlanSendGtRestLogic;

/**
 * Class UpgradeRecommendedOffersGtRestLogic.
 */
class UpgradeRecommendedOffersGtRestLogic extends UpgradeRecommendedOffersRestLogic {

  /**
   * @var \Drupal\oneapp\Services\UtilsService
   */
  public $utils;
  /**
   * @var \Drupal\oneapp_home_gt\Services\UtilsGtService
   */
  public $homeUtils;
  /**
   * @var \Drupal\oneapp_convergent_upgrade_plan\Services\UtilService
   */
  public $upgradeUtils;
  /**
   * @var \Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradeServiceGt
   */
  public $service;
  /**
   * @var \Drupal\oneapp_home_services_gt\Services\v2_0\ServicesGtRestLogic
   */
  public $current;


  /**
   * Get all data plan card for api.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function get($billing_id, $return_recommended_plan = FALSE) {

    $home_utils_service = \Drupal::service('oneapp.home.utils');
    $info = $home_utils_service->getInfoTokenByBillingAccountId($billing_id);
    $customer_account_id =& $info['contractId'];
    $recommend_products = $this->service->getRecommendProductsData($info["subscriberId"]);
    $formatted_offers = UpgradePlanSendGtRestLogic::formatRecommendedOffers($recommend_products);
    $data = [];
    if (!empty($recommend_products)) {
      $this->service->setConfig($this->configBlock);
      $recommended_offers_config = $this->configBlock['recommendedOffers']['fields'];

      $data['comparative'] = FALSE;
      $data['planType'] = 'bundle';
      $data['planCard'] = [
        'title' => [
          'label' => $recommended_offers_config['static']['plan']['label'],
          'show' => (!empty($recommended_offers_config['static']['plan']['show'])) ? TRUE : FALSE,
        ],
        'tax' => [
          'label' => $recommended_offers_config['static']['iva']['label'],
          'show' => (!empty($recommended_offers_config['static']['iva']['show'])) ? TRUE : FALSE,
        ],
      ];

      $recommended_offers = $recommended_offers_verification = [];

      $product_configs = $this->getProductConfigs();

      foreach ($recommend_products as $key => $recommendProduct) {
        $recommended_offer_name = $recommendProduct->name;
        $recommended_offer_id = $recommendProduct->bundle;

        // De momento no se trabajara con plan recomendado.
        $recommended_offers[$key]['featured'] = FALSE;
        $recommended_offers[$key]['planId'] = $recommended_offer_id;
        $recommended_offers_verification[$recommended_offer_id] = [
          'planId' => $recommended_offer_id,
          'planName' => $this->upgradeUtils->getFormatLowerCase($recommended_offer_name, TRUE),
        ];

        $recommended_offers[$key]['planName'] = [
          'value' => $recommended_offer_name,
          'formattedValue' => $this->upgradeUtils->getFormatLowerCase($recommended_offer_name, TRUE),
          'show' => TRUE,
        ];

        $amount = $recommendProduct->fee;
        $currency_id = '';
        $recommended_offers[$key]['price'] = [
          'value' => [
            'amount' => $amount,
            'currencyId' => $currency_id,
          ],
          'formattedValue' => $this->service->formatCurrency($amount, TRUE),
          'show' => TRUE,
        ];

        $current_name = 'price';
        $current_name_label = $this->upgradeUtils->getProductConfigField($product_configs, $current_name, 'label');
        $current_name_show = $this->upgradeUtils->getProductConfigField($product_configs, $current_name, 'show');
        $current_name_class = $this->upgradeUtils->getProductConfigField($product_configs, $current_name, 'class');
        $current_name_format = $this->upgradeUtils->getProductConfigField($product_configs, $current_name, 'format');
        $icon_comparative = $this->upgradeUtils->getProductConfigField($product_configs, $current_name, 'icon');

        $current_plan_price[0] = [
          'productName' => [
            'value' => $current_name,
            'label' => $current_name_label,
            'show' => $current_name_show,
            'class' => $current_name_class,
          ],
          'newProduct' => [
            'value' => $amount,
            'formattedValue' => $this->upgradeUtils->getProductFormatValue($amount, $current_name_format),
            'class' => $this->upgradeUtils->getProductClass($amount, $icon_comparative),
          ],
        ];

        $recommended_offers_products = [];

        // OfferingPlan vs CurrentPlan.
        if (!empty($recommendProduct->benefits)) {
          foreach ($recommendProduct->benefits as $i => $benefit) {

            /**
             * Se usa el título o nombre del producto para buscar
             * si existe configuración particular para éste.
             * De la configuració sólo se tendrán en cuenta:
             * * label (como valor. si está vacío se mostrará el título)
             * * class (si está vacío se usará el nombre para crear una clase)
             * * show (por defecto será TRUE)
             */
            $key_name = strtolower(trim($benefit->benefitTitle));
            $class = strtr($key_name, [
              ' ' => '_',
              'á' => 'a',
              'é' => 'e',
              'í' => 'i',
              'ó' => 'o',
              'ú' => 'u',
              'ñ' => 'n']);

            $recommended_offers_products[$i] = [
              'value' => !empty($product_configs[$key_name]['label'])
                ? $product_configs[$key_name]['label']
                : $benefit->benefitTitle,
              'description' => $benefit->benefitDetail,
              'class' => !empty($product_configs[$key_name]['class'])
                ? $product_configs[$key_name]['class']
                : $class,
              'show' => isset($product_configs[$key_name]['show'])
                ? boolval($product_configs[$key_name]['show'])
                : TRUE,
            ];
          }
        }

        $recommended_offers[$key]['products']['offersList'] = $recommended_offers_products;
        $recommended_offers_verification_prodcuts = implode(" + ", $recommended_offers_verification[$recommended_offer_id]['offers'] ?? []);
        $recommended_offers_verification_plan_label = t('Plan');
        $recommended_offers_verification[$recommended_offer_id]['formattedValue'] =
          "{$recommended_offers_verification_plan_label} {$recommended_offers_verification_prodcuts}";
        // offerBody is the formatted data to send to upgrade JIRA ONEAPP-10398
        $recommended_offers[$key]['offerBody'] = [
          'bundle_id' => $recommended_offer_id,
          'name' => $recommended_offer_name,
          'price' => $amount,
          'plans' => $formatted_offers[$recommended_offer_id],
        ];
      }

      $config_verification = (!empty($this->configBlock['recommendedOffers']['verification']['fields'])) ?
        $this->configBlock['recommendedOffers']['verification']['fields'] : [];

      if ($return_recommended_plan) {
        return $recommended_offers_verification;
      }

      // Order By Amount and Featured.
      $this->upgradeUtils->setConfig($this->configBlock);
      $recommended_offers = $this->upgradeUtils->getOrderPlans($recommended_offers);

      $data['planList'] = array_values($recommended_offers);

      $data['verificationPlan']['title'] = [
        'value' => (!empty($config_verification['title']['label'])) ? $config_verification['title']['label'] : '',
        'show' => (!empty($config_verification['title']['show'])) ? TRUE : FALSE,
      ];
      $dia = $config_verification['date']['days'] + 1;
      $system_date = date("$dia-m-Y");
      $label_activation_date = $config_verification['date']['label'];
      $label_activation_date_end = str_replace("dias", $dia, $label_activation_date);
      $format_date = (!empty($config_verification['date']['formatDate'])) ? $config_verification['date']['formatDate'] : 'short';
      $data['verificationPlan'] = [
        'planType' => 'bundle',
        'title' => [
          'value' => (!empty($config_verification['title']['label'])) ? $config_verification['title']['label'] : '',
          'show' => (!empty($config_verification['title']['show'])) ? TRUE : FALSE,
        ],
        'detail' => [
          'value' => (!empty($config_verification['detail']['label'])) ? $config_verification['detail']['label'] : '',
          'show' => (!empty($config_verification['detail']['show'])) ? TRUE : FALSE,
        ],
        'upgradePlan' => [
          'label' => (!empty($config_verification['plan']['label'])) ? $config_verification['plan']['label'] : '',
          'values' => array_values($recommended_offers_verification),
          'show' => (!empty($config_verification['plan']['show'])) ? TRUE : FALSE,
        ],
        'account' => [
          'label' => (!empty($config_verification['bill']['label'])) ? $config_verification['bill']['label'] : '',
          'value' => $customer_account_id,
          'show' => (!empty($config_verification['bill']['show'])) ? TRUE : FALSE,
        ],
        'quotaPlan' => [
            'label' => (!empty($config_verification['quota']['label'])) ? $config_verification['quota']['label'] : '',
            'value' => $this->service->formatCurrency($amount, TRUE),
            'show' => (!empty($config_verification['quota']['show'])) ? TRUE : FALSE,
          ],
        'activateDate' => [
          'label' => (!empty($config_verification['date']['label'])) ? $label_activation_date_end : '',
          'value' => $this->homeUtils->formatDate(strtotime($system_date), $format_date),
          'show' => (!empty($config_verification['date']['show'])) ? TRUE : FALSE,
        ],
        'termsConditions' => [
          'label' => (!empty($config_verification['terms']['label'])) ? $config_verification['terms']['label'] : '',
          'url' => (!empty($config_verification['terms']['url'])) ? $config_verification['terms']['url'] : '#',
          'value' => (!empty($config_verification['termsDesc']['value'])) ? $config_verification['termsDesc']['value'] : '',
          'show' => (!empty($config_verification['terms']['show'])) ? TRUE : FALSE,
          'modal' => [
            'title' => !empty($config_verification['termsModal']['title']) ? $config_verification['termsModal']['title'] : '',
            'content' => !empty($config_verification['termsModal']['content']) ? $config_verification['termsModal']['content'] : '',
            'show' => !empty($config_verification['termsModal']['show']) ? TRUE : FALSE,
            'action' => [
              'type' => 'button',
              'label' => !empty($config_verification['termsModal']['button']) ? trim($config_verification['termsModal']['button']) : '',
              'url' => '/',
              'show' => !empty($config_verification['termsModal']['button'])
                ? !empty(trim($config_verification['termsModal']['button']))
                : FALSE,
            ]
          ]
        ],
      ];

    }
    else {
      return [
        'noData' => [
          'value' => 'hide',
        ],
      ];
    }

    return $data;

  }

  /**
   * {@inheritdoc}
   */
  public function getProductConfigs() {
    $product_configs = [];
    if (!empty($this->configBlock['recommendedOffers']['fields']['dynamic']['products'])) {
      $config = $this->configBlock['recommendedOffers']['fields']['dynamic']['products'];
      foreach ($config as $value) {
        $key = $value['key'];
        unset($value['key']);
        $product_configs[strtolower($key)] = $value;
      }
    }
    return $product_configs;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataConfig($data) {
    $data_config = [];
    if (!isset($data['noData'])) {

        $config_actions = (isset($this->configBlock['recommendedOffers']['actions']['card'])) ?
        $this->configBlock['recommendedOffers']['actions']['card'] : [];

      $url = (!empty($config_actions['recommendedOffersAction']['url'])) ?
        $config_actions['recommendedOffersAction']['url'] : [];

      $data_config['actions']['upgradePlan'] = [
        'label' => (!empty($config_actions['recommendedOffersAction']['label'])) ?
          $config_actions['recommendedOffersAction']['label'] : '',
        'show' => (!empty($config_actions['recommendedOffersAction']['show'])) ? TRUE : FALSE,
        'type' => (!empty($config_actions['recommendedOffersAction']['type'])) ?
          $config_actions['recommendedOffersAction']['type'] : 'button',
        'url' => $this->upgradeUtils->getUrlByOrigin($url),
      ];

      $url_purchase = (!empty($config_actions['verificationActionAccept']['url'])) ?
        $config_actions['verificationActionAccept']['url'] : [];
      $url_cancel = (!empty($config_actions['verificationActionCancel']['url'])) ?
        $config_actions['verificationActionCancel']['url'] : [];

      $data_config['actions']['verificationActions'] = [
        'purchase' => [
          'label' => (!empty($config_actions['verificationActionAccept']['label'])) ?
            $config_actions['verificationActionAccept']['label'] : '',
          'show' => (!empty($config_actions['verificationActionAccept']['show'])) ? TRUE : FALSE,
          'type' => (!empty($config_actions['verificationActionAccept']['type'])) ?
            $config_actions['verificationActionAccept']['type'] : 'button',
          'url' => $this->upgradeUtils->getUrlByOrigin($url_purchase),
        ],
        'cancel' => [
          'label' => (!empty($config_actions['verificationActionCancel']['label'])) ?
            $config_actions['verificationActionCancel']['label'] : '',
          'show' => (!empty($config_actions['verificationActionCancel']['show'])) ? TRUE : FALSE,
          'type' => (!empty($config_actions['verificationActionCancel']['type'])) ?
            $config_actions['verificationActionCancel']['type'] : 'button',
          'url' => $this->upgradeUtils->getUrlByOrigin($url_cancel),
        ],
      ];

      $image_manager = $this->service->getconfigFactoryService("oneapp.image_manager.config");
      $image_manager_fields = $image_manager->get('image_manager_fields');
      if (!empty($image_manager_fields['public_url'])) {
        $data_config['imagePath'] = ['url' => $image_manager_fields['public_url']];
      }
    }
    else {
      $data_config['message'] = $this->configBlock["message"]["empty"]["label"];
    }

    return $data_config;
  }

}
