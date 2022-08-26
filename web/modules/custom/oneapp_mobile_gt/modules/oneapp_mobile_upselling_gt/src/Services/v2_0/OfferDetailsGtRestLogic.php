<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\OfferDetailsRestLogic;

/**
 * Class OfferDetailsGtRestLogic.
 */
class OfferDetailsGtRestLogic extends OfferDetailsRestLogic {

  /**
   * {@inheritdoc}
   */
  protected $queryParams;

  /**
   * {@inheritdoc}
   */
  public function get($msisdn, $offer_id) {
    $offer = NULL;
    try {
      $billing_type = $this->getBalance($msisdn)->typeClient;
    }
    catch (HttpException $exception) {
      $billing_type = NULL;
    }
    if ($billing_type != NULL) {
      $prepaid = ($billing_type == 'PREPAGO' || $billing_type == 'KIT') ? TRUE : FALSE;
      $this->queryParams = (!$prepaid) ? TRUE : FALSE;
      $offer = $this->getOffer($msisdn, $offer_id);
    }
    if ($offer != NULL) {
      $value = [
        'amount' => isset($offer->price) ? $offer->price : '',
        'currencyId' => \Drupal::service('oneapp.utils')->getCurrencyCode(TRUE),
      ];
      $data = [
        'offerId' => isset($offer->packageId) ? $offer->packageId : '',
        'type' => isset($offer->type) ? $offer->type : '',
        'cost' => [$value],
        'name' => isset($offer->name) ? $offer->name : '',
        'description' => isset($offer->description) ? $offer->description : '',
        'category' => isset($offer->category) ? $offer->category : '',
        'creditPackageCategory' => isset($offer->creditPackageCategory) ? $offer->creditPackageCategory : '',
        'validityNumber' => isset($offer->validityNumber) ? $offer->validityNumber : '',
        'validityType' => isset($offer->validityType) ? $offer->validityType : '',
        'additionalData' => [
          'creditPackagePrice' => isset($offer->creditPackagePrice) ? $offer->creditPackagePrice : '',
          'creditPackagePromotion' => isset($offer->creditPackagePromotion) ? $offer->creditPackagePromotion : '',
          'acquisitionMethods' => [
            [
              'paymentMethodName' => 'TIGOMONEY',
              'cost' => [$value]
            ]
          ],
        ],
      ];
    }
    else {
      $data = [
        'error' => [
          'code' => '404 not found',
          'description' => 'No se encontró la oferta.',
        ],
      ];
    }
    return $data;
  }

  /**
   * Implements getAvailableOffers.
   *
   * @param string $msisdn
   *   Msisdn value.
   * @param string $offer_id
   *   OfferId value.
   *
   * @return ResponseInterface
   *   The HTTP response object.
   *
   * @throws \ReflectionException
   */
  public function getOffer($msisdn, $offer_id) {
    $product = NULL;
    try {
      if ($this->queryParams) {
        $offers = $this->manager
          ->load('oneapp_mobile_upselling_v1_0_available_offers_postpaid_endpoint')
          ->setHeaders([])
          ->setQuery([
            'category' => 'TURBOBUTTONS',
          ])
          ->setParams(['msisdn' => $msisdn])
          ->sendRequest();
      }
      else {
        $offers = $this->manager
          ->load('oneapp_mobile_upselling_v2_0_available_offers_endpoint')
          ->setHeaders([])
          ->setQuery([])
          ->setParams(['msisdn' => $msisdn])
          ->sendRequest();
      }
      if ($offers->products != []) {
        foreach ($offers->products as $offer) {
          if ($offer->packageId == $offer_id) {
            return $offer;
          }
        }
      }
      return $product;
    }
    catch (HttpException $exception) {
      throw $exception;
    }
    return $product;
  }

  /**
   * Implements getCurrentPlan.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return mixed
   *   Msisdn value.
   *
   * @throws \ReflectionException
   */
  public function getBalance($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      throw $exception;
    }
  }

}
