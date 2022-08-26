<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\Core\Database\Database;
use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\AvailableOffersRestLogic;

/**
 * Class AvailableOffersRestLogic.
 */
class AvailableOffersGtRestLogic extends AvailableOffersRestLogic {

  const ASC_ORDER = 1;
  const DESC_ORDER = 0;
  const charsExclude = '*';

  /**
   * Array returned from getInfoTokenByMsisdn()
   *
   * @var array
   */
  protected $tokenInfo = [];

  protected $isPostpaid = FALSE;

  /**
   * AutopacksService.
   *
   * @var string
   */
  protected $autopacksService;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $primaryNumber;

  /**
   * Responds array available offers.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   The array response of object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \ReflectionException
   */
  public function get($msisdn) {
    // Get offers.
    $msisdn = $this->msisdnValid($msisdn);
    $info = $this->getInfoByMsisdn($msisdn);
    $this->isPostpaid = $this->isPostpaid($msisdn);
    $products = $this->getSanitizedAvailableOffers($msisdn, $info['billingType']);
    if (count($products) === 0) {
      return [];
    }

    $this->matchToAutoPackets($products, $msisdn);

    return ['sections' => $products];
  }

  /**
   * {@inheritdoc}
   */
  public function findProductById($package_id, $msisdn, $is_roaming = false, $billing_type = null) {
    $result = [];
    $this->isPostpaid = $this->isPostpaid($msisdn);
    $billing_type = $billing_type ?? $this->mobileUtils->getInfoTokenByMsisdn($msisdn)['billingType'];

    $products_roaming = $this->availableOffersServices->getAvailableRoamingOffers($msisdn, $billing_type);
    $products_vas = $this->availableOffersServices->getAvailableOfferByMsisdn($msisdn, $this->isPostpaid, false)->products ?? [];

    $products = array_merge($products_roaming,$products_vas);

    foreach ($products as $product) {
      if ($package_id == $product->packageId) {
        $result['packageId'] = $product->packageId;
        $result['price'] = [
          'value' => $product->price,
          'formattedValue' => $this->formatCurrencyValue($product->price),
        ];
        $result['type'] = $product->type;
        $result['description'] = $product->description;
        $result['validity'] = $this->sanitizeLabelOfValidity($product);
        $result['category'] = $product->category;
        $result['isFavorite'] = FALSE;
        if (isset($product->includedResources)) {
          $includes_resources = $product->includedResources;
          foreach ($includes_resources as $includes_resource) {
            if ($includes_resource->resourceUnits == 'FAVORITOS') {
              $result['isFavorite'] = TRUE;
            }
          }
        }
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function findAvailableLoans($package_id, $msisdn) {
    $msisdn = $this->msisdnValid($msisdn);
    $categories = [];
    $result = NULL;
    $this->isPostpaid = $this->isPostpaid($msisdn);
    $products = $this->availableOffersServices->getAvailableOfferByMsisdn($msisdn, $this->isPostpaid, false)->products ?? [];
    foreach ($products as $product) {
      if (isset($product->type) && $product->type == 'LOAN') {
        if (isset($product->category)) {
          $categories[] = $product->category;
        }
      }
      if ($package_id == $product->packageId) {
        $result = $product;
      }
    }
    return in_array($result->category, $categories) ? TRUE : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionTree() {
    $data = \Drupal::cache()->get('section_tree');
    if ($data === FALSE) {
      $data = $this->transformSectionsTree();
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function transformSectionsTree() {
    $sections = $this->getAllSectionsFormatted();
    $data = [];
    foreach ($sections as $section) {
      $categories = $this->getAllCategoriesFormatted($section['categories']);
      foreach ($categories as $category) {
        if ($category['subcategories']) {
          $subcategories = $this->getAllSubCategoriesFormatted($category['subcategories']);
          foreach ($subcategories as $subcategory) {
            $data[] = [
              'category' => $category,
              'section' => $section,
              'subcategory' => $subcategory,
            ];
          }
        }
        else {
          $data[] = [
            'category' => $category,
            'section' => $section,
          ];
        }
      }
    }
    \Drupal::cache()->set('section_tree', $data, time() + (60 * 60 * 1000), array('my_first_tag', 'my_second_tag'));
    return \Drupal::cache()->get('section_tree');
  }

  /**
   * {@inheritdoc}
   */
  public function cloneProduct($product) {
    $product_cloned = new \StdClass();
    $product_cloned->packageId = 'NBO-' . strval($product->packageId);
    $product_cloned->subcategory = 'NBO-' . strval($product->subcategory);
    $product_cloned->type = $product->type;
    $product_cloned->name = $product->name;
    $product_cloned->description = $product->description;
    $product_cloned->validityNumber = $product->validityNumber ?? null;
    $product_cloned->validityType = $product->validityType ?? null;
    $product_cloned->price = $product->price;
    $product_cloned->creditPackagePrice = $product->creditPackagePrice ?? null;
    $product_cloned->includedResource = $product->includedResources ?? null;
    return $product_cloned;
  }

  /**
   * @param $products
   * @param $msisdn
   */
  public function matchProductsToNBO(&$products, $msisdn) {
    if (!$this->isPostpaid) {
      $available_offers_service = \Drupal::service('oneapp_mobile_upselling.v2_0.available_offers_services');
      $suggested_products = !empty($available_offers_service->getSuggestedProducts($msisdn)) ? $available_offers_service->getSuggestedProducts($msisdn)->suggestedProducts : null;

      if (!empty($suggested_products)) { //validate empty response of suggested product
      foreach ($products as $product) {
        foreach ($suggested_products as $suggested_product) {
          if ($suggested_product->product == $product->packageId) {
            $products[] = $this->cloneProduct($product);
          }
        }
      }
      }
    }
  }

  public function getSanitizedAvailableOffers($msisdn, $billing_type) {
    $result = [];
    $config = $this->configBlock['offersList']['fields'];
    $products = $this->availableOffersServices->getAvailableOfferByMsisdn($msisdn, $this->isPostpaid, '')->products ?? [];
    $this->matchProductsToNBO($products, $msisdn);
    $products_roaming = $this->availableOffersServices->getAvailableRoamingOffers($msisdn, $billing_type);
    foreach ($products_roaming as $key => $value) {
      $products[] = $value;
    }
    $details = '';
    if ($this->isPostpaid) {
      $details = $this->getSanitizedContractDetails($msisdn);
    }
    $validity_text = t($config['validityPostpaid']['label'], ['@date' => $details]);
    foreach ($products as $product) {
      if (isset($product->type) && ($product->type == 'PACKAGE' || $product->type == 'SUBSCRIPTION' || $product->type == 'ROAMING')) {
        $package_id = ($product->type == 'ROAMING') ? 'ROAM' . $product->packageId : $product->packageId;
        $arr_item = [];
        $arr_item['offerId'] = [
          'label' => '',
          'show' => FALSE,
          'value' => $package_id,
          'formattedValue' => $package_id,
        ];
        $arr_item['offerName'] = [
          'label' => $config['offerName']['label'],
          'show' => (bool) $config['offerName']['show'],
          'value' => $product->name,
          'formattedValue' => $product->name,
        ];
        $arr_item['description'] = [
          'label' => $config['description']['label'],
          'show' => (bool) $config['description']['show'],
          'value' => $product->description,
          'formattedValue' => $product->description,
        ];
        $arr_item['price'] = [
          'label' => $config['price']['label'],
          'show' => (bool) $config['price']['show'],
          'value' => [
            'amount' => $product->price,
            'currencyId' => 'GTQ',
          ],
          'formattedValue' => $this->formatCurrencyValue($product->price),
        ];
        $arr_item['validity'] = [
          'label' => $config['validity']['label'],
          'show' => ($product->category != 'velocidad_adicional') && !empty($config['validity']['show']), //ONEAPP-10446
          'value' => [
            'validity' => $product->validityNumber ?? null,
            'validityUnit' => $this->isPostpaid ? $details : ($product->validityType ?? null),
          ],
          'formattedValue' => $this->isPostpaid ? $validity_text : $this->sanitizeLabelOfValidity($product),
        ];
        $arr_item['creditPackagePrice'] = isset($product->creditPackagePrice) ? TRUE : FALSE;
        if (isset($product->includedResources)) {
          $included_resources = $product->includedResources;
          $arr_tags = [];
          $arr_images = [];
          foreach ($included_resources as $resource) {
            if (strtolower($resource->resourceUnits) == 'app-pass') {
              $arr_tags[] = $resource->resourceDescription;
              $arr_images[] = $this->findImage($resource->resourceDescription);
            }
          }
          $arr_item['tags'] = [
            'label' => '',
            'show' => (bool) $config['tags']['show'],
            'value' => $arr_tags,
            'imageName' => $arr_images,
          ];
        }
        $this->addProductInSectionArray($product->subcategory, $result, $arr_item);
      }
    }

    $this->cleanWeightProperty($result);
    $this->addRechargeProduct($result);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function cleanWeightProperty(&$arr) {
    foreach ($arr as $id => $item) {
      if (isset($arr[$id]['weight'])) {
        unset($arr[$id]['weight']);
      }
      foreach ($item['categories'] as $idCateg => $category) {
        if (isset($arr[$id]['categories'][$idCateg]['weight'])) {
          unset($arr[$id]['categories'][$idCateg]['weight']);
        }
        if (isset($category['subcategories'])) {
          foreach ($category['subcategories'] as $idSubCateg => $subcategory) {
            if (isset($arr[$id]['categories'][$idCateg]['subcategories'][$idSubCateg]['weight'])) {
              unset($arr[$id]['categories'][$idCateg]['subcategories'][$idSubCateg]['weight']);
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sanitizeLabelOfValidity($package) {
    $validity_type = $package->validityType ?? '';
    $validity_number = $package->validityNumber ?? '';
    $validity_text = "";
    if ($validity_type =='Hasta las 23:59:59' || $validity_type == 'hoy a la medianoche (cobro diario)') {
      $validity_text = 'Hoy hasta la Medianoche';
    }
    else {
      switch (strtoupper($validity_type)) {
        case "DIA":
          $validity_type = ($validity_number > 1) ? 'días' : 'dia';
          break;

        case "DIAS":
          $validity_type = ($validity_number > 1) ? 'días' : 'dia';
          break;

        case "SEMANA":
          $validity_type = ($validity_number > 1) ? 'semanas' : 'semana';
          break;

        case "SEMANAS":
          $validity_type = ($validity_number > 1) ? 'semanas' : 'semana';
          break;

        case "MES":
          $validity_type = ($validity_number > 1) ? 'meses' : 'mes';
          break;

        case "MESES":
          $validity_type = ($validity_number > 1) ? 'meses' : 'mes';
          break;
      }

      $validity_text = $validity_number.' '.$validity_type;
    }
    return $validity_text;
  }

  /**
   * {@inheritdoc}
   */
  public function getSanitizedContractDetails($msisdn) {
    $details = $this->getContractDetails($msisdn);
    $next_billing_date = $details->Envelope->Body->GetPostpaidContractDetailsResponse->nextBillingDate;
    $time = strtotime($next_billing_date);
    $label = date('d/M/Y', $time);
    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function formatCurrencyValue($value) {
    $formatted_value = $this->utils->formatCurrency($value, TRUE);
    $formatted_value = str_replace(' ', '', $formatted_value);
    return $formatted_value;
  }

  /**
   * {@inheritdoc}
   */
  public function addProductInSectionArray($subcat_label, &$arr, $product) {
    $obj_tree = $this->getSectionTree();
    $section_tree = $obj_tree->data;
    foreach ($section_tree as $item) {
      if (isset($item['subcategory']) && (strtolower($item['subcategory']['label']) == strtolower($subcat_label))) {
        $this->addSectionCategSubCategProduct($item['section'], $item['category'], $item['subcategory'], $arr, $product);
      }
      elseif ($this->matchCategoryKeyToResponseSubCategory(strtolower($item['category']['key']), strtolower($subcat_label))) {
        $flag = TRUE;
        if (strtolower($item['category']['key']) == 'premium') {
          $entity = \Drupal::entityTypeManager()->getStorage('mobile_offers_category_entity')->load($item['category']['id']);
          $package_ids = explode(',', $entity->get('packageIds'));
          $flag = isset($package_ids) && in_array(strval($product['offerId']['value']), $package_ids) ? TRUE : FALSE;
        } 
        if ($flag === TRUE) {
          $this->addSectionCategProduct($item['section'], $item['category'], $arr, $product);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function matchCategoryKeyToResponseSubCategory($key, $subcategory) {
    $arr = explode('|', $key);
    if (in_array($subcategory, $arr)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function addSectionCategSubCategProduct($section, $categ, $subcateg, &$arr, $product) {
    $found_sect = FALSE;
    $found_categ = FALSE;
    $found_sub_categ = FALSE;
    $section_index = -1;
    $category_index  = -1;
    foreach ($arr as $id_section => $item) {
      if ($item['label'] == $section['label']) {
        $found_sect = TRUE;
        $section_index = $id_section;
        foreach ($item['categories'] as $id_category => $category) {
          if (strtolower($category['label']) == strtolower($categ['label'])) {
            $found_categ = TRUE;
            $category_index = $id_category;
            foreach ($category['subcategories'] as $id_subcategory => $subcategory) {
              if (strtolower($subcategory['label']) == strtolower($subcateg['label'])) {
                $found_sub_categ = TRUE;
                $arr[$id_section]['categories'][$id_category]['subcategories'][$id_subcategory]['products'][] = $product;
                $this->orderByAmount($arr[$id_section]['categories'][$id_category]['subcategories'][$id_subcategory]['products'],
                  self::DESC_ORDER);
              }
            }
          }
        }
      }
    }
    if (!$found_sect) {
      $product['order'] = [
        'label' => 1,
        'show' => TRUE,
      ];
      $arr[] = [
        'label' => $section['label'],
        'class' => $section['class'],
        'show' => (bool) $section['show'],
        'weight' => $section['weight'],
        'categories' => [
          [
            'label' => $categ['label'],
            'show' => (bool) $categ['show'],
            'expand' => (bool) $categ["expand"],
            'weight' => $categ['weight'],
            'subcategories' => [
              [
                'label' => $subcateg['label'],
                'show' => (bool) $subcateg['show'],
                'expand' => (bool) $subcateg['expanded'],
                'weight' => $subcateg['weight'],
                'products' => [$product],
              ],
            ],
          ],
        ],
        'banner' => $section['banner'] ?? [],
      ];
      $this->orderByWeight($arr);
      $found_categ = TRUE;
      $found_sub_categ = TRUE;
    }
    if (!$found_categ) {
      $product['order'] = [
        'label' => 1,
        'show' => TRUE,
      ];
      $arr[$section_index]['categories'][] = [
        'label' => $categ['label'],
        'show' => (bool) $categ['show'],
        'expand' => (bool) $categ['expanded'],
        'weight' => $categ['weight'],
        'subcategories' => [
          [
            'label' => $subcateg['label'],
            'show' => (bool) $subcateg['show'],
            'expand' => (bool) $subcateg['expanded'],
            'weight' => $subcateg['weight'],
            'products' => [$product],
          ]
        ],
      ];
      $this->orderByWeight($arr[$section_index]['categories']);
    }
    if (!$found_sub_categ && $category_index != -1) {
      $product['order'] = [
        'label' => 1,
        'show' => TRUE,
      ];
      $arr[$section_index]['categories'][$category_index]['subcategories'][] = [
        'label' => $subcateg['label'],
        'show' => (bool) $subcateg['show'],
        'expand' => (bool) $subcateg['expanded'],
        'weight' => $subcateg['weight'],
        'products' => [$product],
      ];
      $arr[$section_index]['categories'][$category_index]['expand'] = (bool) $categ['expanded'];
      $this->orderByWeight($arr[$section_index]['categories'][$category_index]['subcategories']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addSectionCategProduct($section, $categ, &$arr, $product) {
    $found_sect = FALSE;
    $found_categ = FALSE;
    $section_index = -1;
    foreach ($arr as $id_section => $item) {
      if ($item['label'] == $section['label']) {
        $found_sect = TRUE;
        $section_index = $id_section;
        foreach ($item['categories'] as $id_category => $category) {
          if (strtolower($category['label']) == strtolower($categ['label'])) {
            $found_categ = TRUE;
            $arr[$id_section]['categories'][$id_category]['products'][] = $product;
            $this->orderByAmount($arr[$id_section]['categories'][$id_category]['products'],
              $this->configBlock['config']['orderList']['asc']);
          }
        }
      }
    }
    if (!$found_sect) {
      $product['order'] = [
        'label' => 1,
        'show' => TRUE,
      ];
      $arr[] = [
        'label' => $section['label'],
        'class' => $section['class'],
        'show' => (bool) $section['show'],
        'weight' => $section['weight'],
        'categories' => [
          [
            'label' => $categ['label'],
            'description' => $categ['description'] ?? '',
            'show' => (bool) $categ['show'],
            'expand' => (bool) $categ["expand"],
            'weight' => $categ['weight'],
            'products' => [$product],
          ],
        ],
        'banner' => $section['banner'] ?? [],
      ];
      $this->orderByWeight($arr);
      $found_categ = TRUE;

    }
    if (!$found_categ) {
      $product['order'] = [
        'label' => 1,
        'show' => TRUE,
      ];
      $arr[$section_index]['categories'][] = [
        'label' => $categ['label'],
        'show' => (bool) $categ['show'],
        'expand' => (bool) $categ["expand"],
        'weight' => $categ['weight'],
        'products' => [$product],
        'banner' => $section['banner'] ?? [],
      ];
      $this->orderByWeight($arr[$section_index]['categories']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addRechargeProduct(&$arr) {
    $config = [];
    foreach ($this->getAllSectionsFormatted() as $section) {
      if (strtolower($section['label']) == 'recargas') {
        $config = [
          'label' => $section['label'],
          'class' => $section['class'],
          'show' => $section['show'],
          'banner' => $section['banner'],
        ];
        break;
      }
    }

    $arr[] = array_merge([
      'label' => 'Recargas',
      'class' => 'recharges',
      'show' => TRUE,
      'categories' => [],
    ], $config);
  }

  /**
   * Sort array of the sections, categories or subcategories by weight.
   *
   * @param array $rows
   *   Array of categories or subcatgories.
   */
  public function orderByAmount(array &$rows, $order) {
    if ($order == self::DESC_ORDER) {
      usort($rows, function ($a, $b) {
        return floatval($a['price']['value']['amount']) < floatval($b['price']['value']['amount']);
      });
    }
    else {
      usort($rows, function ($a, $b) {
        return floatval($a['price']['value']['amount']) > floatval($b['price']['value']['amount']);
      });
    }
    foreach ($rows as $id => $row) {
      $rows[$id]['order'] = [
        'label' => $id + 1,
        'show' => TRUE,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeClient($msisdn = '') {
    $info = $this->getInfoByMsisdn($msisdn);
    $this->primaryNumber['info'] = $info['clientType'];
    return $this->primaryNumber['info'];
  }

  /**
   * {@inheritdoc}
   */
  public function isPostpaid($msisdn = '') {
    $info = $this->getInfoByMsisdn($msisdn);
    return ($info['billingType'] != 'prepaid');
  }

  /**
   * Implements getInfoByMsisdn.
   * Return array with token information by msisdn
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return Array
   *
   * @throws \ReflectionException
   */
  public function getInfoByMsisdn($msisdn = '') {
    if (empty($this->tokenInfo)) {
      try {
        $this->tokenInfo = $this->mobileUtils->getInfoTokenByMsisdn($msisdn);
        if (!empty($this->tokenInfo)) {
          if ($this->tokenInfo['billingType'] == 'prepaid') {
            $this->tokenInfo['clientType'] = 'PREPAGO';
          }
          elseif ($this->tokenInfo['billingType'] == 'hybrid') {
            $this->tokenInfo['clientType'] = 'FACTURA FIJA';
          }
          elseif ($this->tokenInfo['billingType'] == 'postpaid') {
            $this->tokenInfo['clientType'] = 'CREDITO';
          }
          $this->primaryNumber['info'] = $this->tokenInfo['clientType'];
        }
      }
      catch (HttpException $exception) {
        $messages = $this->configBlock['messages'];
        $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
        $message = ($exception->getCode() == '404') ? $title . $messages['empty'] : $title . $messages['error'];

        $reflected_object = new \ReflectionClass(get_class($exception));
        $property = $reflected_object->getProperty('message');
        $property->setAccessible(TRUE);
        $property->setValue($exception, $message);
        $property->setAccessible(FALSE);

        throw $exception;
      }
    }
    return $this->tokenInfo;
  }

  /**
   * Implements getContractDetails.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return ResponseInterface
   *   The HTTP response object.
   *
   * @throws \ReflectionException
   */
  public function getContractDetails($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v1_0_postpaid_contract_details_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      $messages = $this->configBlock['messages'];
      $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
      $message = ($exception->getCode() == '404') ? $title . $messages['empty'] : $title . $messages['error'];

      $reflected_object = new \ReflectionClass(get_class($exception));
      $property = $reflected_object->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      throw $exception;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function findImage($resource_description) {
    $value = $this->sanitizedResourceDescritionLabel($resource_description) . '.svg';

    $name_image = $this->mobileUtils->getImageName($value);

  return $name_image;
  }

  /**
   * {@inheritdoc}
   */
  public function sanitizedResourceDescritionLabel($label) {
    $label = str_replace(str_split(self::charsExclude), '', $label);
    $label = str_replace(' ', '_', strtolower($label));
    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function msisdnValid($msisdn) {
    $mobile_settings = \Drupal::config('oneapp_mobile.config')->get('general');
    $global_settings = \Drupal::config('oneapp_endpoints.settings')->getRawData();
    $msisdn_lenght = $mobile_settings['msisdn_lenght'];
    $prefix_country = $global_settings['prefix_country'];
    if (strlen($msisdn) <= $msisdn_lenght && !preg_match("/^{$prefix_country}[0-9]{$msisdn_lenght}$/", $msisdn)) {
      $msisdn = $prefix_country . $msisdn;
    }
    return $msisdn;
  }

  /**
   * Realize validations for match or exclude transformations of products
   */
  public function matchToAutoPackets(&$response, $msisdn) {
    $is_type_client_allowed = $this->isTypeClientAllowed($this->primaryNumber['info']);
    if ($this->isEnabledAutoPackets() && $is_type_client_allowed) {
      $this->autopacksService = \Drupal::service('oneapp_mobile_payment_gateway_autopackets.v2_0.autopackets_services');
      if ($this->autopacksService->isActiveAutoPackets()) {
        $config_for_autopackets = $this->autopacksService->getConfigForAutoPackets();
        if ($config_for_autopackets['order'] == 'all') {
          $this->applyAllAutoPackets($response, $config_for_autopackets);
        }
        if ($config_for_autopackets['order'] == 'match') {
          $this->applyMatchAutoPackets($response, $config_for_autopackets);
        }
        if ($config_for_autopackets['order'] == 'exclude') {
          $this->applyExcludeAutoPackets($response, $config_for_autopackets);
        }
      }
    }
  }

  /**
   * Realize set class atribute in offerName object for products
   */
  public function applyMatchAutoPackets(&$products, $config_auto_packets) {
    $ids_for_autopackets = $config_auto_packets['ids'];
    $class_for_auto_packts = $config_auto_packets['class'];
    $min_amount = floatval($config_auto_packets['min_amount']);
    foreach ($products as $idSect => $section) {
      if (isset($section['categories'])) {
        foreach ($section['categories'] as $idCateg => $category) {
          if (!empty($category['products'])) {
            foreach ($category['products'] as $idProd => $product) {
              $product_amount = floatval($product['price']['value']['amount']);
              $is_allowed_validity = $this->autopacksService->isAllowedValityAutopacks($product['validity']['value']['validity'],
                $product['validity']['value']['validityUnit']);
              if (in_array($product['offerId']['value'], $ids_for_autopackets) &&
                $product_amount >= $min_amount && $product['creditPackagePrice'] && $is_allowed_validity) {
                $products[$idSect]['categories'][$idCateg]['products'][$idProd]['offerName']['class'] = $class_for_auto_packts;
              }
              unset($products[$idSect]['categories'][$idCateg]['products'][$idProd]['creditPackagePrice']);
            }
          }
        }
      }
    }
  }

  /**
   * Realize set class atribute in offerName object for products
   */
  public function applyExcludeAutoPackets(&$products, $config_auto_packets) {
    $ids_for_autopackets = $config_auto_packets['ids'];
    $class_for_auto_packts = $config_auto_packets['class'];
    $min_amount = floatval($config_auto_packets['min_amount']);
    foreach ($products as $idSect => $section) {
      if (isset($section['categories'])) {
        foreach ($section['categories'] as $idCateg => $category) {
          if (!empty($category['products'])) {
            foreach ($category['products'] as $idProd => $product) {
              $product_amount = floatval($product['price']['value']['amount']);
              $is_allowed_validity = $this->autopacksService->isAllowedValityAutopacks($product['validity']['value']['validity'],
                $product['validity']['value']['validityUnit']);
              if (!in_array($product['offerId']['value'], $ids_for_autopackets) &&
                $product_amount >= $min_amount && $product['creditPackagePrice'] && $is_allowed_validity) {
                $products[$idSect]['categories'][$idCateg]['products'][$idProd]['offerName']['class'] = $class_for_auto_packts;
              }
              unset($products[$idSect]['categories'][$idCateg]['products'][$idProd]['creditPackagePrice']);
            }
          }
        }
      }
    }
  }

  /**
   * Realize set class atribute in offerName object for products
   */
  public function applyAllAutoPackets(&$products, $config_auto_packets) {
    $class_for_auto_packts = $config_auto_packets['class'];
    $min_amount = floatval($config_auto_packets['min_amount']);
    foreach ($products as $idSect => $section) {
      if (isset($section['categories'])) {
        foreach ($section['categories'] as $idCateg => $category) {
          if (!empty($category['products'])) {
            foreach ($category['products'] as $idProd => $product) {
              $product_amount = floatval($product['price']['value']['amount']);
              $is_allowed_validity = $this->autopacksService->isAllowedValityAutopacks($product['validity']['value']['validity'],
                $product['validity']['value']['validityUnit']);
              if ($product_amount >= $min_amount && $product['creditPackagePrice'] && $is_allowed_validity) {
                $products[$idSect]['categories'][$idCateg]['products'][$idProd]['offerName']['class'] = $class_for_auto_packts;
              }
              unset($products[$idSect]['categories'][$idCateg]['products'][$idProd]['creditPackagePrice']);
            }
          }
        }
      }
    }
  }

  /**
   * Determinate if module autopackets is active
   */
  public function isEnabledAutoPackets() {
    $config = \Drupal::config('oneapp_mobile.config');
    $config_auto_packets = $config->get('autopackets');
    return (bool) (isset($config_auto_packets) && $config_auto_packets['activate_autopackets'] == 1);
  }

  /**
   * GetPlanType form AccountInfo.
   *
   * @param mixed $account_info
   *   Account Info object from Client.
   *
   * @return string
   *   planType.
   */
  protected function isTypeClientAllowed($account_info) {
    $array = [];
    $config_autopack = \Drupal::config("oneapp_mobile.config")->get("autopackets");
    switch ($account_info) {
      case 'PREPAGO':
      case 'KIT':
        if ($config_autopack['autopackets_plan_types']['prepaid']) {
          $array = ['PREPAGO', 'KIT'];
        }
        break;

      case 'FACTURA FIJA':
        if ($config_autopack['autopackets_plan_types']['hybrid']) {
          $array = ['FACTURA FIJA'];
        }
        break;

      case 'CREDITO':
      case 'STAFF DE COMCEL':
        if ($config_autopack['autopackets_plan_types']['postpaid']) {
          $array = ['CREDITO', 'STAFF DE COMCEL'];
        }
        break;

      default:
        $array = [];
        break;
    }
    return in_array($account_info, $array);
  }

}
