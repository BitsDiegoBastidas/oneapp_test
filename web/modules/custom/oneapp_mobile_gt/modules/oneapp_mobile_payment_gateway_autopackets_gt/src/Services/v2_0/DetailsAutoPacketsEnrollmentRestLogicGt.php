<?php

namespace Drupal\oneapp_mobile_payment_gateway_autopackets_gt\Services\v2_0;

use Drupal\oneapp_mobile_payment_gateway_autopackets\Services\v2_0\DetailsAutoPacketsEnrollmentRestLogic;

/**
 * Class DetailsAutoPacketsEnrollmentRestLogic.
 */
class DetailsAutoPacketsEnrollmentRestLogicGt extends DetailsAutoPacketsEnrollmentRestLogic {

  /**
   * return form
   *
   * @return array
   */
  public function getForm() {
    $billing_form = $this->utilsPayment->getBillingDataForm('packets', 'mobile');
    $new_card_form = $this->utilsPayment->getFormPayment($product_type);
    $billing_form_autopackets = (object) \Drupal::config("oneapp.payment_gateway.mobile_autopackets.config")->get("billing_form");
    $billing_form["billingDataForm"]["fullname"]["value"] = !empty($billing_form["billingDataForm"]["fullname"]["value"]) ? $billing_form["billingDataForm"]["fullname"]["value"] : $billing_form_autopackets->fullname["value"];
    $billing_form["billingDataForm"]["nit"]["value"] = !empty($billing_form["billingDataForm"]["nit"]["value"]) ? $billing_form["billingDataForm"]["nit"]["value"] : $billing_form_autopackets->nit["value"];
    $billing_form["billingDataForm"]["address"]["value"] = !empty($billing_form["billingDataForm"]["address"]["value"]) ? $billing_form["billingDataForm"]["address"]["value"] : $billing_form_autopackets->address["value"];
    if (isset($billing_form_autopackets->disable_email) && $billing_form_autopackets->disable_email) {
      $billing_form["billingDataForm"]["email"]['disable'] = TRUE;
      $billing_form["billingDataForm"]["email"]["value"] = $this->authTokenService->getEmail();
    }
    if ($billing_form) {
      $forms = [$new_card_form, $billing_form];
    }
    else {
      $forms = $new_card_form;
    }
    return $forms;
  }

}
