<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0;

use Drupal\oneapp_convergent_upgrade_plan\Services\v2_0\UpgradeRecommendedOffersMobileRestLogic;

class UpgradeRecommendedOffersMobileGtRestLogic extends UpgradeRecommendedOffersMobileRestLogic
{

  /**
   * @var \Drupal\oneapp\Services\UtilsService
   */
  protected $utils;
  /**
   * @var \Drupal\oneapp_mobile_gt\Services\UtilsServiceGt
   */
  protected $mobileUtils;
  /**
   * @var \Drupal\oneapp_convergent_upgrade_plan\Services\UtilService
   */
  protected $upgradeUtils;
  /**
   * @var \Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradeServiceGt
   */
  protected $service;

  public function get($id, $resume = FALSE) {

    $this->service->setConfig($this->configBlock);

    $response_api_dar = $this->mobileUtils->getInfoTokenByAccountId($id);

    if (empty($response_api_dar['msisdn'])
     || empty($response_api_dar['billingAccountId'])
     || empty($response_api_dar['customerAccountId'])
     || ($response_api_dar['lifecycle_status'] != 'active')) {
      return $this->mobileUtils->getEmptyState(TRUE);
    }

    $profiling_available_plans = $this->service->getDetailsProfilingPlan($response_api_dar["msisdn"]);
    $renewals_offers_details   = $this->service->getRenewalsOffersDetails($response_api_dar['msisdn']);

    if (empty($renewals_offers_details)) {
      return $this->mobileUtils->getEmptyState(TRUE);
    }

    $recommended_offers_config = $this->configBlock['recommendedOffersMobile']['fields'];
    $current_plan = $this->getCurrentPlanFormatted($response_api_dar["msisdn"]);
    $formatted_offers = $this->formatRecommendedOffers($current_plan, $profiling_available_plans, $renewals_offers_details);

    if (empty($current_plan)) {
      return $this->mobileUtils->getEmptyState(TRUE);
    }

    /** @var \Drupal\oneapp_mobile_upselling_gt\Services\v2_0\DataBalanceDetailGtRestLogic $data_balance_service */
    $data_balance_service = \Drupal::service('oneapp_mobile_upselling.v2_0.data_balance_detail_rest_logic');
    $current_plan_quota_details = $data_balance_service->getQuotaInfo($response_api_dar['msisdn']);

    if (empty($current_plan_quota_details)) {
      return $this->mobileUtils->getEmptyState(TRUE);
    }

    $data['comparative'] = TRUE;
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

    // Api Plan - recommendedplans and availableplans
    $this->getComparativePlansMobile(
      $profiling_available_plans,
      $current_plan,
      $renewals_offers_details,
      $current_plan_quota_details,
      $recommended_offers,
      $recommended_offers_verification,
      $formatted_offers
    );

    $config_verification = (!empty($this->configBlock['recommendedOffersMobile']['verification']['fields'])) ?
      $this->configBlock['recommendedOffersMobile']['verification']['fields'] : [];

    // Order By Amount and Featured.
    $this->upgradeUtils->setConfig($this->configBlock);
    $recommended_offers = $this->upgradeUtils->getOrderPlans($recommended_offers);

    $data['planList'] = array_values($recommended_offers);

    $reset_date_str = $current_plan_quota_details->postpaidPlan->nextResetDate ?? date(\DateTime::ATOM);
    $reset_date_obj = \DateTime::createFromFormat(\DateTime::ATOM, $reset_date_str);
    $format_date = (!empty($config_verification['date']['formatDate'])) ? $config_verification['date']['formatDate'] : 'selfcare';
    $data['verificationPlan'] = [
      'planType' => 'bundle',
      'title' => [
        'value' => (!empty($config_verification['title']['label'])) ? $config_verification['title']['label'] : '',
        'show' => isset($config_verification['title']['show']) ? boolval($config_verification['title']['show']) : TRUE,
      ],
      'detail' => [
        'value' => (!empty($config_verification['detail']['label'])) ? $config_verification['detail']['label'] : t('Details'),
        'show' => isset($config_verification['detail']['show']) ? boolval($config_verification['detail']['show']) : TRUE,
      ],
      'upgradePlan' => [
        'label' => (!empty($config_verification['plan']['label'])) ? $config_verification['plan']['label'] : '',
        'values' => array_values($recommended_offers_verification),
        'show' => isset($config_verification['plan']['show']) ? boolval($config_verification['plan']['show']) : TRUE,
      ],
      'account' => [
        'label' => (!empty($config_verification['bill']['label'])) ? $config_verification['bill']['label'] : t('Cuenta'),
        'value' => $this->upgradeUtils->getFormatAccount($response_api_dar['billingAccountId']),
        'show' => isset($config_verification['bill']['show']) ? boolval($config_verification['bill']['show']) : TRUE,
      ],
      'activateDate' => [
        'label' => (!empty($config_verification['date']['label'])) ? $config_verification['date']['label'] : t('Fecha de activacion'),
        'value' => $this->utils->formatDate($reset_date_obj->getTimestamp(), $format_date),
        'show' => isset($config_verification['date']['show']) ? boolval($config_verification['date']['show']) : TRUE,
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

    return $data;
  }

  public function isAppInCurrentPlan($current_plan_quota_details, $app_name) {

    if (empty($current_plan_quota_details->currentPasses)) {
      return FALSE;
    }

    foreach ($current_plan_quota_details->currentPasses as $app) {
      $current_app_name_str_to_lower = strtolower($app->description);
      $app_name_without_space = str_replace("_", " ", $app_name);
      if (preg_match("/{$app_name_without_space}/i", $current_app_name_str_to_lower)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentPlanFormatted($id, $resume = FALSE) {

    $current_plan = $this->service->getCurrentDataPlanMobile($id);

    if (empty($current_plan)) {
      return NULL;
    }

    $empty_object = (object) [];

    $current_plan_data = [
      'rate'      => number_format((float)$current_plan->planPrice, 2, '.', ''),
      'local_call'=> $empty_object,
      'sms'       => $empty_object,
      'data'      => $empty_object,
      'roaming'   => $empty_object,
    ];

    if (empty($current_plan->consumption)) {
      return NULL;
    }

    $consumption = $current_plan->consumption;

    foreach ($consumption as $key => $value) {

      // local_call->limit will be ONNET + EXNET, according to ONEAPP-10423
      if ($value->type == "VOZ ONNET" || $value->type == "VOZ EXNET") {
        $value->limit = !empty($current_plan_data['local_call']->limit)
          ? $value->limit  +  $current_plan_data['local_call']->limit
          : $value->limit;
      }

      if ($value->type == "VOZ ONNET") {
        $current_plan_data['local_call'] = $value;
      }

      if ($value->type == "VOZ EXNET") {
        $current_plan_data['local_call']->limit = $value->limit;
      }

      if ($value->type == "SMS") {
        $current_plan_data['sms'] = $value;
      }

      if ($value->type == "DATA") {
        $current_plan_data['data'] = $value;
      }

      if ($value->type == "VOZ ROAMIN" || $value->type == "VOZ ROAMING IN") {
        $current_plan_data['roaming'] = $value;
      }
    }

    return $current_plan_data;
  }

  public function getAppValue($app_list, $app_name) {
    if ($app_name == "rate") {
      return $app_list["rate"];
    }

    if ($app_name == "roaming") {
      return strtolower($app_list[$app_name]->planName);
    }

    return $app_list[$app_name]->limit;
  }

  public function getAttributeFormattedValue($value, $format, $app_name) {
      if ($app_name == "data") {
        $value = intval($this->upgradeUtils->getProductFormatValue($value, "mbps"));
        return "{$value} GB";
      }

      if ($app_name == "local_call") {
        return ($value > 8000) ? "Ilimitado" : $value;
      }

      if ($app_name == "sms") {
        return "Ilimitados";
      }

      return "{$format}{$value}";
  }

  public function getComparativePlansMobile($available_plans, $current_plan, $recommended_list_plans,
    $current_plan_quota_details, &$recommended_offers, &$recommended_offers_verification, $formatted_offers = []) {

    $product_configs = $this->getProductConfigs();

    $enable_format_data = (!empty($this->configBlock['recommendedOffersMobile']['fields']['static']['mbpsFormatted']['method'])) ?
      $this->configBlock['recommendedOffersMobile']['fields']['static']['mbpsFormatted']['method'] : 0;

    $recommended_offer_data = 'MB';

    $offers_details = [];

    foreach ($available_plans->offers as $available_plan) {
      $key = $available_plan->webBundleReferenceId;
      $offers_details[$key]['web_bundle_reference_id'] = $key;
      $offers_details[$key]['smsPlanId'] = $available_plan->smsPlanId;
      $offers_details[$key]['voicePlanId'] = $available_plan->voicePlanId;
      $offers_details[$key]['dataPlanId'] = $available_plan->dataPlanId;
    }

    foreach ($recommended_list_plans as $recommended_plan) {
      if (array_key_exists($recommended_plan->bundle, $offers_details)) {
        $recommended_offer_name = $recommended_plan->name;
        $recommended_offer_id = $key = $recommended_plan->bundle;

        $recommended_offers[$key]['featured'] = TRUE;
        $recommended_offers[$key]['planId'] = $recommended_offer_id;

        $amount = $recommended_plan->fee;

        $recommended_offers_verification[$key] = [
          'planId' => $recommended_offer_id,
          'planName' => $this->upgradeUtils->getFormatLowerCase($recommended_offer_name, TRUE),
          'monthlyAmount' => $amount,
          'planAlternateId' => null,
        ];

        $recommended_offers[$key]['planName'] = [
          'value' => $recommended_offer_name,
          'formattedValue' => $this->upgradeUtils->getFormatLowerCase($recommended_offer_name, TRUE),
          'show' => TRUE,
        ];

        $currency_id = null;

        $recommended_offers[$key]['price'] = [
          'value' => [
            'amount' => $amount,
            'currencyId' => $currency_id,
          ],
          'formattedValue' => $this->service->formatCurrency($amount, TRUE),
          'show' => TRUE,
        ];

        $recommended_offers_products = [];
        $app_names = [];

        $internet_data_value = intval(str_replace("GB", " GB", $recommended_plan->data)) * 1000;

        $extra_applications = [
          "local_call"  => ["label" => "Llamadas",  "value"  => $recommended_plan->onNetMinutes + $recommended_plan->exNetMinutes],
          "data"        => ["label" => "Internet",  "value"  =>  str_replace("GB", "", $recommended_plan->data)],
          "roaming"     => ["label" => "Roaming",   "value"  => $recommended_plan->roaming],
          "sms"         => ["label" => "SMS",       "value"  => ""],
          "rate"        => ["label" => "Precio",    "value"  => $recommended_plan->fee]
        ];

        $app_names[] = "local_call";
        $app_names[] = "data";
        $app_names[] = "roaming";
        $app_names[] = "sms";
        $app_names[] = "rate";

        if (!empty($recommended_plan->apps)) {
          foreach ($recommended_plan->apps as $app) {
            $app_names[] = $app->name;
          }
        }

        $i = 0;
        foreach ($app_names as $app_name) {
          if (array_key_exists($app_name, $extra_applications)) {
            $product_name = $extra_applications[$app_name]["label"];
            $product_name_label = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'label');
            $product_name_show = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'show');
            $product_name_class = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'class');
            $product_name_format = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'format');
            $icon_comparative = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'icon');

            $recommended_offers_products[$i]['productName'] = [
              'value' => $product_name,
              'label' => $product_name_label,
              'show' => $product_name_show,
              'class' => $product_name_class,
            ];

            $product_name_format = isset($product_configs[strtolower($product_name)]['format'])
              ? $product_configs[strtolower($product_name)]['format']
              : null;

            $current_product_value = $this->getAppValue($current_plan, $app_name);

            $current_product_formatted_value = $app_name == "data" || $app_name == "local_call" || $app_name == "sms"
              ? $this->getAttributeFormattedValue($current_product_value, $product_name_format, $app_name)
              : $this->upgradeUtils->getProductFormatValue($current_product_value, $product_name_format);

            if ($app_name == "roaming") {
              $current_product_formatted_value = $current_product_value;
            }

            $recommended_offers_products[$i]['currentProduct'] = [
              'value' => $current_product_value,
              'formattedValue' => $current_product_formatted_value,
              'class' => $this->upgradeUtils->getProductClass(0, $icon_comparative),
            ];

            $new_product = $extra_applications[$app_name]["value"];

            $new_product_formatted_value = $app_name == "data" || $app_name == "local_call" || $app_name == "sms"
              ? $this->getAttributeFormattedValue($new_product, $product_name_format, $app_name)
              : $this->upgradeUtils->getProductFormatValue($new_product, $product_name_format);

            if ($app_name == "roaming") {
              $new_product_formatted_value = strtolower($new_product);
            }

            $recommended_offers_products[$i]['newProduct'] = [
              'value' => $new_product_formatted_value,
              'formattedValue' => $new_product_formatted_value,
              'class' => $this->upgradeUtils->getProductClass(0, $icon_comparative),
            ];
          }
          else {
            $product_name = $app_name;
            $product_name_label = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'label');
            $product_name_show = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'show');
            $product_name_class = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'class');
            $product_name_format = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'format');
            $icon_comparative = $this->upgradeUtils->getProductConfigField($product_configs, $product_name, 'icon');

            $recommended_offers_products[$i]['productName'] = [
              'value' => $product_name,
              'label' => $product_name_label,
              'show' => $product_name_show,
              'class' => $product_name_class,
            ];

            $current_product = $this->isAppInCurrentPlan($current_plan_quota_details, $product_name);
            $recommended_offers_products[$i]['currentProduct'] = [
              'value' => $current_product,
              'formattedValue' => $this->upgradeUtils->getProductFormatValue($current_product, $product_name_format),
              'class' => $this->upgradeUtils->getProductClass($current_product, $icon_comparative),
            ];

            $new_product = $product_name_show;
            $product_name_format = $product_configs[strtolower($product_name)]['format'];
            $recommended_offers_products[$i]['newProduct'] = [
              'value' => $new_product,
              'formattedValue' => $this->upgradeUtils->getProductFormatValue($new_product, $product_name_format),
              'class' => $this->upgradeUtils->getProductClass($new_product, $icon_comparative),
            ];
          }
          $i++;
        }
        $recommended_offers[$key]['products']['offersList'] = $recommended_offers_products;
        $recommended_offers[$key]['offerBody'] = $formatted_offers[$key] ?? [];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStrposNameProducts($name1, $name2) {
    $flag = FALSE;

    if (strpos(strtolower($name1), strtolower($name2)) !== FALSE) {
      $flag = TRUE;
    }

    if (strpos(strtolower($name2), strtolower($name1)) !== FALSE) {
      $flag = TRUE;
    }

    return $flag;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductConfigs()
  {
    $product_configs = [];
    if (isset($this->configBlock['recommendedOffersMobile']['fields']['dynamic']['products'])) {
      $config = $this->configBlock['recommendedOffersMobile']['fields']['dynamic']['products'];
      foreach ($config as $value) {
        $key = $value['key'];
        unset($value['key']);
        $product_configs[strtolower($key)] = $value;
      }
    }
    return $product_configs;
  }

  /**
   * @param string $msisdn
   * @param object $recommended_offers
   * @param array $detailed_offers
   * @return array
   */
  public function formatRecommendedOffers($current_plan, $recommended_offers, $detailed_offers) {


    $formatted_offers = [];

    if (empty($recommended_offers->offers) || empty($detailed_offers)) {
      return $formatted_offers;
    }

    // Indexing Detailed Offers
    foreach ($detailed_offers as $key => $val) {
      $detailed_offers[$val->bundle] = $val;
      unset($detailed_offers[$key]);
    }

    // Looking for recommended details
    foreach ($recommended_offers->offers as $key => $val) {
      if (!empty($val->webBundleReferenceId) && !empty($detailed_offers[$val->webBundleReferenceId])) {
        $recommended_offers->offers[$key]->details = $detailed_offers[$val->webBundleReferenceId];
        unset($recommended_offers->offers[$key]->details->devices);
        $formatted_offers[$val->webBundleReferenceId] = [
          'bundle_id' => $val->webBundleReferenceId,
          'name' => $recommended_offers->offers[$key]->details->name,
          'fee' => $recommended_offers->offers[$key]->details->fee,
          'requestBody' => [
            'currentPlanId' => $current_plan['local_call']->planCode ?? null,
            'planId' => [$val->voicePlanId, $val->smsPlanId, $val->dataPlanId],
            'plantType' => ['VOZ', 'SMS', 'GPRS'],
            'planDescription' => ['', '', ''],
            'planResource' => [12, 12, 12],
            'parameterName' => ['days', 'EFECTIVIDAD', 'canal', 'trasaction'],
            'parameterValue' => [0, 'P', 'selfcare', 'upsell']
          ],
        ];
      }
    }

    unset($detailed_offers);

    return $formatted_offers;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataConfig($data) {
    $data_config = [];
    if (!isset($data['noData'])) {
      $data_config['actions'] = $this->getActions();
    }
    else {
      $data_config['message'] = $this->configBlock["message"]["empty"]["label"];
    }

    return $data_config;
  }
}
