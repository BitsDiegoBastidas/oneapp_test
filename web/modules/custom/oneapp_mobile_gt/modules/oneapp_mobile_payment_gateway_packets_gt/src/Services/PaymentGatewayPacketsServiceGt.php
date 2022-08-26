<?php

namespace Drupal\oneapp_mobile_payment_gateway_packets_gt\Services;

use Drupal\oneapp_mobile_payment_gateway_packets\Services\PaymentGatewayPacketsService;

/**
 * Class PaymentGatewayPacketsServiceGt.
 */
class PaymentGatewayPacketsServiceGt extends PaymentGatewayPacketsService {


  /**
   * Obtiene el listado de paquetes.
   */
  public function getPacketsList($id) {
    return $this->manager
      ->load('oneapp_mobile_payment_gateway_packets_gt_v2_0_packets_list_endpoint')
      ->setParams(['id' => $id])
      ->setHeaders([])
      ->setQuery([])
      ->sendRequest();

  }
}
