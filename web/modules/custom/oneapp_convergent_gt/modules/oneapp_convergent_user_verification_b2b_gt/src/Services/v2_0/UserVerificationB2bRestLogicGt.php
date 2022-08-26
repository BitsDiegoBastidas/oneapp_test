<?php

namespace Drupal\oneapp_convergent_user_verification_b2b_gt\Services\v2_0;

use Drupal\oneapp_convergent_user_verification_b2b\Services\v2_0\UserVerificationB2bRestLogic;

/**
 * Class UserVerificationB2bRestLogicPa.
 */
class UserVerificationB2bRestLogicGt extends UserVerificationB2bRestLogic  {

  /**
   * {@inheritdoc}
   */
  public function getValidationDataHome($data) {
    $invoices = $this->service->callInvoicesHomeApi($data['idType']);
    if (!empty($invoices->invoices)) {
      $last_three_invoices = array_slice($invoices->invoices, 0, 3);
      foreach ($last_three_invoices as $invoice) {
        if (!empty($invoice->number) && $invoice->number == $data['invoiceId']) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidationDataMobile($data) {
    $invoices = $this->service->callInvoicesMobileApi($data['idType']);
    if (!empty($invoices->invoices)) {
      $last_three_invoices = array_slice($invoices->invoices, 0, 3);
      foreach ($last_three_invoices as $invoice) {
        if (!empty($invoice->number) && $invoice->number == $data['invoiceId']) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
