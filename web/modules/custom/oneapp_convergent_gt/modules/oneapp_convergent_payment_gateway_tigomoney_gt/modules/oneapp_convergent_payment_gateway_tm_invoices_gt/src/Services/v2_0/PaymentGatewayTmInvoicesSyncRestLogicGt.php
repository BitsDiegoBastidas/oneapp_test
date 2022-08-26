<?php

namespace Drupal\oneapp_convergent_payment_gateway_tm_invoices_gt\Services\v2_0;

use Drupal\oneapp_convergent_payment_gateway_tm_invoices\Services\v2_0\PaymentGatewayTmInvoicesSyncRestLogic;

/**
 * Class PaymentGatewayTmInvoicesSyncRestLogic.
 */
class PaymentGatewayTmInvoicesSyncRestLogicGt extends PaymentGatewayTmInvoicesSyncRestLogic {

  /**
   * Start (Initialize the payment process).
   */
  public function start($id, $id_type, $business_unit, $product_type, $params) {
    $this->mobileUtils = \Drupal::service('oneapp.mobile.utils');
    $balance = $this->utilsPayment->getBalance($id, $id_type, $business_unit, $params);
    $balance['additionalData']['payerAccount'] = $this->getPayerAccount($params, $id);
    $balance['additionalData']['invoiceId'] = isset($balance["invoiceId"]) ? $balance["invoiceId"] : '';
    if (isset($params["isPartialPayment"]) && !$params["isPartialPayment"]) {
      $params['amount'] = $balance["dueAmount"];
    }
    if ($balance['dueAmount'] != $params['amount'] && !$params['isPartialPayment']) {
      throw new \Exception("El monto es incorrecto");
    }
    if ($balance["dueAmount"] <= 0 && !$params['isPartialPayment']) {
      throw new \Exception("No se pueden pagar facturas con deuda 0 o con un valor negativo");
    }
    if (isset($balance["noData"]["value"]) && $balance["noData"]["value"] == "empty") {
      throw new \Exception("No se pueden pagar facturas con deuda 0 o con un valor negativo");
    }
    $amount = ($params["isPartialPayment"] && !isset($balance['amountForPartialPayment'])) ? $params["amount"] : $balance["dueAmount"];
    $id = $this->modifyMsisdnForPayment($id);
    $balance["accountNumber"] = $id;
    $fields = [
      'uuid' => $this->tokenAuthorization->getUserIdPayment(),
      'accountId' => $id,
      'accountNumber' => !empty($balance["accountNumber"]) ? $balance["accountNumber"] : $id,
      'accountType' => $business_unit,
      'productType' => $product_type,
      'amount' => $amount,
      'isPartialPayment' => $params['isPartialPayment'] ? 1 : 0,
      'numberReference' => 0,
      'accessType' => $this->tokenAuthorization->getAccessType(),
    ];
    if (isset($balance['additionalData']) && (!empty($balance['additionalData']))) {
      if (isset($balance['period'])) {
        $balance['additionalData']['period'] = $balance['period'];
      }
      elseif(isset($balance['endPeriod'])) {
        $balance['additionalData']['period'] = $balance['endPeriod'];
      }
      elseif(isset($balance['dueDate'])) {
        $balance['additionalData']['period'] = $balance['dueDate'];
      }
      $fields['additionalData'] = serialize($balance['additionalData']);
    }
    $transaction_id = $this->transactions->initTransaction($fields, $product_type);
    $purchaseorder_id = $this->transactions->encryptId($transaction_id);

    $response = [
      'purchaseorderId' => $purchaseorder_id,
      'dueAmount' => $amount,
      'invoiceId' => $balance["invoiceId"],
      'accountNumber' => !empty($balance["accountNumber"]) ? $balance["accountNumber"] : $id,
      'accountId' => $id,
      'payerAccount' => $balance['additionalData']['payerAccount'],
      'isMultipay' => (isset($balance["multipay"]) && $balance["multipay"]) ? TRUE : FALSE,
      'productType' => $this->config['fields']['productType']['value'],
    ];
    if (isset($balance['period'])) {
      $response['period'] = $balance['period'];
    }
    elseif(isset($balance['endPeriod'])) {
      $response['period'] = $balance['endPeriod'];
    }
    elseif(isset($balance['dueDate'])) {
      $response['period'] = $balance['dueDate'];
    }
    return $response;
  }
}
