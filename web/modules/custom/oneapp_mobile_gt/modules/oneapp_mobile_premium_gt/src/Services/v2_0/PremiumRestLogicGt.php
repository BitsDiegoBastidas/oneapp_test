<?php

namespace Drupal\oneapp_mobile_premium_gt\Services\v2_0;

use Drupal\oneapp_mobile_premium\Services\v2_0\PremiumRestLogic;
use Drupal\file\Entity\File;

/**
 * Class PremiumRestLogicGt.
 */
class PremiumRestLogicGt extends PremiumRestLogic {


  /**
   * Get data all premium products formated.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function get($accountId) {

    $available_offers = $this->service->getAvailableOffers($accountId);
    $active_offers = $this->service->getActiveOffers($accountId);
    $available_offers = $this->service->filterAvailableOffers($active_offers, $available_offers);


    $products = $this->service->getAllProducts();

    $outstandingProduct = NULL;
    $base_url = $this->utils->getUrlImages();

    $productList = [];
    foreach ($products as $product) {
      $dupalService = $this->getDrupalService($product);
      $dataProduct = $dupalService->getData($accountId, $product, $available_offers, $active_offers);

      $bannerUrl = rawurlencode(File::load($product->get('banner_card_list')->target_id)->get('filename')->value);
      $bannerResponsiveUrl = rawurlencode(File::load($product->get('banner_card_detail')->target_id)->get('filename')->value);

      $visitLink = '';
      if ($this->mobileDetect->isIOS()) {
        $visitLink = $product->get('open_url_ios')->value;
      }
      elseif ($this->mobileDetect->isAndroidOS()) {
        $visitLink = $product->get('open_url_android')->value;
      }
      else {
        $visitLink = $product->get('open_url')->value;
      }

      if (!is_null($dataProduct)) {
        if ($product->get('logo1')->target_id) {
          $logo1 = rawurlencode(File::load($product->get('logo1')->target_id)->get('filename')->value);
          $logo1_html = '<div class="logo" style="background: url(' . $base_url . $logo1 . ')"></div>';
          $dataProduct['priceHtml'] = str_replace('@logo1', $logo1_html, $dataProduct['priceHtml']);
        }
        
        if ($product->get('logo2')->target_id) {
          $logo2 = rawurlencode(File::load($product->get('logo2')->target_id)->get('filename')->value);
          $logo2_html = '<div class="logo" style="background: url(' . $base_url . $logo2 . ')"></div>';
          $dataProduct['priceHtml'] = str_replace('@logo2', $logo2_html, $dataProduct['priceHtml']);
        }

        $productData['productId'] = $this->formatField('productId', $product->get('id_service')->value);
        $productData['productName'] = $this->formatField('productName', $product->get('name_service')->value);
        $productData['category'] = $this->formatField('category', $product->get('category')->value);
        $productData['price'] = $this->formatField('price', [
          'value' => $dataProduct['price'],
          'formattedValue' => $dataProduct['priceHtml'],
        ]);

        $productData['status'] = $this->formatField('status', $dataProduct['isActive']);
        $productData['outstanding'] = $this->formatField('outstanding', $product->get('outstanding')->value);

        if ($product->get('outstanding')->value && !$dataProduct['isActive']) {
          $bannerUrl = rawurlencode(File::load($product->get('banner_outstanding')->target_id)->get('filename')->value);
        }

        $productData['banner'] = $this->formatField('banner', $bannerUrl);
        $productData['bannerResponsive'] = $this->formatField('banner', [
          'value' => $bannerResponsiveUrl,
          'show' => TRUE,
        ]);

        if ($dataProduct['isActive'] && $visitLink != '') {
          $productData['visitLink'] = $this->formatField('visitLink', [
            'value' => $visitLink,
            'show' => TRUE,
          ]);
        }
        else {
          $productData['visitLink'] = $this->formatField('visitLink', [
            'value' => $visitLink,
            'show' => FALSE,
          ]);
        }

        if ($product->get('outstanding')->value) {
          $outstandingProduct = $productData;
        }
        else {
          $productList[] = ['data' => $productData, 'order' => $product->get('order')->value];
        }
      }
    }

    array_multisort(array_column($productList, 'order'), SORT_ASC, $productList);
    $productList = array_column($productList, 'data');

    if (!is_null($outstandingProduct)) {
      array_unshift($productList, $outstandingProduct);
    }

    if (count($productList) > 0){
      $data = ['productList' => $productList];
    }
    else {
      $data = ['productList' => [], 'noData' => ['value' => 'empty']];
    }
    return $data;
  }
}
