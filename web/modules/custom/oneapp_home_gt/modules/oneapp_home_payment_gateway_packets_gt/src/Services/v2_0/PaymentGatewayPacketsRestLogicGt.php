<?php

namespace Drupal\oneapp_home_payment_gateway_packets_gt\Services\v2_0;

use Symfony\Component\HttpFoundation\Response;
use Drupal\oneapp_home_payment_gateway_packets\Services\v2_0\PaymentGatewayPacketsRestLogic;

/**
 * class PaymentGatewayPacketsRestLogicGt
 */
class PaymentGatewayPacketsRestLogicGt extends PaymentGatewayPacketsRestLogic {

  /**
   * @inheritdoc
   */
  public function generateOrderId($business_unit, $product_type, $id_type, $id, $purchase_order_id) {

    $is_blocked_payment_he = (bool) $this->config['he_otp']['disable']['value'];
    if ($is_blocked_payment_he && $this->tokenAuthorization->isHe()) {
      throw new \Exception($this->config['he_otp']['disable']['message'], Response::HTTP_BAD_REQUEST);
    }

    $this->params['uuid'] = $this->tokenAuthorization->getUserIdPayment();
    $this->params['tokenUuId'] = $this->tokenAuthorization->getTokenUuid();
    // Orden de prioridad para correo a utilizar.
    $this->params['email'] = (empty($this->tokenAuthorization->getEmail()) && isset($this->params['email']))
                           ? $this->params['email']
                           : $this->tokenAuthorization->getEmail();
    if (!$this->tokenAuthorization->isHe()) {
      $this->params['customerNameToken'] = $this->tokenAuthorization->getGivenNameUser() . " "
          . $this->tokenAuthorization->getFirstNameUser();
    }
    // Validacion de sobrescritura  oneapp_mobile_payment_gateway_config.
    $add_data = [];
    $config_fac = \Drupal::config("oneapp.payment_gateway.home_packets.config")->getRawData();
    if ($config_fac['billing_form']['overwrite_data']) {
      $this->validateDataOverWrite($add_data, $config_fac);
    }

    $decrypt_purchaseorder_id = $this->transactions->decryptId($purchase_order_id);
    $configApp = $this->utilsPayment->getConfigPayment($product_type, 'configuration_app', $business_unit);
    $this->params['apiHost'] = $configApp->setting_app_payment['api_path'];
    $this->params['aws_service'] = isset($configApp->setting_app_payment["aws_service"])
                                 ? $configApp->setting_app_payment["aws_service"]
                                 : 'payment';
    $data_transaction = $this->transactions->getTransactionById($decrypt_purchaseorder_id, []);

    if ($data_transaction->stateOrder != "INITIALIZED") {
      throw new \Exception(t('Este pago ya fue realizado'), Response::HTTP_BAD_REQUEST);
    }
    $this->params['packageId'] = $data_transaction->numberReference;
    $body = $this
      ->getBodyPayment($business_unit, $product_type, $id_type, $id, $purchase_order_id, $this->params, $add_data);
    $order = $this->utilsPayment
      ->generateOrderId($body, $business_unit, $product_type, $this->params);
    $additional_data = [];
    if ($this->isAutoPackets) {
      if (isset($this->params) && isset($this->params["tokenizedCardId"])) {
        $additional_data = (object)unserialize($data_transaction->additionalData);
        $additional_data->paymentTokenId = $this->params["tokenizedCardId"];
      }
      if (isset($this->params["numberCard"])) {
        $additional_data = (object)unserialize($data_transaction->additionalData);
        $additional_data->numberCard = $this->params["numberCard"];
      }
      if (isset($this->params["billingData"])) {
        $additional_data->billingData = $this->params["billingData"];
      }
      $additional_data->email = $this->params["email"];
    }
    $this->transactions->updateDataTransactionOrderInProgress($decrypt_purchaseorder_id, $order, $additional_data);
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

  /**
   * Return payment gateway body by payment method
   *
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
  public function getBodyPayment($business_unit, $product_type, $id_type, $id, $purchase_order_id, $params, $add_data) {
    $body = [];
    $module_handler = \Drupal::service('module_handler');
    if ($module_handler->moduleExists('oneapp_convergent_payment_gateway_ingenico') ) {
      $data = \Drupal::config("oneapp.payment_gateway.home_packets.config")->get('ingenico');
    }
    if (isset($data["setting_app_payment"]["active"]) && $data["setting_app_payment"]["active"]) {
      $body = $this->utilsPayment
        ->getIngenicoPaymentBody($business_unit, $product_type, $id_type, $id, $purchase_order_id, $this->params, $add_data);
    }
    else {
      $body = $this->utilsPayment
        ->getBodyPayment($business_unit, $product_type, $id_type, $id, $purchase_order_id, $this->params, $add_data);
    }
    return $body;
  }

  /**
   * @inheritdoc
   */
  public function start($id, $business_unit, $product_type, $params, $request, $target_msisdn) {
    $is_blocked_payment_he = (bool) $this->config['he_otp']['disable']['value'];
    if ($is_blocked_payment_he && $this->tokenAuthorization->isHe()) {
      return [
        'message' => $this->config['he_otp']['disable']['message'],
        'actions' => [
          'label' => $this->config['he_otp']['disable']['button'],
          'show' => TRUE,
          'type' => "button",
        ],
      ];
    }
    $module_config = $this->utilsPayment->getConfigPayment($product_type, '', $business_unit);
    if (isset($target_msisdn)) {
      $target_msisdn = $this->modifyMsisdn($target_msisdn);
    }
    $id = $this->modifyMsisdn($id);
    $offer = $this->getOffer($id, $params["offerId"]);
    $taxes = FALSE;
    if (isset($module_config->taxes['active']) && $module_config->taxes['active']) {
      $taxes = TRUE;
      $this->setTaxes($offer, $module_config->taxes['tax']);
    }
    $amount = ($taxes) ? $offer->totalCost : $offer->cost;
    if (is_float($amount)) {
      $amount = number_format($amount, 2, '.', '');
    }
    $params['query']['accountNumber'] = (isset($target_msisdn)) ? $target_msisdn : $id;
    $fields = [
      'uuid' => $this->tokenAuthorization->getUserIdPayment(),
      'accountId' => $id,
      'accountNumber' => (isset($target_msisdn)) ? $target_msisdn : $id,
      'accountType' => $business_unit,
      'productType' => $product_type,
      'amount' => $amount,
      'isPartialPayment' => 0,
      'numberReference' => $params['offerId'],
      'additionalData' => serialize($offer),
      'accessType' => $this->tokenAuthorization->getAccessType(),
    ];
    $transaction_id = $this->transactions->initTransaction($fields, $product_type);
    $finger_print = $this->getAttachments($id, $business_unit, $product_type, $transaction_id);
    $purchaseorder_id = $this->transactions->encryptId($transaction_id);
    $data = [
      'fingerPrint' => $finger_print,
      'purchaseorderId' => $purchaseorder_id,
    ];
    $data_offer = $this->formatOffers($offer, $fields, $taxes);
    $billing_form = $this->utilsPayment->getBillingDataForm('packets', 'home');
    $type_form = $this->isAutoPackets ? 'autopackets' : $product_type;
    $new_card_form = $this->utilsPayment->getFormPayment($type_form, $data);
    if ($billing_form) {
      $forms = [$new_card_form, $billing_form];
    }
    else {
      $forms = $new_card_form;
    }
    $actions = $this->config["actions"];
    if ($this->isLowDenominations($transaction_id)) {
      $params['query']['applicationName'] = $this->utilsPayment->getLowDenominationsAppName($product_type, $business_unit);
    }
    $response = [
      'forms' => $forms,
      'data' => $data,
      'cards' => $this->utilsPayment->getCards($business_unit, $params),
      'offer' => $data_offer,
      'actions' => $actions,
    ];
    if ($this->tokenAuthorization->isHe()) {
      $response['message'] = $this->config['he_otp']['flow']['message'];
      $response['actions']['messageButton'] = [
        'label' => $this->config['he_otp']['flow']['button'],
        'show' => TRUE,
        'type' => 'button',
      ];
    }
    foreach ($response['actions'] as $key => $action) {
      $response['actions'][$key]['show'] = (bool) $action['show'];
    }

    return $response;
  }

  /**
   * Return fingerprint attachemts by payment method
   *
   * @param string $id
   * @param string $business_unit
   * @param string $product_type
   * @param array $params
   * @param mixed $transaction_id
   *
   * @return mixed attachments
   */
  public function getAttachments($id, $business_unit, $product_type, $transaction_id) {
    $finger_print = [];
    if ($this->utilsPayment->isActiveIngenico()) {
      try {
        $finger_print = $this->utilsPayment->getIngenicoAttachments($id, $business_unit, $product_type, $transaction_id);
      } catch (\Exception $e) {
          throw new \Exception(t('Ha ocurrido un error durante la inicialización de la transacción'), 404);
      }
    }
    else {
      $finger_print = $this->utilsPayment->getAttachments($id, $business_unit, $product_type, $transaction_id);
    }
    return $finger_print;
  }
}
