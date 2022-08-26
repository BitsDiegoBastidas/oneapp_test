<?php

namespace Drupal\oneapp_home_billing_gt\Services;

use Drupal\oneapp_home_billing\Services\BillingService;
use Drupal\oneapp\Exception\HttpException;

/**
 * Class BillingRestLogicSv.
 */
class BillingServiceGt extends BillingService {

  /**
   * Get the all balance info.
   *
   * @param string $id
   *   Account Id.
   * @param string $id_type
   *   Account Id Type: to access other info about Id if it is necessary.
   *
   * @return object
   *   data.
   */
  public function getBalance($id, $id_type) {

    $access = $this->validateAccessToB2bInvoices($id, $id_type);
    if (empty($access)) {
      return [
        'invoiceList' => [],
        'noData' => ['value' => 'no_access'],
      ];
    }

    $balance = $this->callBalanceApi($id);
    $invoices = $this->getInvoicesData($id, $id_type);
    $last_invoice = $invoices[0] ?? [];

    $balance->invoiceId = isset($balance->lastInvoiceNumber) && !empty($balance->lastInvoiceNumber) ? $balance->lastInvoiceNumber : '';
    $balance->invoiceSerial = $last_invoice->serial ?? '';
    $balance->creationDate = isset($balance->expirationDate) && !empty($balance->expirationDate) ? $balance->expirationDate : '';
    $balance->dueDate = isset($balance->expirationDate) && !empty($balance->expirationDate) ? $balance->expirationDate : '';
    $balance->dueAmount = isset($balance->pendingBalance) && !empty($balance->pendingBalance) ? $balance->pendingBalance : 0;
    $balance->dueInvoicesCount = isset($balance->unpaidInvoicesCount) && !empty($balance->unpaidInvoicesCount) ?
      $balance->unpaidInvoicesCount : 0;
    $balance->billingAccountId = (isset($last_invoice) && isset($last_invoice->billingAccountId)) ? $last_invoice->billingAccountId : '';

    if (!empty($last_invoice)) {
      $cycle = isset($last_invoice->billingInfo->cycle) ? $this->getCycleDay($last_invoice->billingInfo->cycle) : '';
      $balance->invoiceId = empty($balance->invoiceId) ? $last_invoice->number : $balance->invoiceId;
      $balance->creationDate = empty($balance->creationDate) ? $last_invoice->expirationDate : $balance->creationDate;
      $balance->creationDate = date("Y-m-d",strtotime($last_invoice->billingInfo->endDate."+ 1 day"));
      $balance->dueDate = empty($balance->dueDate) ? $last_invoice->expirationDate : $balance->dueDate;
      $balance->extendedDueDate['value'] = $cycle;
      $balance->extendedDueDate['formattedValue'] = $cycle;
      $balance->dueDate = isset($last_invoice->expirationDate) ? $last_invoice->expirationDate : '';
      $balance->period = isset($last_invoice->billingInfo) ?
        $last_invoice->billingInfo->startDate . " - " . $last_invoice->billingInfo->endDate : '';
      $balance->startPeriod = isset($last_invoice->billingInfo->startDate) ? $last_invoice->billingInfo->startDate : '';
      $balance->endPeriod = isset($last_invoice->billingInfo->endDate) ? $last_invoice->billingInfo->endDate : '';
      $balance->hasPayment['value'] = isset($last_invoice->status) ? $last_invoice->status : '';
      $balance->hasPayment['formattedValue'] = isset($last_invoice->status) && $last_invoice->status == "Vigente" ? TRUE : FALSE;
      $balance->lastInvoiceAmount = isset($last_invoice->amount) ? $last_invoice->amount : '';
    }
    else {
      $balance->extendedDueDate = '';
      $balance->period = '';
      $balance->hasPayment = '';
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
  public function callBalanceApi($id, $query = []) {
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
        return new \stdClass;
      }
      throw $exception;
    }
  }

  /**
   * Get data invoices.
   *
   * @param string $id
   *   Account Id.
   * @param string $id_type
   *   Account Id Type: to access other info about Id if it is necessary.
   *
   * @return object
   *   Data of all invoices
   */
  public function getInvoicesData($id, $id_type = NULL) {

    $access = $this->validateAccessToB2bInvoices($id, $id_type);
    if (empty($access)) {
      return [
        'invoiceList' => [],
        'noData' => ['value' => 'no_access'],
      ];
    }

    $invoices = $this->callInvoicesApi($id);
    $invoices = isset($invoices->invoices) ? $invoices->invoices : $invoices;
    if (isset($invoices->noData) && $invoices->noData || empty($invoices)) {
      return [
        'invoiceList' => [],
        'noData' => ['value' => 'empty']
      ];
    }

    $config_block = \Drupal::config('adf_block_config.oneapp_home_billing_v2_0_invoices_block')->getRawData();

    if (is_array($invoices)) {
      foreach ($invoices as $key => $invoice) {
        $invoices[$key]->invoiceId = isset($invoice->number) ? $invoice->number : '';
        $invoices[$key]->invoiceSerial = $invoice->serial;
        $invoices[$key]->invoiceAmount = isset($invoice->amount) ? $invoice->amount : 0;
        $invoices[$key]->dueAmount =  isset($invoice->balance) ? $invoice->balance : 0;
        $invoices[$key]->creationDate = isset($invoice->expirationDate) ? $invoice->expirationDate : '';
        $invoices[$key]->dueDate = isset($invoice->expirationDate) ? $invoice->expirationDate : '';
        $end_date = $this->utils->getFormattedValue($config_block['block']['fields']['period']['format'], $invoice->billingInfo->endDate);
        $invoices[$key]->period['value'] = $invoice->billingInfo->endDate;
        $invoices[$key]->period['formattedValue'] = $end_date;
        $invoices[$key]->startPeriod = isset($invoice->billingInfo->startDate) ? $invoice->billingInfo->startDate : '';
        $invoices[$key]->endPeriod = isset($invoice->billingInfo->endDate) ? $invoice->billingInfo->endDate : '';;
        $invoices[$key]->hasPayment = floatval($invoice->balance) == 0 ? TRUE : FALSE;
      }
    }
    return $invoices;
  }

  /**
   * Request to invoices api.
   *
   * @param [type] $id
   *   Id of account query.
   * @param array $query
   *   Query params for request.
   *
   * @return object
   *   data from api.
   */
  public function callInvoicesApi($id, $query = []) {
    try {
      $invoices = $this->manager
        ->load('oneapp_home_billing_v2_0_invoices_endpoint')
        ->setParams(['id' => $id])
        ->setHeaders([])
        ->setQuery($query)
        ->sendRequest();
      return $invoices;
    }
    catch (HttpException $exception) {
      if ($exception->getCode() == 404) {
        $invoices = new \stdClass;
          $invoices->noData = "empty";
          return $invoices;
      }
      throw $exception;
    }
  }

  /**
   * Implements getPdf api.
   *
   * @param string $id
   *   Account Id.
   * @param string $invoice_id
   *   Invoice Id.
   *
   * @return mixed
   *   The HTTP response object.
   *
   * @throws \Drupal\oneapp\Exception\HttpException
   * @throws \Exception
   */
  public function callPdfApiGt($id, $invoice_id, $invoice_serial, $service_type = 'HOME', $decode_json = TRUE) {
    $result = [];
    try {
      $data = $this->manager
        ->load('oneapp_home_billing_v2_0_pdf_download_endpoint')
        ->setParams(['id' => $id, 'invoice_id' => $invoice_id])
        ->setHeaders(['Accept' => 'application/pdf',])
        ->setQuery(['invoice_serial' => $invoice_serial, 'service_type' => $service_type])
        ->setDecodeJson($decode_json)
        ->sendRequest();
      if (isset($data->invoice)) {
        return base64_decode($data->invoice);
      }
      else {
       throw new \Exception('Resource Not Found', 404);
      }
    } catch (HttpException $e) {
       if ($e->getCode() == 500) {
         throw new \Exception('Resource Not Found', 500);
       }
       elseif ($e->getCode() == 404) {
         throw new \Exception('Resource Not Found', 404);
       }
    }
    return $result;
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
  public function getPdfDataGt($id, $id_type, $invoice_id, $invoice_serial) {
    $result = $this->callInvoicesApi($id);
    $invoices = isset($result->invoices) ? $result->invoices : $result;
    $founded_invoice_id = FALSE;
    if (is_array($invoices)) {
      foreach ($invoices as $invoice) {
        if ($invoice->number == $invoice_id) {
          $founded_invoice_id = TRUE;
        }
      }
    }
    if ($founded_invoice_id) {
      return $this->callPdfApiGt($id, $invoice_id, $invoice_serial);
    }
    else {
      throw new \Exception('The invoiceId not belong to msisdn', 404);
    }
  }

  /**
   * update user data
   *
   * @param string $subscriber_id
   * @param array $body
   * @return string
   */
  public function updateBillingInfo($subscriber_id, $body) {
    try {
      $response = $this->manager
        ->load('oneapp_convergent_home_electronic_invoice_v2_0_put_billing_info_endpoint')
        ->setHeaders(['Content-Type' => 'application/json'])
        ->setParams(['id' => $subscriber_id])
        ->setBody($body)
        ->sendRequest();

      if (isset($response) && $response->message == 'Settings successfully updated') {
        return '200';
      }
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      return $e->getCode();
    }
  }

  /**
   * Update email customer
   *
   * @param string $agreement_id
   * @param string $subscriber_id
   * @param array $body
   * @return string
   */
  public function updatePaperlessInvoiceInfo($agreement_id, $subscriber_id, $body) {
    try {
      $response = $this->manager
        ->load('oneapp_convergent_home_electronic_invoice_v2_0_paperless_invoice_info_endpoint')
        ->setHeaders(['Content-Type' => 'application/json'])
        ->setParams([
          'agreementId'   => $agreement_id,
          'subscriberId'  => $subscriber_id
        ])
        ->setBody($body)
        ->sendRequest();

      if (isset($response) && $response->code == '00') {
        return '200';
      }
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      return $e->getCode();
    }
  }

}
