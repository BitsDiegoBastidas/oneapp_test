<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\AvailableOffersServices;

/**
 * AvailableOffersGtServices class
 */
class AvailableOffersGtServices extends AvailableOffersServices {


  public function getAvailableOfferByMsisdn($msisdn, $is_postpaid = null, $category = 'TURBOBUTTONS') {
    try {
      if (!$is_postpaid) {
        // Loading Enpoint: Mobile upselling available offers v2.0
        $this->manager->load('oneapp_mobile_upselling_v2_0_available_offers_endpoint');
      }
      else {
        // Loading Enpoint: Mobile upselling available offers postpaid endpoint V1.0
        $this->manager->load('oneapp_mobile_upselling_v1_0_available_offers_postpaid_endpoint');
      }

      // Get Query Params From Endpoint Config
      $query_string = parse_url($this->manager->getEntity()->getEndpointReplaced())['query'] ?? '';
      parse_str($query_string, $query_params);
      if (!empty($category)) {
        $query_params['category'] = $category;
      }

      return $this->manager
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders([])
        ->setQuery($query_params ?? [])
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
   * The Method get the available roaming products offers
   * according to JIRA ONEAPP-8192
   *
   * @param $msisdn
   * @param $billing_type
   * @return array
   */
  public function getAvailableRoamingOffers($msisdn, $billing_type) {

    $roaming_offers = [];

    if ($billing_type == 'hybrid') {
      $categories = [64, 67];
      $channel_id = 63;
    }
    elseif ($billing_type == 'postpaid') {
      $categories = [64];
      $channel_id = 63;
    }
    elseif ($billing_type == 'prepaid') {
      $categories = [66, 67];
      $channel_id = 70;
    }
    else {
      return $roaming_offers;
    }

    foreach ($categories as $category) {
      $query_params = ['channelId' => $channel_id, 'categoryId' => $category, 'packageType' => 'all'];
      $roaming_offers[$category] = $this->getAvailableRoamingProducts($msisdn, $query_params);
    }

    return $this->formatAvailableRoamingOffers($roaming_offers);
  }

  /**
   * @param $msisdn
   * @param array $query_params ['channelId' => '', 'categoryId' => '', 'packageType' => '']
   * @return object
   */
  public function getAvailableRoamingProducts($msisdn, $query_params = [])
  {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_available_roaming_products_endpoint')
        ->setHeaders([])
        ->setBody([])
        ->setParams(['msisdn' => $msisdn])
        ->setQuery($query_params)
        ->sendRequest();
    } catch (\Drupal\oneapp\Exception\HttpException $exception) {
      return null;
    }
  }

  public function getSuggestedProducts($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v1_0_available_offers_suggested_products_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      return null;
    }
  }



  public function formatAvailableRoamingOffers($roaming_offers) {
    $formatted = [];
    foreach ($roaming_offers as $key => $product) {
      $subscriptions = $product->subscriptionList ?? [];
      foreach ($subscriptions as $i => $subscription) {
        // These categories must be created in http://.../admin/config/mobile_offers_category_entity
        switch (strtolower($subscription->packageType)) {
          case 'pasaporte':
            $category = 'cobertura_adicional';
            break;
          case 'paquete':
            $category = 'velocidad_adicional';
            break;
          case 'addon':
            $category = 'minutos_adicionales_roaming';
            break;
        }
        $formatted[] = (object) [
          'type' => 'ROAMING',
          'packageId' => $subscription->packageCode,
          'name' => $subscription->packageName,
          'description' => $subscription->packageName,
          'price' => $subscription->packagePrice,
          'category' => $category ?? '',
          'subcategory' => $category ?? '',
          'validityType' => $subscription->packageTimeUnit,
          'validityNumber' => $subscription->packageTime,
          'packageType' => $subscription->packageType
        ];
      }
    }
    return $formatted;
  }

  /**
   * Return endpoint response
   * https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/atpa/subscribers/{msisdn}
   *
   * @param $msisdn
   * @return object|null
   */
  public function getAtpaInfoByMsisdn($msisdn)
  {
    try {
      return $this->manager->load('oneapp_mobile_upselling_v1_0_details_by_msisdn_endpoint')
        ->setHeaders([])
        ->setParams(['msisdn' => $msisdn])
        ->setQuery([])
        ->setBody([])
        ->sendRequest();
    }
    catch (\Exception $e) {
      return null;
    }
  }
}
