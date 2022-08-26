<?php

namespace Drupal\oneapp_mobile_payment_gateway_packets_gt\Services\v2_0;

use Symfony\Component\HttpFoundation\Response;
use Drupal\oneapp_mobile_payment_gateway_packets\Services\v2_0\PaymentGatewayPacketsRestLogic;

/**
 * Class PaymentGatewayPacketsRestLogicGt.
 */
class PaymentGatewayPacketsRestLogicGt extends PaymentGatewayPacketsRestLogic {

  /**
   * PaymentGatewayRestLogic start (Inicializa el proceso de pagos)
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
    $module_config = (object) $this->tokenAuthorization->getApplicationSettings("configuration_app");
    if (isset($module_config->setting_app_payment['enableBilingAccountByMsisdn']) &&
      $module_config->setting_app_payment['enableBilingAccountByMsisdn']) {
      $mobile_utils_service = \Drupal::service('oneapp.mobile.utils');
      $params['billingAccountId'] = $mobile_utils_service->getBillingAccountByMsisdn($id);
    }

    $id = $this->modifyMsisdn($id);
    if (isset($target_msisdn)) {
      $target_msisdn = $this->modifyMsisdn($target_msisdn);
    }
    $params['query']['accountNumber'] = (isset($target_msisdn)) ? $target_msisdn : $id;
    $payment_method = $params['paymentMethod'] ?? ($params['paymentMethodName'] ?? 'default');
    $offer = $this->getOffer($id, $this->parsePackageId($params["offerId"]));
    $additional_data = new \Stdclass();
    $additional_data->offer = $offer;
    $additional_data->paymentMethod = $payment_method;
    $fields = [
      'uuid' => $this->tokenAuthorization->getUserIdPayment(),
      'accountId' => $id,
      'accountNumber' => (isset($target_msisdn)) ? $target_msisdn : $id,
      'accountType' => $business_unit,
      'productType' => $product_type,
      'amount' => $offer['creditPackagePrice'],
      'isPartialPayment' => 0,
      'numberReference' => $offer['packageId'],
      'additionalData' => serialize($additional_data),
      'accessType' => $this->tokenAuthorization->getAccessType(),
    ];
    $transaction_id = $this->transactions->initTransaction($fields, $product_type);
    $finger_print = $this->getAttachments($id, $business_unit, $product_type, $transaction_id);
    $purchaseorder_id = $this->transactions->encryptId($transaction_id);
    $data = [
      'fingerPrint' => $finger_print,
      'purchaseorderId' => $purchaseorder_id,
    ];
    $data_offer = $this->formatOffers($offer, $fields);
    $billing_form = $this->getBillingForm();
    $type_form = $this->isAutoPackets ? 'autopackets' : $product_type;
    $new_card_form = $this->utilsPayment->getFormPayment($type_form);
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
   * Dar formato a las ofertas.
   */
  public function formatOffers($offer, $fields, $taxes = FALSE) {
    $row = [];
    $mobile_service = \Drupal::service('oneapp.mobile.utils');
    $oneapp_utils = \Drupal::service('oneapp.utils');
    $row['offerId'] = [
      'label' => $this->config["fields"]["offerId"]["label"],
      'show' => $this->config["fields"]["offerId"]["show"] ? TRUE : FALSE,
      'value' => $offer['packageId'],
      'formattedValue' => (string) $offer['packageId'],
    ];
    $row['accountNumber'] = [
      "show" => ($this->config['fields']['msisdn']['show']) ? TRUE : FALSE,
      "label" => $this->config['fields']['msisdn']['label'],
      "value" => $fields['accountNumber'],
      "formattedValue" => (string) $mobile_service->modifyMsisdnCountryCode($fields['accountNumber'], FALSE),
    ];
    $row['offerName'] = [
      'label' => $this->config["fields"]["offerName"]["label"],
      'show' => $this->config["fields"]["offerName"]["show"] ? TRUE : FALSE,
      'value' => $offer['name'],
      'formattedValue' => (string) $offer['name'],
    ];
    $row['description'] = [
      'label' => $this->config["fields"]["description"]["label"],
      'show' => $this->config["fields"]["description"]["show"] ? TRUE : FALSE,
      'value' => $offer['name'],
      'formattedValue' => (string) $offer['name'],
    ];
    $row['categoryName'] = [
      'label' => $this->config["fields"]["categoryName"]["label"],
      'show' => $this->config["fields"]["categoryName"]["show"] ? TRUE : FALSE,
      'value' => $offer['category'],
      'formattedValue' => $offer['category'],
    ];
    $row['validity'] = [
      'label' => $this->config["fields"]["validity"]["label"],
      'show' => $this->config["fields"]["validity"]["show"] ? TRUE : FALSE,
      'value' => $offer['validityNumber'],
      'formattedValue' => $offer['validityNumber'] . ' ' . $offer['validityType'],
    ];

    $amount = [
      'value' => $offer['cost'],
      'formattedValue' => $oneapp_utils->formatCurrency($offer['cost']),
    ];

    $row['amount'] = [
      'label' => $this->config["fields"]["amount"]["label"],
      'show' => $this->config["fields"]["amount"]["show"] ? TRUE : FALSE,
      'value' => $amount['value'],
      'formattedValue' => $amount['formattedValue'],
    ];
    if (isset($this->config["fields"]["nextPayment"]) && isset($this->config["fields"]["frequency"])) {
      $date = new \DateTime();
      $date_formatter = \Drupal::service('date.formatter');
      $validity = $this->getFrecuencyFormatted($offer);
      $expiration_date = $oneapp_utils->formatDateRegressiveWithDuration($date->format('Y-m-d H:i:s'), $validity['value'], false);
      $row['nextPayment'] = [
        'label' => $this->config["fields"]["nextPayment"]["label"],
        'show' => $this->config["fields"]["nextPayment"]["show"] ? TRUE : FALSE,
        'value' => $expiration_date["value"],
        'formattedValue' =>
          $date_formatter->format(strtotime($expiration_date["value"]), $this->config["configs"]["dates"]["expirationDate"]),
      ];
      $frecuency = $this->getFrecuencyFormatted($offer);
      $row['frequency'] = [
        'label' => $this->config["fields"]["frequency"]["label"],
        'show' => $this->config["fields"]["frequency"]["show"] ? TRUE : FALSE,
        'value' => $frecuency['value'],
        'formattedValue' => $frecuency['formattedValue'],
      ];
    }

    return $row;
  }

  /**
   * Obtiene la oferta.
   */
  public function getOffer($id, $offer_id) {
    $packets_list = $this->service->getPacketsList($id);
    $this->matchProductsToNBO($packets_list->products, $id);
    $packet_info = $this->getPacketInfo($id, $packets_list, $offer_id);
    return $packet_info;
  }

  /**
   * Genera la orden.
   */
  public function generateOrderId($business_unit, $product_type, $id_type, $id, $purchaseorder_id) {
    $id = $this->modifyMsisdn($id);
    $is_blocked_payment_he = (bool) $this->config['he_otp']['disable']['value'];
    if ($is_blocked_payment_he && $this->tokenAuthorization->isHe()) {
      throw new \Exception($this->config['he_otp']['disable']['message'], Response::HTTP_BAD_REQUEST);
    }

    $this->params['uuid'] = $this->tokenAuthorization->getUserIdPayment();
    $this->params['tokenUuId'] = $this->tokenAuthorization->getTokenUuid();
    // Orden de prioridad para correo a utilizar.
    $this->params['email'] =
      !empty($this->tokenAuthorization->getEmail()) ? $this->tokenAuthorization->getEmail() : $this->params['email'];
    if (!$this->tokenAuthorization->isHe()) {
      $this->params['customerNameToken'] =
        $this->tokenAuthorization->getGivenNameUser() . " " . $this->tokenAuthorization->getFirstNameUser();
    }
    else {
      $this->params['email'] = empty($this->params['email']) ? 'null@cybersource.com' : trim($this->params['email']);
    }
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
      $type = $this->utilsPayment->saveBillingData($this->params['billingData']);
    }
    $decrypt_purchaseorder_id = $this->transactions->decryptId($purchaseorder_id);
    $config_app = $this->utilsPayment->getConfigPayment($product_type, 'configuration_app', $business_unit);
    $this->params['apiHost'] = $config_app->setting_app_payment['api_path'];
    $data_transaction = $this->transactions->getTransactionById($decrypt_purchaseorder_id, []);
    if ($data_transaction->stateOrder != "INITIALIZED") {
      throw new \Exception(t('Este pago ya fue realizado'), Response::HTTP_BAD_REQUEST);
    }
    $this->params['packageId'] = $data_transaction->numberReference;
    $addit_data = (array)unserialize($data_transaction->additionalData);
    $payment_method = $addit_data["paymentMethod"] ?? 'default';
    $offer = $addit_data["offer"];

    $additional_data = [
      'documentNumber' => $document_number,
      'documentType' => "NIT",
      'productReference' => isset($offer['creditPackageCategory']) ? $offer['creditPackageCategory'] : $offer['productReference'],
      "purchaseDetails" => [
        [
          "name" => "CO0434",
          "quantity" => (string) $offer['creditPackagePrice'],
          "amount" => '1',
        ],
      ],
    ];
    $body = $this->getBodyPayment($payment_method, $business_unit, $product_type, $id_type, $id, $purchaseorder_id, $this->params, $additional_data);
    $order = $this->utilsPayment
      ->generateOrderId($body, $business_unit, $product_type, $this->params);
    if ($this->isAutoPackets) {
      if (isset($this->params) && isset($this->params["tokenizedCardId"])) {
        $additional_data = unserialize($data_transaction->additionalData);
        $additional_data['paymentTokenId'] = $this->params["tokenizedCardId"];
      }
      if (isset($this->params["numberCard"])) {
        $additional_data = unserialize($data_transaction->additionalData);
        $additional_data['numberCard'] = $this->params["numberCard"];
      }
      $additional_data['billingData'] = $this->params["billingData"];
      $additional_data["productReference"] = $offer["packageId"];
      $additional_data->billingData = $this->params["billingData"];
      $additional_data->email = $this->params["email"];
    }
    if (isset($addit_data["offer"]["category"])) {
      $additional_data["offer"]["category"] = $addit_data["offer"]["category"];
    }
    $this->transactions->updateDataTransactionOrderInProgress($decrypt_purchaseorder_id, $order, $additional_data);
    $body_logs = $body;
    $body_logs['creditCardDetails'] = [];
    $fields_log = [
      'purchaseOrderId' => $decrypt_purchaseorder_id,
      'message' => "Order in progress",
      'codeStatus' => 200,
      'operation' => $this->transactions::CREATED_ORDER,
      'description' => "Back office response: \n" . json_encode($order->body, JSON_PRETTY_PRINT) . "\nBody: \n" .
        json_encode($body_logs, JSON_PRETTY_PRINT),
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
  public function getBodyPayment($payment_method, $business_unit, $product_type, $id_type, $id, $purchase_order_id, $params, $add_data) {
    $body = [];
    if ($payment_method == 'creditCard' && $this->utilsPayment->isActiveIngenico() ) { // valida también si Ingenico está activo
      $body = $this->utilsPayment
        ->getIngenicoPaymentBody($business_unit, $product_type, $id_type, $id, $purchase_order_id, $this->params, $add_data);
    }
    else {
      $body = $this->utilsPayment
        ->getBodyPayment($business_unit, $product_type, $id_type, $id, $purchase_order_id, $this->params, $add_data);
    }
    return $body;
  }

  public function matchProductsToNBO(&$products, $msisdn) {
    $available_offers_service = \Drupal::service('oneapp_mobile_upselling.v2_0.available_offers_services');
    $suggested_products = $available_offers_service->getSuggestedProducts($msisdn)->suggestedProducts;
    if (!empty($suggested_products)) { 
      foreach ($products as $id_product => $product) {
        foreach ($suggested_products as $suggested_product) {
          if ($suggested_product->product == $product->packageId) {
            $product->packageId = strval($product->packageId);
            $product->subcategory = strval($product->subcategory);
            $products[] = $product;
            unset($products[$id_product]);
          }
        }
      }
    }
  }

  /**
   * Obtiene información del paquete.
   */
  protected function getPacketInfo($id, $packets_list, $offer_id) {
    $packet_info = NULL;
    foreach ($packets_list->products as $product) {
      if ($product->packageId == $offer_id) {
        if (!isset($product->creditPackagePrice) && !isset($product->creditPackageCategory)) {
          throw new \Exception(t('Paquete no disponible con este medio de pago'), Response::HTTP_BAD_REQUEST);
        }
        $packet_info['msisdn'] = $id;
        $packet_info['packageId'] = $product->packageId;
        $packet_info['cost'] = $product->cost;
        $packet_info['name'] = $product->name;
        $packet_info['category'] = $product->category;
        $packet_info['creditPackagePrice'] = $product->creditPackagePrice;
        $packet_info['creditPackageCode'] = $product->creditPackageCode;
        $packet_info['creditPackageCategory'] = $product->creditPackageCategory;
        $packet_info['validityNumber'] = $product->validityNumber;
        $packet_info['validityType'] = $product->validityType;
        break;
      }

    }
    if (!$packet_info) {
      throw new \Exception(t('Paquete no valido'), Response::HTTP_BAD_REQUEST);
    }
    return $packet_info;
  }

  public function getFrecuencyFormatted($offer) {
    $validity = isset($offer["validityType"]) && strtolower($offer["validityType"]) == "dias" ?
      $offer["validityNumber"] * 24 : $offer["validityNumber"];
    if ($validity != '') {
      $days = round($validity / 24, 2);
      return [
        'value' => $validity,
        'formattedValue' => t('@day días', ['@day' => $days]),
      ];
    }
    else {
      return [
        'value' => $validity,
        'formattedValue' => $offer["validityNumber"] . ' ' . $offer["validityType"],
      ];
    }
  }

  public function getBillingForm() {
    $billing_form = $this->utilsPayment->getBillingDataForm('packets', 'mobile');
    if ($this->isAutoPackets) {
      $billing_form_autopackets = (object) \Drupal::config("oneapp.payment_gateway.mobile_autopackets.config")->get("billing_form");
      $billing_form["billingDataForm"]["fullname"]["value"] = !empty($billing_form["billingDataForm"]["fullname"]["value"]) ?
        $billing_form["billingDataForm"]["fullname"]["value"] : $billing_form_autopackets->fullname["value"];
      $billing_form["billingDataForm"]["nit"]["value"] = !empty($billing_form["billingDataForm"]["nit"]["value"]) ?
        $billing_form["billingDataForm"]["nit"]["value"] : $billing_form_autopackets->nit["value"];
      $billing_form["billingDataForm"]["address"]["value"] = !empty($billing_form["billingDataForm"]["address"]["value"]) ?
        $billing_form["billingDataForm"]["address"]["value"] : $billing_form_autopackets->address["value"];
      if (isset($billing_form_autopackets->disable_email) && $billing_form_autopackets->disable_email) {
        $billing_form["billingDataForm"]["email"]['disable'] = TRUE;
        $billing_form["billingDataForm"]["email"]["value"] = $this->tokenAuthorization->getEmail();
      }
    }
    return $billing_form;
  }

  /**
   * Parse Product Reference permite eliminar el prefijo NBO- para que no vaya a Payment Gateway
   */
  public function parsePackageId($package_id) {
    $result = str_replace('NBO-', '', $package_id);
    return $result;
  }

  /**
   * @inheritDoc
   */
  public function getAttachments($id, $business_unit, $product_type, $purchase_order_id, $is_he = FALSE) {
    $finger_print = [];
    $module_handler = \Drupal::service('module_handler');
    if ($module_handler->moduleExists('oneapp_convergent_payment_gateway_ingenico') ) {
      $data = \Drupal::config("oneapp.payment_gateway.{$business_unit}_packets.config")->get('ingenico');
    }
    if (isset($data["setting_app_payment"]["active"]) && $data["setting_app_payment"]["active"]) {
      try {
        $purchase_order_id = is_numeric($purchase_order_id) ? $purchase_order_id : $this->transactions->decryptId($purchase_order_id);
        $finger_print = $this->utilsPayment->getIngenicoAttachments($id, $business_unit, $product_type, $purchase_order_id);
      } catch (\Exception $e) {
          throw new \Exception(t('Ha ocurrido un error durante la inicialización de la transacción'), 404);
      }
    }
    else {
      $finger_print = $this->utilsPayment->getAttachments($id, $business_unit, $product_type, $purchase_order_id);
    }
    return $finger_print;
  }

}
