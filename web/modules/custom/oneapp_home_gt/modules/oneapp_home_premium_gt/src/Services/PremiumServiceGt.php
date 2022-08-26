<?php

namespace Drupal\oneapp_home_premium_gt\Services;

use Drupal\oneapp_home_premium\Services\PremiumService;
use Drupal\oneapp\Exception\HttpException;

/**
 * Class PremiumService.
 *
 * @package Drupal\oneapp_home_premium\Services;
 */
class PremiumServiceGt extends PremiumService {

  /**
   * Get getAvailableOffers
   */

  public function getAvailableOffers($id) {

    try {
      $id = $this->getIdWithoutPrefix($id);
      $query = ['category' => '59'];
      $available_offers_api = $this->callGetAvailableOffersApi($id, 2, $query);
      $available_offers = [];

      foreach ($available_offers_api as $availableOffer) {
        if (is_array($availableOffer) || is_object($availableOffer)) {
          foreach ($availableOffer as $products) {
            $package_id = isset($products->packageId) ? $products->packageId : 0;

            if ($package_id !== 0 && $package_id !== '') {
              $available_offers[] =
              [
                'offeringId' => $products->packageId,
                'offer' => (array) $products,
              ];
            }
          }
        }
      }
    }
    catch (HttpException $exception) {
      $code = $exception->getCode();
      if ($code == '404') {
        return ['noData' => 'empty'];
      }
      else {
        return [];
      }
    }

    return $available_offers;
  }

  /**
   * Get getActiveOffers
   */

  public function getActiveOffers($id) {

    try {

      $id = $this->getIdWithoutPrefix($id);
      $active_offers_api = $this->callGetActiveOffersApi($id);

      $active_offers = [];

      foreach ($active_offers_api as $activeOffer) {
        if (is_array($activeOffer) || is_object($activeOffer)) {
          foreach ($activeOffer as $dataoffer => $offer) {
            if (isset($offer->productId) && $offer->productId !== 0 && $offer->productId !== '') {
              $active_offers[] =
              [
                'offeringId' => $offer->productId,
                'offer' => (array) $offer,
              ];
            }
          }
        }
      }

      return $active_offers;

    } catch (\Exception $e) {
      $this->errorActiveOffers = t('En este momento no podemos obtener información de productos contratados, intenta de nuevo más tarde.');
    }

  }

/**
 * GetAvailableOffersApi.
 *
 */
  public function callGetAvailableOffersApi($id, $product_id, $query = []) {

      $data = $this->manager
        ->load('oneapp_home_premium_v2_0_available_offers_endpoint')
        ->setParams(['id' => $id, 'productId' => $product_id])
        ->setHeaders([])
        ->setQuery($query)
        ->sendRequest();
      return $data;

  }



}
