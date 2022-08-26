<?php


namespace Drupal\oneapp_convergent_payment_gateway_tigomoney_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_convergent_payment_gateway_tigomoney\Services\v2_0\ValidityTigomoneyAccountService;

/**
 * Class ValidityTigomoneyAccountService.
 *
 * @package Drupal\oneapp_convergent_payment_gateway_tigomoney\Services;
 */
class ValidityTigomoneyAccountServiceGt extends ValidityTigomoneyAccountService {

  /**
   * {@inheritdoc}
   */
  public function hasAccountTigoMoney() {
    // Función que devuelve si tiene cuenta en tigoMoney
    // y la url de redireccionamiento en dependencia de si existe cuenta o no.
    try {
      $valid_account_tigo_money = $this->manager
        ->load('oneapp_convergent_payment_gateway_tigomoney_v2_0_mobile_validate_account_endpoint')
        ->setParams([
          'msisdn' => $this->primaryNumber['accountId'],
        ])
        ->setHeaders([])
        ->setQuery([])
        ->setBody([])
        ->sendRequest();
    }
    catch (HttpException $e) {
      if ($e->getCode() === 404) {
        $valid_account_tigo_money = FALSE;
      }
    }
    if (isset($valid_account_tigo_money->subscriber->status) && $valid_account_tigo_money->subscriber->status == "Success") {
      $url = $this->configuration['urlRedirect']['webviewTigoMoney'];
      return [
        'value' => TRUE,
        'url' => $url,
      ];
    }
    else {
      $url = $this->configuration['urlRedirect']['createAccountTigoMoney'];
      return [
        'value' => FALSE,
        'url' => $url,
      ];
    }
  }

}
