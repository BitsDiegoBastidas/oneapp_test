<?php

namespace Drupal\oneapp_mobile_billing_gt\Services;

use Drupal\oneapp_mobile_billing\Services\BillingService;
use Drupal\oneapp\Exception\HttpException;

/**
 * Class BillingServiceSv.
 */
class BillingServiceGt extends BillingService {


  /**
   * Responds to setConfig.
   *
   * @param mixed $configBlock
   *   Config card or default.
   */
  public function setConfig($configBlock) {
    $this->configBlock = $configBlock;
  }

  /**
   * Process to request the invoice api.
   *
   * @param [string] $id
   * @param [string] $id_type
   * @return void
   */
  public function getInvoicesData($id, $id_type = NULL) {

    $access = $this->validateAccessToB2bInvoices($id, $id_type);
    if (empty($access)) {
      return [
        'invoiceList' => [],
        'noData' => ['value' => 'no_access'],
      ];
    }

    $billing_account_id = $id;
    if ($id_type == "subscribers") {
      $config_mobile = \Drupal::config('oneapp_mobile.config')->getRawData();
      if (isset($config_mobile["billing"]["getBilingAccountByMsisdn"]) && $config_mobile["billing"]["getBilingAccountByMsisdn"]) {
        // Obtener billing_account_id.
        $mobile_utils_service = \Drupal::service('oneapp.mobile.utils');
        $billing_account_id = $mobile_utils_service->getBillingAccountByMsisdn($id);
      }
    }

    $invoices = $this->callInvoicesApi($billing_account_id);
    if (isset($invoices->noData) && $invoices->noData) {
      return [
        'invoiceList' => [],
        'noData' => ['value' => 'empty'],
      ];
    }

    $access = TRUE;
    $invoices = isset($invoices->invoices) ? $invoices->invoices : $invoices;
    if (is_array($invoices)) {
      // Validation access for business accounts.
      $validate_access = isset($config_mobile["billing"]['validate_access']) ? $config_mobile["billing"]['validate_access'] : '1';
      if ($validate_access) {
        $access = $this->validateAccessByInvoice($invoices[0], $id);
      }

      if ($access == FALSE) {
        return [
          'invoiceList' => [],
          'noData' => ['value' => 'no_access'],
        ];
      }

      $rows = [];
      foreach ($invoices as $index => $invoice) {
        $invoice->billingAccountId = $billing_account_id;
        $invoice->dueAmount = $invoice->balance;
        $invoice->invoiceId = $invoice->number;
        $invoice->invoiceSerial = $invoice->serial;
        $invoice->invoiceAmount = $invoice->amount;
        $invoice->billingPeriod = new \stdClass();
        $invoice->billingPeriod->startDateTime = $invoice->billingInfo->startDate;
        $invoice->billingPeriod->endDateTime = $invoice->billingInfo->endDate;
        $invoice->currencyId = '';
        $dueDate = $invoice->expirationDate;
        $invoice->dueDate = $invoice->expirationDate;
        $invoice->hasPayment = floatval($invoice->balance) == 0 ? TRUE : FALSE;
        $rows[] = $invoice;
      }
      return $rows;
    }
    return $invoices;
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance($id, $id_type) {

    $access = $this->validateAccessToB2bInvoices($id, $id_type);
    if (empty($access)) {
      return [
        'invoiceList' => [],
        'noData' => ['value' => 'no_access'],
      ];
    }

    $billing_account_id = $id;
    if ($id_type == "subscribers") {
      $config_mobile = \Drupal::config('oneapp_mobile.config')->getRawData();
      if (isset($config_mobile["billing"]["getBilingAccountByMsisdn"]) && $config_mobile["billing"]["getBilingAccountByMsisdn"]) {
        // Obtener billingAccountId.
        $mobile_utils_service = \Drupal::service('oneapp.mobile.utils');
        $billing_account_id = $mobile_utils_service->getBillingAccountByMsisdn($id);
      }
    }

    $balance = $this->callBalanceApi($billing_account_id);

    $invoices = $this->getInvoicesData($id, $id_type);
    if (!isset($invoices['noData'])) {
      // Get last invoice to continue.
      if (isset($invoices[0])) {
        $last_invoice = $invoices[0];
      }
    }

    $balance->dueAmount = isset($balance->pendingBalance) ? $balance->pendingBalance : 0;
    $balance->billingAccountId = $id;
    if (isset($last_invoice)) {
      $balance->invoiceId = $last_invoice->invoiceId;
      $balance->invoiceSerial = $last_invoice->serial;
      $balance->dueDate = $last_invoice->expirationDate;
      $balance->hasPayment = $last_invoice->hasPayment;
      $balance->billingCycle = $this->getCycleDay($last_invoice->billingInfo->cycle);
      $balance->currencyId = '';
      $balance->dueInvoicesCount = !empty($balance->unpaidInvoicesCount) ? $balance->unpaidInvoicesCount : $this->countInvoices($invoices);
      $balance->dueAmount = !empty($balance->dueAmount) ? $balance->dueAmount : $this->countAmountInvoices($invoices);
      $balance->lastInvoiceAmount = isset($last_invoice->amount) ? $last_invoice->amount : '';
    }
    else {
      $balance->hasPayment = TRUE;
    }

    return $balance;
  }

  /**
   * Implements callBalanceApi.
   *
   * @param string $id
   *   the Id to get data.
   *
   * @return ResponseInterface
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   */
  public function callBalanceApi($id) {
    try {
      return $this->manager
        ->load('oneapp_mobile_billing_v2_0_balance_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      if ($exception->getCode() == 404) {
        return new \stdClass();
      }
      throw $exception;
    }
  }

  /**
   * Count invoices.
   *
   * @param string $invoices
   *   Invoices array.
   */
  public function countInvoices($invoices = []) {
    $count = 0;
    foreach ($invoices as $index => $invoice) {
      if (isset($invoice->status) && $invoice->status != "Vigente") {
        $count++;
      }
    }
    return $count;
  }

  /**
   * Count ammount invoices debt.
   *
   * @param string $invoices
   *   Invoices array.
   */
  public function countAmountInvoices($invoices = []) {
    $amount = 0;
    foreach ($invoices as $index => $invoice) {
      if (isset($invoice->status) && $invoice->status != "Vigente") {
        $amount = $amount + $invoice->balance;
      }
    }
    return $amount;
  }

  /**
   * Implements getPdf.
   *
   * @param string $id
   *   Billing account.
   * @param string $invoiceId
   *   Id of invoice.
   * @param string $invoiceSerial
   *   Invoice serial.
   * @param string $decodeJson
   *   Convert to json or not.
   *
   * @return mixed
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   * @throws \Exception
   */
  public function callPdfApi($id, $invoice_id, $invoice_serial = NULL, $service_type = 'TELEFONIA', $decode_json = TRUE) {
    try {
      $data = $this->manager
        ->load('oneapp_mobile_billing_v2_0_invoices_pdf_endpoint')
        ->setHeaders([])
        ->setQuery([
          'invoice_serial' => $invoice_serial,
          'service_type' => $service_type,
        ])
        ->setParams([
          'id' => $id,
          'invoice_id' => $invoice_id,
        ])
        ->setDecodeJson($decode_json)
        ->sendRequest();
      if (isset($data->invoice)) {
        return base64_decode($data->invoice);
      }
      else {
        throw new \Exception('Resource Not Found', 404);
      }
    }
    catch (HttpException $httpException) {
      if ($httpException->getCode() == 500) {
        throw new \Exception('Resource Not Found', 500);
      }
      elseif ($httpException->getCode() == 404) {
        throw new \Exception('Resource Not Found', 404);
      }
    }
  }

  /**
   * Implements getInvoices.
   *
   * @param string $id
   *   Billing account Id.
   *
   * @return ResponseInterface
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   */
  public function callInvoicesApi($id) {
    try {
      return $this->manager
        ->load('oneapp_mobile_billing_v2_0_invoices_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      if ($exception->getCode() == 404) {
        $invoices = new \stdClass();
        $invoices->noData = "empty";
        return $invoices;
      }
      throw $exception;
    }
  }

  /**
   * Process data for the call api.
   *
   * @param string $id
   *   Account Id.
   * @param string $id_type
   *   Account Id Type: to access other info about Id if it is necessary.
   * @param string $invoice_id
   *   Invoice id.
   *
   * @return array
   *   Info to get pdf file with 'url' o 'data' index.
   * @throws \Exception
   */
  public function getPdf($id, $id_type, $invoice_id, $invoice_serial = NULL) {
    $is_postpaid = $this->isPostpaid($id);
    if (!$is_postpaid) {
      throw new \Exception('The id belong to prepaid account', 404);
    }
    $result = $this->callInvoicesApi($id);
    $founded_invoice_id = FALSE;
    $invoices = isset($result->invoices) ? $result->invoices : $result;
    if (is_array($invoices)) {
      foreach ($invoices as $invoice) {
        if ($invoice->number == $invoice_id) {
          $founded_invoice_id = TRUE;
        }
      }
    }
    if ($founded_invoice_id) {
      return $this->callPdfApi($id, $invoice_id, $invoice_serial);
    }
    else {
      throw new \Exception('The invoiceId not belong to msisdn', 404);
    }
  }

  /**
   * Get client Type.
   */
  public function getTypeClient($msisdn) {
    $info = $this->getInfoByMsisdn($msisdn);
    return $info->Envelope->Body->Subscriber->Product;
  }

  /**
   * Check if the account tpe is pospaid.
   */
  public function isPostpaid($msisdn) {
    $type_client = $this->getTypeClient($msisdn);
    $is_postpaid = FALSE;
    switch ($type_client) {
      case 'CREDITO':
        $is_postpaid = TRUE;
        break;
      case 'STAFF DE COMCEL':
        $is_postpaid = TRUE;
        break;
      case 'FACTURA FIJA':
        $is_postpaid = TRUE;
        break;
      default:
        $is_postpaid = FALSE;
    }
    return $is_postpaid;
  }

  /**
   * Implements getInfoByMsisdn.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return ResponseInterface
   *   The HTTP response object.
   *
   * @throws \ReflectionException
   */
  public function getInfoByMsisdn($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v1_0_details_by_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      throw new \Exception($exception->getMessage(), $exception->getCode());
    }
  }

}
