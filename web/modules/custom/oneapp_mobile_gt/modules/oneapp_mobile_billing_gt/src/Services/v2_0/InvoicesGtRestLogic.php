<?php

namespace Drupal\oneapp_mobile_billing_gt\Services\v2_0;

use Drupal\oneapp_mobile_billing\Services\v2_0\InvoicesRestLogic;

/**
 * Class InvoicesRestLogic.
 */
class InvoicesGtRestLogic extends InvoicesRestLogic {

  /**
   * Responds the invoices data.
   *
   * @param string $account_id
   *   account id.
   * @param string $account_id_type
   *   account id type.
   *
   * @return mixed
   *   The array with data structure.
   */
  public function get($account_id, $account_id_type) {
    $rows = [];
    $config = $this->configBlock['config'];
    $date_formatter = \Drupal::service('date.formatter');
    $billing = \Drupal::service('oneapp_mobile_billing.billing_service');

    // Get invoices.
    $invoices = $billing->getInvoicesData($account_id, $account_id_type);

    if (isset($invoices["noData"]) && (($invoices["noData"]['value'] == 'empty') || ($invoices['noData']['value'] == 'no_access'))) {
      return $invoices;
    }

    if (!isset($invoices['noData'])) {
      if (isset($invoices)) {
        $invoices = array_slice($invoices, 0, intval($config['limit']['limit']));
        $rows = [];
        foreach ($invoices as $invoice) {
          $row = [];
          foreach ($this->configBlock['history'] as $id => $field) {
            $row[$id] = [
              'label' => $field['label'],
              'show' => ($field['show']) ? TRUE : FALSE,
            ];

            switch ($id) {
              case 'invoiceId':
                $row[$id]['value'] = isset($invoice->invoiceId) ? $invoice->invoiceId . '_' . $invoice->invoiceSerial : '';
                $row[$id]['formattedValue'] = isset($invoice->invoiceId) ? $invoice->invoiceId : '';
                break;

              case 'billingPeriod':
                $start_date_time = $invoice->billingPeriod->startDateTime;
                $end_date_time = $invoice->billingPeriod->endDateTime;

                $row[$id]['value'] = [
                  'startDateTime' => $start_date_time,
                  'endDateTime' => $end_date_time,
                ];
                $row[$id]['formattedValue'] = $date_formatter->format(strtotime($end_date_time), $config['date']['formatPeriod']);
                break;

              case 'invoiceAmount':
                $row[$id]['value'] = $invoice->invoiceAmount;
                $row[$id]['formattedValue'] = $this->utils->formatCurrency($invoice->invoiceAmount, $invoice->currencyId);
                break;

              case 'dueAmount':
                $row[$id]['value'] = $invoice->dueAmount;
                $row[$id]['formattedValue'] = $this->utils->formatCurrency($invoice->dueAmount, $invoice->currencyId);
                break;

              case 'dueDate':
                $row[$id]['value'] = $invoice->dueDate;
                $row[$id]['formattedValue'] = $date_formatter->format(strtotime($invoice->dueDate), $config['date']['format']);
                break;

              case 'hasPayment':
                $row[$id]['value'] = $invoice->hasPayment;
                $row[$id]['formattedValue'] = $this->utils->getFormatValueHasPayment($invoice->hasPayment, $invoice->dueDate);
                break;
            }
          }
          $rows[] = $row;
        }
      }

      return [
        'invoiceList' => $rows,
        'urlDownload' => $billing->getFormatUrlDownload($account_id, $account_id_type),
      ];
    }
    else {
      return $invoices;
    }
  }

}
