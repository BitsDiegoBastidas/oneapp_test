<?php

namespace Drupal\oneapp_mobile_premium_gt\Services\v2_0;

use Drupal\oneapp_mobile_premium\Services\v2_0\PremiumPortfolioRestLogic;
use Drupal\file\Entity\File;

/**
 * Class PremiumPortfolioRestLogicGt.
 */
class PremiumPortfolioRestLogicGt extends PremiumPortfolioRestLogic {

  /**
   * {@inheritdoc}
   */
  public function get($account_id) {

    $available_offers = $this->service->getAvailableOffers($account_id);
    $active_offers = $this->service->getActiveOffers($account_id);
    $available_offers = $this->service->filterAvailableOffers($active_offers, $available_offers);

    $products = $this->service->getAllProducts();
    $product_list = [];
    $outstanding_product = NULL;
    $base_url = $this->utils->getUrlImages();
    $outstanding = NULL;
    $product_list_outstanding = [];

    foreach ($products as $product) {
      $drupal_service = $this->getDrupalService($product);

      $data_product = $drupal_service->getData($account_id, $product, $available_offers, $active_offers);

      if (!is_null($data_product) && $data_product['isActive']) {
        $product_data['productId'] = $this->formatField('productId', $product->get('id_service')->value);
        $product_data['productName'] = $this->formatField('productName', $product->get('name_service')->value);
        $product_data['price'] = $this->formatField('price', [
          'value' => $data_product['price'],
          'formattedValue' =>  $this->replaceTokens($product, $data_product['priceHtml'], $base_url),
        ]);
        $product_data['status'] = $this->formatField('status', [
          'value' => TRUE,
          'formattedValue' => t('Activo'),
        ]);

        $product_list[] = ['data' => $product_data, 'order' => $product->get('order')->value];
      }
      elseif (!is_null($data_product)) {

        $banner_url = rawurlencode(File::load($product->get('banner_card_list')->target_id)->get('filename')->value);
        $banner_responsive_url = rawurlencode(File::load($product->get('banner_card_detail')->target_id)->get('filename')->value);

        $data_product = $drupal_service->getDataEntity($product, $available_offers, $active_offers);

        $outstanding_product['productId'] = $this->formatField('productId', $product->get('id_service')->value);
        $outstanding_product['productName'] = $this->formatField('productName', $product->get('name_service')->value);
        $outstanding_product['price'] = $this->formatField('price', [
          'value' => $data_product['price'],
          'formattedValue' => $this->replaceTokens($product, $data_product['priceHtml'], $base_url),
        ]);
        $outstanding_product['banner'] = $this->formatField('banner', $banner_url);
        $outstanding_product['bannerResponsive'] = $this->formatField('banner', $banner_responsive_url);
        $outstanding_product['totalProducts'] = count($products);

        if ($product->get('outstanding')->value) {
          $outstanding = $outstanding_product;
        }
        else {
          $product_list_outstanding[] = ['data' => $outstanding_product, 'order' => $product->get('order')->value];
        }
      }
    }

    if (count($product_list) > 0) {
      array_multisort(array_column($product_list, 'order'), SORT_ASC, $product_list);
      $product_list = array_column($product_list, 'data');

      return ['productList' => $product_list];
    }
    elseif (count($product_list_outstanding) > 0) {

      array_multisort(array_column($product_list_outstanding, 'order'), SORT_ASC, $product_list_outstanding);
      $product_list_outstanding = array_column($product_list_outstanding, 'data');
      $product_list_outstanding[0]['totalProducts'] = count($product_list_outstanding);

      if (!is_null($outstanding)) {
        array_unshift($product_list_outstanding, $outstanding);
      }

      return $product_list_outstanding[0];
    }
    elseif (!empty($outstanding)) {
      array_unshift($product_list_outstanding, $outstanding);
      $product_list_outstanding[0]['totalProducts'] = 1;
      return $product_list_outstanding[0];
    }
    else {
      return ['productList' => [], 'noData' => ['value' => 'empty']];
    }
  }
}
