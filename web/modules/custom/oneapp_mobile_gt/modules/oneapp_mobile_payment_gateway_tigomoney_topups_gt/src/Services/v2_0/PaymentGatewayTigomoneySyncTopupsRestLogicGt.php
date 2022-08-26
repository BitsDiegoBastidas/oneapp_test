<?php

namespace Drupal\oneapp_mobile_payment_gateway_tigomoney_topups_gt\Services\v2_0;

use Drupal\oneapp_mobile_payment_gateway_tigomoney_topups\Services\v2_0\PaymentGatewayTigomoneySyncTopupsRestLogic;

class PaymentGatewayTigomoneySyncTopupsRestLogicGt extends PaymentGatewayTigomoneySyncTopupsRestLogic {

  /**
   * Genera la orden.
   */
  public function generateOrderId($business_unit, $product_type, $id_type, $id, $purchaseorder_id) {
    $this->params['uuid'] = $this->token->getUserIdPayment();
    $this->params['tokenUuId'] = $this->token->getTokenUuid();

    $config_app = $this->utilsPaymentConvergent->getConfigPayment($product_type, 'configuration_app', $business_unit);
    $this->params['apiHost'] = $config_app->setting_app_payment['api_path'];
    $this->params['aws_service'] = isset($config_app->setting_app_payment["aws_service"]) ?
      $config_app->setting_app_payment["aws_service"] : 'payment';
    $decrypt_purchaseorder_id = $this->transactions->decryptId($purchaseorder_id);
    $data_transaction = $this->transactions->getTransactionById($decrypt_purchaseorder_id, []);
    $document_number = (isset($this->params['billingData']['nit']) && (strlen($this->params['billingData']['nit']) > 0)) ?
      strtoupper(trim($this->params['billingData']['nit'])) : "CF";
    if (isset($this->params['billingData'])) {
      $this->params['billingData']['nit'] = $document_number;
      if (isset($this->params['billingData']['address']) && (strlen($this->params['billingData']['address']) > 0)) {
        $this->params['street'] = trim($this->params['billingData']['address']);
      }
      $this->params['email'] = (isset($this->params['billingData']['email']) && (strlen($this->params['billingData']['email']) > 0)) ?
        trim($this->params['billingData']['email']) : trim($this->params['email']);
      if (isset($this->params['billingData']['fullname']) && (strlen($this->params['billingData']['fullname']) > 0)) {
        if (isset($this->params['customerNameToken']) && isset($this->params['tokenizedCardId'])) {
          $this->params['customerNameToken'] = ucwords(strtolower(trim($this->params['billingData']['fullname'])));
        }
        else {
          $this->params['customerName'] = ucwords(strtolower(trim($this->params['billingData']['fullname'])));
        }
      }
      $this->utilsPaymentConvergent->saveBillingData($this->params['billingData']);
    }
    // Validacion de sobrescritura  oneapp_mobile_payment_gateway_config.
    $add_data = [];
    $config_fac = \Drupal::config("oneapp.payment_gateway_tigomoney.mobile_topups.config")->getRawData();
    if ($config_fac['billing_form']['overwrite_data']) {
      $this->validateDataOverWrite($add_data, $config_fac);
    }
    $add_data += [
      'documentNumber' => $document_number,
      'documentType' => "NIT",
      "purchaseDetails" => [
        [
          "name" => "CO0434",
          "quantity" => (string) $data_transaction->amount,
          "amount" => '1',
        ],
      ],
    ];
    $id = $this->modifyMsisdn($id);
    $body = $this->utilsPaymentConvergent
      ->getBodyPayment($business_unit, $product_type, $id_type, $id, $purchaseorder_id, $this->params, $add_data);
    $this->params = $this->deleteParams($this->params);
    $order = $this->utilsPaymentConvergent
      ->generateOrderId($body, $business_unit, $product_type, $this->params);
    $this->transactions->updateDataTransactionOrderInProgress($decrypt_purchaseorder_id, $order);
    $body_logs = $body;
    $body_logs['creditCardDetails'] = [];
    $fields_log = [
      'purchaseOrderId' => $decrypt_purchaseorder_id,
      'message' => "Order in progress",
      'codeStatus' => 200,
      'operation' => $this->transactions::CREATED_ORDER,
      'description' => "Back office response: \n" . json_encode($order->body, JSON_PRETTY_PRINT) .
        "\nBody: \n" . json_encode($body_logs, JSON_PRETTY_PRINT),
      'type' => $product_type,
    ];
    $this->transactions->addLog($fields_log);
    return $order->body;

  }
}
