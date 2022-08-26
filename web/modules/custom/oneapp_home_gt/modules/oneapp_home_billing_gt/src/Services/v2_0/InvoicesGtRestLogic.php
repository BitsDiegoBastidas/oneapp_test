<?php

namespace Drupal\oneapp_home_billing_gt\Services\v2_0;

use Drupal\oneapp_home_billing\Services\v2_0\InvoicesRestLogic;

/**
 * Class InvoicesRestLogic.
 */
class InvoicesGtRestLogic extends InvoicesRestLogic {



  /**
   * Get all data invoices for api.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function get($id, $idType) {

    $limit_invoices = $this->getLimitInvoices();
    $billing = \Drupal::service("oneapp_home_billing.billing_data");
    $data = $billing->getInvoicesData($id, $idType);

    if (isset($data["noData"]) && (($data["noData"]['value'] == 'empty') || ($data['noData']['value'] == 'no_access'))) {
      return $data;
    }

    $invoices = [];
    if (isset($data)) {
      if (is_array($data) && count($data) > 0) {
        if ($limit_invoices > 0) {
          $data = array_slice($data, 0, $limit_invoices);
        }
        foreach ($data as $key => $invoice) {
          $invoices[$key]['invoiceId'] = $this->formatFieldInvoiceId('invoiceId', $invoice->invoiceId, $invoice->invoiceSerial);
          $invoices[$key]['invoiceAmount'] = $this->formatField('invoiceAmount', $invoice->invoiceAmount);
          $invoices[$key]['dueAmount'] = $this->formatField('dueAmount', $invoice->dueAmount);
          $invoices[$key]['period'] = $this->formatField('period', $invoice->period);
          if (!empty($invoice->endPeriod)) {
            $startPeriod = $this->utils->getFormattedValue($this->configBlock['fields']['period']['format'], $invoice->startPeriod);
            $endPeriod = $this->utils->getFormattedValue($this->configBlock['fields']['period']['format'], $invoice->endPeriod);
            $invoices[$key]['period']['formattedValue'] = t('@startPeriod a @endPeriod', ['@startPeriod' => $startPeriod, '@endPeriod' => $endPeriod]);
            unset($startPeriod);
            unset($endPeriod);
          }
          $invoices[$key]['dueDate'] = $this->formatField('dueDate', $invoice->dueDate);
          $invoices[$key]['invoiceType'] = $this->formatField('invoiceType', '');  //TODO
          $invoices[$key]['hasPayment'] = $this->formatField('hasPayment', $invoice->hasPayment);
          $invoices[$key]['hasPayment']['formattedValue'] = $this->utils->getFormatValueHasPayment($invoice->hasPayment, $invoice->dueDate);

          // TODO revisar si es requerido trabajar con el peso.
          uasort($invoices[$key], ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
          foreach ($invoices[$key] as $attr => $value) {
            if (isset($value['weight'])) {
              unset($invoices[$key][$attr]['weight']);
            }
          }
        }
      }
      else {
        $invoices = NULL;
      }
    }
    return [
      'invoiceList' => $invoices,
      'urlDownload' => $billing->getFormatUrlDownload($id, $idType),
    ];
  }

  /**
   * Format the reponse with the block configuarion values.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function formatFieldInvoiceId($field, $invoiceId, $invoiceSerial) {
    $config = $this->configBlock["fields"][$field];
    $data['label'] = isset($config['label']) ? $config['label'] : $config['title'];
    $data['value'] = $invoiceId . '_' . $invoiceSerial;
    $data['formattedValue'] = $invoiceId;
    $data['show'] = isset($config['show']) ? $config['show'] ? TRUE : FALSE : FALSE;
    $data['weight'] = isset($config['weight']) ? $config['weight'] : 0;
    return $data;
  }

}
