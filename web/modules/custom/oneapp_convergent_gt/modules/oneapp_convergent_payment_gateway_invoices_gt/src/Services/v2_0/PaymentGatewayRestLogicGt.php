<?php

namespace Drupal\oneapp_convergent_payment_gateway_invoices_gt\Services\v2_0;

use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use Drupal\key\Exception\KeyValueNotSetException;
use Drupal\oneapp_convergent_payment_gateway_invoices\Services\v2_0\PaymentGatewayRestLogic;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Exception;

/**
 * Class PaymentGatewayRestLogicGt
 */
class PaymentGatewayRestLogicGt extends PaymentGatewayRestLogic {

  /**
   * @inheritdoc
   */
  public function start($id, $id_type, $business_unit, $product_type, $params) {
    $this->isB2b($id);
    $balance = $this->utilsPayment->getBalance($id, $id_type, $business_unit, $params);

    if (isset($params["isPartialPayment"]) && !$params["isPartialPayment"]) {
      $params['amount'] = $balance["dueAmount"];
    }
    if ($balance['dueAmount'] != $params['amount'] && !$params['isPartialPayment']) {
      throw new \Exception("El monto es incorrecto");
    }
    if ($balance["dueAmount"] <= 0 && !$params['isPartialPayment']) {
      throw new \Exception("No se pueden pagar facturas con deuda 0 o con un valor negativo");
    }
    $amount = ($params["isPartialPayment"] && !$balance['amountForPartialPayment']) ? $params["amount"] : $balance["dueAmount"];
    $account_number_invoice_payment = $this->utilsPayment->getAccountNumberForPaymentGatewayFromToken($business_unit, $id);
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
      $balance['additionalData']['accountNumberInvoicePayments'] = $account_number_invoice_payment;
      $fields['additionalData'] = serialize($balance['additionalData']);
    }
    else {
      $additional_data = new \Stdclass();
      $additional_data->period = $balance['period'];
      $additional_data->paymentMethod = $params['paymentMethodName'] ?? 'creditCard';
      $additional_data->accountNumberInvoicePayments = $account_number_invoice_payment;
      $fields['additionalData'] = serialize($additional_data);
    }
    $transaction_id = $this->transactions->initTransaction($fields, $product_type);
    $finger_print = $this->getAttachments($additional_data->paymentMethod, $id, $business_unit, $product_type, $transaction_id);
    $purchaseorder_id = $this->transactions->encryptId($transaction_id);
    $response = [
      'fingerPrint' => $finger_print,
      'purchaseorderId' => $purchaseorder_id,
      'dueAmount' => $amount,
      'invoiceId' => $balance["invoiceId"],
      'accountNumber' => !empty($balance["accountNumber"]) ? $balance["accountNumber"] : $id,
      'accountId' => $id,
      'isMultipay' => (isset($balance["multipay"]) && $balance["multipay"]) ? TRUE : FALSE,
      'productType' => t('Pago de factura'),
    ];
    if (isset($balance['period'])) {
      $response['period'] = $balance['period'];
    }
    if ($response['isMultipay']) {
      $config_app = (object) $this->tokenAuthorization->getApplicationSettings('configuration_app');
      $response['applicationName'] = $config_app->setting_app_payment['applicationNameMultipay'];
    }
    return $response;
  }

  public function getAttachments($payment_method, $id, $business_unit, $product_type, $transaction_id) {
    $attachments = null;

    if (strtolower($payment_method) == 'creditcard') {
      try {
        $attachments = $this->utilsPayment->getIngenicoAttachments($id, $business_unit, $product_type, $transaction_id);
      } catch (\Exception $e) {
          throw new \Exception(t('Ha ocurrido un error durante la inicialización de la transacción'), 404);
      }
    } else {
      $attachments = $this->utilsPayment->getAttachments($id, $business_unit, $product_type, $transaction_id);
    }

    return $attachments;
  }

  public function generateOrderId($business_unit, $product_type, $id_type, $id, $purchaseorder_id) {
    $this->isB2b($id);
    $this->params['uuid'] = $this->tokenAuthorization->getUserIdPayment();
    $this->params['tokenUuId'] = $this->tokenAuthorization->getTokenUuid();

    $config_value_default = (object) \Drupal::config('oneapp_convergent_payment_gateway.config')->get('fields_default_values');
    $email_default = $config_value_default->email["send_default_value_email"] ? $config_value_default->email["email_default_value"] : '';

    // login/he/otp/phonePass
    // Email = getEmail(), params['email'] , $email_default.
    $this->params['email'] = (!empty($this->tokenAuthorization->getEmail())) ? $this->tokenAuthorization->getEmail() :
      ((!empty($this->params['email'])) ? $this->params['email'] : $email_default);

    if (!$this->tokenAuthorization->isHe()) {
      $this->params['customerNameToken'] =
        $this->tokenAuthorization->getGivenNameUser() . " " . $this->tokenAuthorization->getFirstNameUser();
    }

    $config_app = $this->tokenAuthorization->getApplicationSettings('configuration_app');
    $this->params['apiHost'] = $config_app["setting_app_payment"]["api_path"];
    if (isset($config_app["setting_app_payment"]["aws_service"])) {
      $this->params['aws_service'] = $config_app["setting_app_payment"]["aws_service"];
    }
    if (empty($this->params['street']) && !empty($this->params['address'])) {
      $this->params['street'] = $this->params['address'];
    }
    $decrypt_purchaseorder_id = $this->transactions->decryptId($purchaseorder_id);
    $data_transaction = $this->transactions->getTransactionById($decrypt_purchaseorder_id);
    if ($data_transaction->stateOrder != "INITIALIZED") {
      throw new \Exception(t('Este pago ya fue realizado'), Response::HTTP_BAD_REQUEST);
    }
    $additional_data = $this->setAdditionalData($data_transaction);
    $additional_data['enrollMe'] = $this->params['enrollMe'] ?? FALSE;
    $data_transaction->additionalData = serialize($additional_data);
    $is_multipay = (isset($additional_data['isMultipay']) && $additional_data['isMultipay']) ? TRUE : FALSE;
    $additional_data_for_payment_body = (isset($additional_data['fieldsForPaymentBody'])) ? $additional_data['fieldsForPaymentBody'] : [];
    if ($is_multipay) {
      $additional_data_for_payment_body['applicationName'] = $config_app["setting_app_payment"]['applicationNameMultipay'];
    }
    $body = $this->
      getBodyPayment($additional_data['paymentMethod'], $business_unit, $product_type, $id_type, $id, $purchaseorder_id, $this->params, $additional_data_for_payment_body);

    $this->addAdditionalPaymentInformation($body, $config_app);
    $order_id = $this->utilsPayment
      ->generateOrderId($body, $business_unit, $product_type, $this->params, $is_multipay);
    $fields = [
      'stateOrder' => "ORDER_IN_PROGRESS",
      'changed' => time(),
      'orderId' => $order_id->body->orderId,
      'transactionId' => $order_id->body->transactionId,
      'additionalData' => $data_transaction->additionalData,
    ];
    $this->transactions->updateDataTransaction($decrypt_purchaseorder_id, $fields);
    $body_logs = $body;
    $body_logs['creditCardDetails'] = [];
    $body_logs['sendCvvEmpty'] = isset($this->params['cvv']) & !empty($this->params['cvv']) ? TRUE : FALSE;
    $fields_log = [
      'purchaseOrderId' => $decrypt_purchaseorder_id,
      'message' => "Order in progress",
      'codeStatus' => 200,
      'operation' => $this->transactions::CREATED_ORDER,
      'description' => "Back office response: \n" . json_encode($order_id->body, JSON_PRETTY_PRINT) .
        "\nBody: \n" . json_encode($body_logs, JSON_PRETTY_PRINT),
      'type' => $product_type,
    ];
    $this->transactions->addLog($fields_log);

    return $order_id->body;
  }

  public function getBodyPayment($payment_method, $business_unit, $product_type, $id_type, $id, $purchaseorder_id, $params, $additional_data) {
    $body = null;
    if (strtolower($payment_method) == 'creditcard') {
      $body = $this->utilsPayment
        ->getIngenicoPaymentBody($business_unit, $product_type, $id_type, $id, $purchaseorder_id, $this->params, $additional_data);
    } else {
      $body = $this->utilsPayment
        ->getBodyPayment($business_unit, $product_type, $id_type, $id, $purchaseorder_id, $this->params, $additional_data);
    }
    return $body;
  }
}
