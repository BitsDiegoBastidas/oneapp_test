<?php

namespace Drupal\oneapp_mobile_billing_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_billing\Services\v2_0\BalanceRestLogic;

/**
 * Class BalanceRestLogic.
 */
class BalanceGtRestLogic extends BalanceRestLogic {

  /**
   * Responds to GET requests.
   *
   * @param string $billingAccountId
   *   Msisdn.
   *
   * @return array
   *   The associative array.
   */
  public function get($account_id, $account_id_type) {
    $rows = [];
    $date_formatter = \Drupal::service('date.formatter');
    $billing = \Drupal::service('oneapp_mobile_billing.billing_service');

    // Get debt balance.
    $this->balance = $billing->getBalance($account_id, $account_id_type);

    if (is_array($this->balance) && (($this->balance['noData']['value'] == 'no_access') || ($this->balance['noData']['value'] == 'empty'))) {
      return $this->balance;
    }

    foreach ($this->configBlock['debtBalance']['fields'] as $id => $field) {
      $row[$id] = [
        'label' => $field['label'],
        'show' => ($field['show']) ? TRUE : FALSE,
      ];

      switch ($id) {
        case 'invoiceId':
          $row[$id]['value'] = isset($this->balance->invoiceId) ? $this->balance->invoiceId . '_' . $this->balance->invoiceSerial : "---------";
          $row[$id]['formattedValue'] = $this->balance->invoiceId ?? "---------";
          break;

        case 'dueAmount':
          $due_amount = $this->balance->dueAmount;
          $row[$id]['value'] = $due_amount;
          $row[$id]['formattedValue'] = $this->utils->formatCurrency($due_amount, $this->balance->currencyId ?? NULL);
          break;

        case 'dueDate':
          $date = isset($this->balance->dueDate) && $this->balance->dueDate != "" ? $date_formatter->format(strtotime($this->balance->dueDate), 'custom', 'd M Y') : "---------";
          $row[$id]['value'] = $date;
          $row[$id]['formattedValue'] = $date;
          break;

        case 'dueInvoicesCount':
          $row[$id]['value'] = isset($this->balance->dueInvoicesCount) ? $this->balance->dueInvoicesCount : 0;
          $row[$id]['formattedValue'] = isset($this->balance->dueInvoicesCount) ? (string) $this->balance->dueInvoicesCount : '0';
          break;

        case 'billingCycle':
          $row[$id]['value'] = isset($this->balance->billingCycle) ? $this->balance->billingCycle : "---------";
          $row[$id]['formattedValue'] = isset($this->balance->billingCycle) ? $this->balance->billingCycle . ' ' . $field['description'] : "---------";
          break;

        case 'billingAccountId':
          $row[$id]['value'] = isset($this->balance->billingAccountId) ? $this->balance->billingAccountId : $account_id;
          $row[$id]['formattedValue'] = isset($this->balance->billingAccountId) ? $this->balance->billingAccountId : $account_id;
          break;

      }
      $rows[$id] = $row[$id];
    }
    $rows['isDelinquent'] = ['value' => FALSE];
    // If user has not paid the last invoice.
    if (isset($this->balance) && !$this->balance->hasPayment) {
      $rows['isDelinquent'] = ['value' => $this->utils->isExpiratedDate($this->balance->dueDate)];
    }
    if (isset($this->balance->dueInvoicesCount) && $this->balance->dueInvoicesCount > 1) {
      $rows['isDelinquent'] = ['value' => TRUE];
    }
    $rows['urlDownload'] = isset($this->balance->invoiceId) ? $billing->getFormatUrlDownload($account_id, $account_id_type, (isset($this->balance->invoiceId) ? $this->balance->invoiceId . '_' . $this->balance->invoiceSerial : "---------")) : null;
    return $rows;
  }


}
