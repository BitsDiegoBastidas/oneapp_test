<?php

namespace Drupal\oneapp_mobile_premium_gt\Services;

use Drupal\oneapp_mobile_premium\Services\PremiumServiceChannel;

/**
 * Class PremiumServiceChannelGt.
 *
 * @package Drupal\oneapp_mobile_premium_ni\Services;
 */
class PremiumServiceChannelGt extends PremiumServiceChannel {

  /**
   * Obtener el billing id de la linea
   */
  protected function getBillingId($id) {
    try {
      $exists_mobile_utils = \Drupal::hasService('oneapp.mobile.utils');
      if ($exists_mobile_utils) {
        $oneapp_mobile_utils = \Drupal::service('oneapp.mobile.utils');
        $data = $oneapp_mobile_utils->getInfoTokenByMsisdn($id);
        if (!is_null($data)) {
          return $data['billingAccountId'];
        }
      }

      $current_plan = $this->callCurrentPlansApi($id);
      return $current_plan[0]->billingAccountId;
    }catch (\Exception $e) {
      $current_plan = $this->callCurrentPlansApi($id);
      return $current_plan[0]->billingAccountId;
    }
  }

  /**
   * Call api SetUpsellingTV.
   */
  protected function callCurrentPlansApi($id) {
    return $this->manager
      ->load('oneapp_mobile_premium_v2_0_current_plans_endpoint')
      ->setParams(['id' => $id])
      ->setHeaders([])
      ->setQuery([])
      ->sendRequest();
  }

  /**
   * Subscribe to channel.
   */
  public function subscribe($id, $product, $payload, $available_offers, $active_offers) {
    try {
      $offer_id = parent::getType($product, $available_offers, $active_offers)['offerId'];
      $data_entity = parent::getDataEntity($product, $available_offers, $active_offers);
      $billing_id = $this->getBillingId($id);


      $body = [
        "msisdn" => $this->getIdWithPrefix($id),
        "packageId" => $offer_id,
      ];

      $this->callSetUpsellingTVApi($billing_id, $body);

      $data_entity['textCardConfirmActivation'] = $data_entity['textCardConfirmActivation'];

      return $data_entity;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Call api SetUpsellingTV.
   */
  protected function callSetUpsellingTVApi($id, $offering_id) {
    return $this->manager
      ->load('oneapp_mobile_premium_v2_0_set_uppselling_tv_endpoint')
      ->setParams(['id' => $offering_id['msisdn'], 'offeringId' =>$offering_id['packageId']])
      ->setHeaders(['Content-Type'=>'application/json'])
      ->setBody([])
      ->sendRequest();
  }

  /**
   * Unsubscribe to channel.
   */
  public function unsubscribe($id, $product, $payload, $available_offers, $active_offers) {
    try {
      $offer_id = $this->getType($product, $available_offers, $active_offers)['offerId'];
      //buscar el id de la suscripcion
      $suscription_id = null;
      foreach ($active_offers as $offer) {
        if ($offer["offeringId"] == $offer_id) {
          $suscription_id = $offer["offer"]->productId;
          break;
        }
      }
      if (!is_null($suscription_id)) {
        $body = [
          "msisdn" => $this->getIdWithPrefix($id),
          "packageId" => $offer_id,
        ];

        $response = $this->callUnSetUpsellingTVApi($id, $body);

        $data_entity['bodyText'] = $product->get('text_card_confirm_deactivation')->value;

        return $data_entity;
      }

      return NULL;
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * Call api UnSetUpsellingTV.
   */
  protected function callUnSetUpsellingTVApi($id, $offering_id) {
    return $this->manager
      ->load('oneapp_mobile_premium_v2_0_unset_uppselling_tv_endpoint')
      ->setParams(['id' => $offering_id['msisdn'], 'offeringId' =>$offering_id['packageId']])
      ->setHeaders([])
      ->setQuery([])
      ->sendRequest();
  }

  /**
   * Get id with prefix.
   */
  protected function getIdWithPrefix($id) {
    $config = \Drupal::config('oneapp_endpoints.settings')->getRawData();
    $prefix_country = $config['prefix_country'];
    if (substr($id, 0, strlen($prefix_country)) != $prefix_country) {
      $id = $prefix_country . $id;
    }

    return $id;
  }


}
