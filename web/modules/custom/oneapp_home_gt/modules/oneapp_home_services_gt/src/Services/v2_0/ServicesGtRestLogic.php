<?php

namespace Drupal\oneapp_home_services_gt\Services\v2_0;

use Drupal\oneapp_home_services\Services\v2_0\ServicesRestLogic;

/**
 * Class ServicesGtRestLogic.
 */
class ServicesGtRestLogic extends ServicesRestLogic {

  /**
   * Retorna Servicios complementarios.
   *
   * @return array
   */
  public function getPlanHomeDetail($plan_code) {
    try {

      $result = $this->dataService->getPlanHomeDetailApi($plan_code);

      // isHD, isMainPlan, isDigital, productType, isPrepaid.
      $product_type = $result->productType;
      $is_digital = $result->isDigital;
      $is_main_plan = $result->isMainPlan;
      $product_name = $result->planName;
      $product_class = '';
      switch ($product_type) {

        case 6:
          $product_class = 'tv';
          break;

        case 7:
          if ($is_digital && !$is_main_plan) {
            $product_class = 'tv';
          }
          if ($is_digital && $is_main_plan) {
            $product_class = 'tv';
          }
          if (!$is_digital) {
            $product_class = 'tv';
          }
          break;

        case 8:
          if (!$is_main_plan) {
            $product_class = 'internet';
          }
          break;

        case 9:
          $product_class = 'telefonia';
          break;

      }
      return ['productName' => $product_name, 'productClass' => $product_class];

    }
    catch (\Exception $e) {
      return '';
    }
  }

  /**
   * Override getAllProducts method to show the active products only.
   *
   * @return array
   */
  public function getAllProducts($id) {
    $products = [];
    $client_account_general_info = $this->dataService->getClientAccountGeneralInfo($id);
    if (isset($client_account_general_info->contracts->ContractType)) {
      $contract_type = $client_account_general_info->contracts->ContractType;

      $contract_number = $contract_type->contractNumber;

      $home_bundle_info = $this->dataService->getHomeBundleInfo($contract_number);

      $asset_type = $contract_type->accounts->AssetType;

      // Convierte a Array el valor de assetType cuando solo viene un elemento.
      if (!is_array($asset_type) && is_object($asset_type)) {
        $asset_type_array = [];
        $asset_type_array[] = $asset_type;
        $asset_type = $asset_type_array;
      }

      $offering_list = [];
      foreach ($asset_type as $product_type) {
        $msisdn = $product_type->msisdn;
        $home_supplementary_services = $this->dataService->getHomeSupplementaryServices($msisdn);
        $plan_code = $product_type->plans->PlanType->planCode;
        $plan_home_detail = $this->getPlanHomeDetail($plan_code);

        $offering_list[] = [
          'productId' => $plan_code,
          'offeringName' => $plan_home_detail['productName'],
          'subscriptionNumber' => $msisdn,
        ];

        if (!empty($home_supplementary_services)) {
          foreach ($home_supplementary_services as $key => $value) {
            $offering_list[] = [
              'productId' => $plan_code,
              'offeringName' => $value->serviceDescription,
              'subscriptionNumber' => $msisdn,
            ];
          }
        }

        $product = [
          'productId' => $plan_code,
          'productName' => $plan_home_detail['productName'],
          'productClass' => $plan_home_detail['productClass'],
          'offeringList' => $offering_list,
        ];

        unset($offering_list);
        array_push($products, $product);
      }

      $products['config']['subtitle'] = [
        'value' => $home_bundle_info->bundleName,
        'show' => TRUE,
      ];
    }

    return $products;
  }

  /**
   * Override getAllProducts method to show the active products only.
   *
   * @return array
   */
  public function formatPortfolio($response, $config) {

    if (empty($response)) {
      return [
        'products' => [],
        'noData' => [
          'message' => $this->configBlock['message']['empty']['label'],
          'value' => "empty",
        ],
      ];
    }

    $portfolio = [];
    $product_fields = $config["options"]["tables"]["productData"]["fields"];
    uasort($product_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $offering_fields = $config["options"]["tables"]["offeringData"]["fields"];
    uasort($offering_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    $device_fields = $config["options"]["tables"]["deviceList"]["fields"];
    uasort($device_fields, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

    foreach ($response as $productKey => $value) {
      foreach ($product_fields as $productField => $productFieldValue) {
        foreach ($value as $field => $fieldValue) {
          if ($productField == $field) {
            if ($field == "productName") {
              $portfolio[$productKey][$field] = [
                'label' => $productFieldValue["label"],
                'value' => $fieldValue,
                'formattedValue' => (string) $fieldValue,
                'class' => $value["productClass"],
                'show' => (isset($productFieldValue["show"]) && $productFieldValue["show"]) ? TRUE : FALSE,
              ];
            }
            else {
              $portfolio[$productKey][$field] = [
                'label' => $productFieldValue["label"],
                'value' => $fieldValue,

                'formattedValue' => (string) $fieldValue,
                'show' => (isset($productFieldValue["show"]) && $productFieldValue["show"]) ? TRUE : FALSE,
              ];
            }
          }
        }
      }

      if (!empty($value["offeringList"])) {
        foreach ($value["offeringList"] as $key => $offering) {
          foreach ($offering_fields as $offeringField => $offeringFieldValue) {
            foreach ($offering as $offeringIndex => $offeringValue) {
              if ($offeringField == $offeringIndex) {
                $portfolio[$productKey]['offeringList'][$key][$offeringField] = [
                  'label' => $offeringFieldValue["label"],
                  'value' => $offeringValue,
                  'formattedValue' => (string) $offeringValue,
                  'show' => (isset($offeringFieldValue["show"]) && $offeringFieldValue["show"]) ? TRUE : FALSE,
                ];
              }
            }
          }

          $device_list = [];
          if (isset($offering["deviceList"]) && count($offering["deviceList"]) > 0) {
            foreach ($offering["deviceList"] as $index => $device) {
              foreach ($device_fields as $deviceFieldIndex => $device_fieldsValue) {
                foreach ($device as $field => $deviceValue) {
                  if ($field == $deviceFieldIndex) {
                    $device_list[][$field] = [
                      'label' => $device_fieldsValue["label"],
                      'value' => $deviceValue,
                      'formattedValue' => (string) $deviceValue,
                      'show' => $device_fieldsValue["show"] ? TRUE : FALSE,
                    ];
                  }
                }
              }
            }
            $portfolio[$productKey]['offeringList'][$key]['devicesList'] = $device_list;
          }
        }
      }
    }

    $result['products'] = $portfolio;
    return $result;
  }

  /**
   * Verify if client is available for view intraway service
   *
   * @return boolean
   */
  public function isAvailableForIntrawayService($id) {
    $client_account_general_info = $this->dataService->getClientAccountGeneralInfo($id);
    if (isset($client_account_general_info->contracts->ContractType)) {
      $contract_type = $client_account_general_info->contracts->ContractType;
      // $contract_number = $contract_type->contractNumber;
      // $home_bundle_info = $this->dataService->getHomeBundleInfo($contract_number);

      $asset_type = $contract_type->accounts->AssetType;

      // Convierte a Array el valor de assetType cuando solo viene un elemento.
      if (!is_array($asset_type) && is_object($asset_type)) {
        $asset_type_array = [];
        $asset_type_array[] = $asset_type;
        $asset_type = $asset_type_array;
      }

      foreach ($asset_type as $product_type) {
        // $msisdn = $product_type->msisdn;
        $plan_code = $product_type->plans->PlanType->planCode;
        $plan_home_detail = $this->getPlanHomeDetail($plan_code);
        if ($plan_home_detail['productClass'] == 'internet') {
          return TRUE;
        }

      }
    }
    return FALSE;
  }

  /**
   * Eliminar el asento de las palabras.
   *
   * @param $str
   * @return string
   */
  public function stripAccents($str) {
    return strtr(
      utf8_decode($str),
      utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
      'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
  }

}
