<?php

namespace Drupal\oneapp_mobile_billing_gt\Services\v2_0;

use Drupal\oneapp_mobile_billing\Services\v2_0\DownloadInvoiceRestLogic;

/**
 * Class DownLoadInvoiceRestLogic.
 */
class DownloadInvoiceGtRestLogic extends DownloadInvoiceRestLogic {


  /**
   * get the pdf data
   *
   * @param [string] $id
   * @param [string] $id_type
   * @param [string] $invoice_id
   * @return void
   */
  public function get($id, $id_type, $invoice_id) {
    $arr = explode('_', $invoice_id);
    $invoice_id = $arr[0];
    $invoice_serial = $arr[1];
    $billing = \Drupal::service('oneapp_mobile_billing.billing_service');
    return $billing->getPdf($id, $id_type, $invoice_id, $invoice_serial);
  }

}
