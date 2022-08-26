<?php

namespace Drupal\oneapp_mobile_premium_gt\Services;

use Drupal\oneapp_mobile_premium\Services\PremiumService;

/**
 * Class PremiumServiceGt.
 *
 * @package Drupal\oneapp_mobile_premium_gt\Services;
 */
class PremiumServiceGt extends PremiumService {

  /**
   * {@inheritdoc}
   */
  public function getAvailableOffers($id) {

    $headers = ['channel' => 'amazon'];
    $query = ['category' => '59'];

    try {
      $id = $this->getIdWithoutPrefix($id);
      $available_offers_api = $this->callGetAvailableOffersApi($id, $headers, $query);
      $available_offers = [];
      if (!empty($available_offers_api->products)) {
        foreach ($available_offers_api->products as $available_offer) {
          if (isset($available_offer->packageId)) {
            $available_offers[] = [
              'offeringId' => $available_offer->packageId,
              'offer' => $available_offer,
            ];
          }
        }
      }
    }
    catch (\Exception $exception) {

      $get_response = $exception->getResponse();
      $get_response->getBody()->seek(0);
      $error_content = isset($get_response) ? $get_response->getBody()->getContents() : NULL;
      $error = isset($error_content) ? json_decode($error_content, TRUE) : [];

      if (isset($error['code']) && $error['code'] == '404') {
        return ['noData' => 'empty'];
      }
      else {
        return [];
      }
    }

    return $available_offers;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveOffers($id) {

    $headers = ['channel' => 'amazon'];
    $id = $this->getIdWithoutPrefix($id);
    $active_offers_api = $this->callGetActiveOffersApi($id, $headers);

    $active_offers = [];
    if (!empty($active_offers_api->subscriptions)) {
      foreach ($active_offers_api->subscriptions as $product) {
        if (isset($product->productId)) {
          $active_offers[] = [
            'offeringId' => $product->productId,
            'offer' => $product,
          ];
        }
      }
    }
    return $active_offers;
  }

  /**
   * {@inheritdoc}
   */
  public function callGetAvailableOffersApi($id, $headers = [], $query = []) {

    return $this->manager
      ->load('oneapp_mobile_premium_v2_0_available_offers_endpoint')
      ->setParams(['id' => $id])
      ->setHeaders($headers)
      ->setQuery($query)
      ->sendRequest();
  }

  /**
   * elimina las ofertas en available que tambien llegan en el api de productos activos
   */
  public function filterAvailableOffers($active_offers=[], $available_offers=[]) {
      $result = [];
      $actives = array_column($active_offers, 'offeringId');
      foreach ($available_offers as $key => $value) {
        if (!in_array($value['offeringId'], $actives)) {
          $result[] = $value;
        }
      }

      return $result;
  }

}
