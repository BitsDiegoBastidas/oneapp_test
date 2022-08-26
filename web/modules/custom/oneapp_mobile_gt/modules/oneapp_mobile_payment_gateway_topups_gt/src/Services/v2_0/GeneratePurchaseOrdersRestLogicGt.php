<?php

namespace Drupal\oneapp_mobile_payment_gateway_topups_gt\Services\v2_0;

use Symfony\Component\HttpFoundation\Response;
use Drupal\oneapp_mobile_payment_gateway_topups\Services\v2_0\GeneratePurchaseOrdersRestLogic;

/**
 * Declare custom class for topups GT.
 */
class GeneratePurchaseOrdersRestLogicGt extends GeneratePurchaseOrdersRestLogic {

  /**
   * Genera la orden.
   */
  public function generateOrderId($business_unit, $product_type, $id_type, $id, $purchaseorder_id) {
    $this->params['uuid'] = $this->token->getUserIdPayment();
    $this->params['tokenUuId'] = $this->token->getTokenUuid();
    // Orden de prioridad para correo a utilizar.
    $this->params['email'] = (empty($this->token->getEmail()) && isset($this->params['email'])) ? $this->params['email'] : $this->token->getEmail();
    if (!$this->token->isHe()) {
      $this->params['customerNameToken'] = $this->token->getGivenNameUser() . " " . $this->token->getFirstNameUser();
    }else {
      $this->params['email'] = isset($this->params['email']) ? trim($this->params['email']) : 'null@cybersource.com';
    }
    $config_app = $this->utilsPaymentConvergent->getConfigPayment($product_type, 'configuration_app', $business_unit);
    $this->params['apiHost'] = $config_app->setting_app_payment['api_path'];
    $this->params['aws_service'] = isset($config_app->setting_app_payment["aws_service"]) ? $config_app->setting_app_payment["aws_service"] : 'payment';
    $decrypt_purchaseorder_id = $this->transactions->decryptId($purchaseorder_id);
    $data_transaction = $this->transactions->getTransactionById($decrypt_purchaseorder_id, []);
    $additional_data = (object)unserialize($data_transaction->additionalData);
    $payment_method = $additional_data->paymentMethod ?? 'bancard';
    if ($data_transaction->stateOrder != "INITIALIZED") {
      throw new \Exception(t('Este pago ya fue realizado'), Response::HTTP_BAD_REQUEST);
    }
    $documentNumber = (isset($this->params['billingData']['nit']) && (strlen($this->params['billingData']['nit']) > 0)) ? strtoupper(trim($this->params['billingData']['nit'])) : "CF";
    if (isset($this->params['billingData'])) {
      $this->params['billingData']['nit'] = $documentNumber;
      if (isset($this->params['billingData']['address']) && (strlen($this->params['billingData']['address']) > 0)) {
        $this->params['street'] = trim($this->params['billingData']['address']);
      }
      $this->params['email'] = (isset($this->params['billingData']['email']) && (strlen($this->params['billingData']['email']) > 0)) ? trim($this->params['billingData']['email']) : trim($this->params['email']);
      if (isset($this->params['billingData']['fullname']) && (strlen($this->params['billingData']['fullname']) > 0)) {
        if (isset($this->params['customerNameToken']) && isset($this->params['tokenizedCardId'])) {
          $this->params['customerNameToken'] = ucwords(strtolower(trim($this->params['billingData']['fullname'])));
        }
        else {
          $this->params['customerName'] = ucwords(strtolower(trim($this->params['billingData']['fullname'])));
        }
      }
      $type = $this->utilsPaymentConvergent->saveBillingData($this->params['billingData']);
    }
    if (!isset($this->params['customerName']) || $this->params['customerName'] == "") {
      $this->params['customerName'] = "consumidor_final";
    }
    $id = $this->modifyMsisdn($id);
    $additionalData = [
      'documentNumber' => $documentNumber,
      'documentType' => "NIT",
      "purchaseDetails" => [
        [
          "name" => "CO0434",
          "quantity" => (string) $data_transaction->amount,
          "amount" => '1',
        ],
      ],
    ];
    $body = $this->getBodyPayment($payment_method, $business_unit, $product_type, $id_type, $id, $purchaseorder_id, $this->params, $additionalData);

    $order = $this->utilsPaymentConvergent
      ->generateOrderId($body, $business_unit, $product_type, $this->params);

    $this->transactions->updateDataTransactionOrderInProgress($decrypt_purchaseorder_id, $order);
    $bodyLogs = $body;
    $bodyLogs['creditCardDetails'] = [];
    $fieldsLog = [
      'purchaseOrderId' => $decrypt_purchaseorder_id,
      'message' => "Order in progress",
      'codeStatus' => 200,
      'operation' => $this->transactions::CREATED_ORDER,
      'description' => "Back office response: \n" . json_encode($order->body, JSON_PRETTY_PRINT) . "\nBody: \n" . json_encode($bodyLogs, JSON_PRETTY_PRINT),
      'type' => $product_type,
    ];
    $this->transactions->addLog($fieldsLog);
    return $order->body;
  }

  /**
   * 
   */
  public function start($id, $business_unit, $product_type, $params, $target_msisdn) {
    $is_blocked_payment_he = (bool) $this->config['he_otp']['disable']['value'];
    if ($is_blocked_payment_he && $this->token->isHe()) {
      return [
        'blocked' => [
          'message' => $this->config['he_otp']['disable']['message'],
          'actions' => [
            'label' => $this->config['he_otp']['disable']['button'],
            'show' => TRUE,
            'type' => "button",
          ],
        ],
      ];
    }
    $taxes = $this->checkAndSetTaxes($params);
    $id = $this->modifyMsisdn($id);
    if (isset($target_msisdn)) {
      $target_msisdn = $this->modifyMsisdn($target_msisdn);
    }
    $additional_data = new \Stdclass();
    $additional_data->originalAmount = $params['amount'];
    $payment_method = $params['paymentMethod'] ?? 'bancard';
    $additional_data->paymentMethod = $payment_method;
    $amount = ($taxes) ? $params['totalAmount'] : $params['amount'];
    if (is_float($amount)) {
      $amount = number_format($amount, 2, '.', '');
    }
    $fields = [
      'uuid' => $this->token->getUserIdPayment(),
      'accountId' => $id,
      'accountNumber' => (isset($target_msisdn)) ? $target_msisdn : $id,
      'accountType' => $business_unit,
      'productType' => $product_type,
      'amount' => $amount,
      'numberReference' => $this->getRechargeIdByAmount($amount),
      'additionalData' => serialize($additional_data),
      'accessType' => $this->token->getAccessType(),
    ];
    $transaction_id = $this->transactions->initTransaction($fields, $product_type);
    $finger_print = $this->getAttachments($payment_method, $id, $business_unit, $product_type, $transaction_id);
    $purchaseorder_id = $this->transactions->encryptId($transaction_id);
    $response = [
      'fingerPrint' => $finger_print,
      'purchaseorderId' => $purchaseorder_id,
      'amount' => $amount,
      'accountNumber' => (isset($target_msisdn)) ? $target_msisdn : $id,
    ];
    if ($taxes) {
      $response['taxes'] = $params['taxes'];
    }
    return $response;
  }

  /**
   * Return fingerprint attachemts by payment method
   *
   * @param string $payment_method
   * @param string $id
   * @param string $business_unit
   * @param string $product_type
   * @param array $params
   * @param mixed $transaction_id
   *
   * @return mixed attachments
   */
  public function getAttachments($payment_method, $id, $business_unit, $product_type, $transaction_id) {
    $finger_print = '';
    if ($payment_method == 'creditCard') {
      try {
        $finger_print = $this->utilsPaymentConvergent->getIngenicoAttachments($id, $business_unit, $product_type, $transaction_id);
      } catch (\Exception $e) {
          throw new \Exception(t('Ha ocurrido un error durante la inicialización de la transacción'), 404);
      }
    }
    else {
      $finger_print = $this->utilsPaymentConvergent->getAttachments($id, $business_unit, $product_type, $transaction_id);
    }
    return $finger_print;
  }

  /**
   * Return payment gateway body by payment method
   *
   * @param string $payment_method
   * @param string $business_unit
   * @param string $product_type
   * @param string $id_type
   * @param string $id
   * @param string $purchase_order_id
   * @param array $params
   * @param mixed $add_data
   *
   * @return mixed attachments
   */
  public function getBodyPayment($payment_method, $business_unit, $product_type, $id_type, $id, $purchase_order_id, $params, $add_data) {
    $body = [];
    if ($payment_method == 'creditCard') {
      $body = $this->utilsPaymentConvergent
        ->getIngenicoPaymentBody($business_unit, $product_type, $id_type, $id, $purchase_order_id, $this->params, $add_data);
    }
    else {
      $body = $this->utilsPaymentConvergent
        ->getBodyPayment($business_unit, $product_type, $id_type, $id, $purchase_order_id, $this->params, $add_data);
    }
    return $body;
  }

}
