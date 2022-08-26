<?php

namespace Drupal\oneapp_home_billing_gt\Services\v2_0;



use Drupal\oneapp_home_billing\Services\v2_0\BalanceRestLogic;

/**
 * Class BalanceRestLogic.
 */
class BalanceGtRestLogic extends BalanceRestLogic {


  /**
   * Get data balance formated.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function get($id, $account_id_type) {

    $balance = $this->service->getBalance($id, $account_id_type);
    $this->balance = $balance;

    if (is_array($balance) && (($balance['noData']['value'] == 'empty') || ($balance['noData']['value'] == 'no_access'))) {
      return $balance;
    }

    $data = [];
    if ($balance) {
      $data['dueAmount'] = $this->formatField('dueAmount', $balance->dueAmount);
      $data['dueInvoicesCount'] = $this->formatField('dueInvoicesCount', $balance->dueInvoicesCount);
      $data['extendedDueDate'] = $this->formatField('extendedDueDate', $balance->extendedDueDate);

      $data['invoiceId'] = $this->formatFieldInvoiceId('invoiceId', $balance->invoiceId, $balance->invoiceSerial);
      $data['creationDate'] = $this->formatField('creationDate', $balance->creationDate);
      $data['dueDate'] = $this->formatField('dueDate', $balance->dueDate);
      $data['period'] = $this->formatField('period', $balance->period);

      // Rewrite by original value period.
      if (!empty($balance->endPeriod)) {
        $start_period = $this->utils->getFormattedValue($this->configBlock['fields']['period']['format'], $balance->startPeriod);
        $end_period = $this->utils->getFormattedValue($this->configBlock['fields']['period']['format'], $balance->endPeriod);
        $data['period']['formattedValue'] = t('@startPeriod a @endPeriod', ['@startPeriod' => $start_period, '@endPeriod' => $end_period]);
        unset($start_period);
        unset($end_period);
      }
      $data['hasPayment'] = $this->formatField('hasPayment', $balance->hasPayment);
      $data['hasPayment']['formattedValue'] = $this->utils->getFormatValueHasPayment($balance->hasPayment, $balance->dueDate);

      //uasort($data, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);

      $data['isDelinquent']['value'] = FALSE;
      // If user has not paid the last invoice.
      if (isset($balance) && !$balance->hasPayment) {
        $data['isDelinquent'] = ['value' => $this->utils->isExpiratedDate($balance->dueDate)];
      }
      if (isset($balance->dueInvoicesCount) && $balance->dueInvoicesCount > 1) {
        $data['isDelinquent'] = ['value' => TRUE];
      }
    }
    foreach ($data as $key => $value) {
      if (isset($value['weight'])) {
        unset($data[$key]['weight']);
      }
    }
    $data['pdfUrl'] = $this->service->getFormatUrlDownload($id, $account_id_type, $data['invoiceId']['value']);
    unset($period);
    unset($balance);
    return (array) $data;
  }

  /**
   * Format the reponse with the block configuarion values.
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function formatFieldInvoiceId($field, $invoice_id, $invoice_serial) {
    $config = $this->configBlock["fields"][$field];
    $data['label'] = isset($config['label']) ? $config['label'] : $config['title'];

    $data['value'] = $invoice_id . '_' . $invoice_serial;
    $value = $this->valueDescription($field, $invoice_id);
    $data['formattedValue'] = $this->utils->getFormattedValue($config['format'], $value);

    $data['show'] = (!empty($config['show'])) ? TRUE : FALSE;
    $data['weight'] = (!empty($config['weight'])) ? $config['weight'] : 0;
    return $data;
  }


}
