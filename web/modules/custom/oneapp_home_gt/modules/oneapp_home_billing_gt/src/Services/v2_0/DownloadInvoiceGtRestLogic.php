<?php

namespace Drupal\oneapp_home_billing_gt\Services\v2_0;

use Drupal\oneapp_home_billing\Services\v2_0\DownloadInvoiceRestLogic;

/**
 * Class DownloadInvoiceRestLogic.
 */
class DownloadInvoiceGtRestLogic extends DownloadInvoiceRestLogic {



  /**
   * Get pdf invoice.
   *
   * @return file
   *   Return pdf file.
   */
  public function get($id, $id_type, $invoice_id) {
    $arr = explode('_', $invoice_id);
    $invoice_i_d = $arr[0];
    $invoice_serial = $arr[1];
    return $this->service->getPdfDataGt($id, $id_type, $invoice_i_d, $invoice_serial);
  }

}
