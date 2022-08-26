<?php

namespace Drupal\oneapp_rest_gt\Services\v2_0;

use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\UnauthorizedHttpException;
use Drupal\oneapp_rest\Services\AccessAndCheckService;

/**
 * Validate the services access...
 */
class AccessAndCheckServiceGt extends AccessAndCheckService {


  /**
   * {@inheritdoc}
   */
  public function validateAccessId($request, $id, $account_id_type = NULL) {
    $simple_auth_settings = \Drupal::config('adf_simple_auth.settings')->getRawData();

    if (isset($simple_auth_settings['activate']) && $simple_auth_settings['activate']) {
      $jwt_service = \Drupal::service('adf_simple_auth.jwt');
      $mobile_utils_service = \Drupal::service('oneapp.mobile.utils');
      try {
        $payload = $jwt_service->getTokenPayload($request);
        if (isset($payload)) {
          if ($id !== "me") {
            $int_rec = $payload->intRec ?? '';
            $this->setIntRec($int_rec);
            if (isset($payload->aL)) {
              $account_list = json_decode($payload->aL);
              foreach ($account_list as $value) {
                if (isset($value->baL)) {
                  $billing_accounts = $value->baL;
                  foreach ($billing_accounts as $billing_account) {
                    $subscriptions = $billing_account->sL;
                    if ($billing_account->baId == $id) {
                      $this->setAccountData($billing_account, $value);
                      return TRUE;
                    }
                    $ps_id = !empty($billing_account->psId) ? $mobile_utils_service->getFormattedMsisdn($billing_account->psId) : '';
                    if (!empty($ps_id) && (strpos($ps_id, $id) !== FALSE || strpos($id, $ps_id) !== FALSE)) {
                      $this->setAccountData($billing_account, $value);
                      return TRUE;
                    }
                    else {
                      foreach ($subscriptions as $subscription) {
                        if (isset($subscription->msisdnL)) {
                          foreach ($subscription->msisdnL as $msisdn_account) {
                            // Delete prefix country from accountId.
                            $msisdn = $mobile_utils_service->getFormattedMsisdn($msisdn_account->msisdn);
                            if (!empty($msisdn) && (strpos($msisdn, $id) !== FALSE || strpos($id, $msisdn) !== FALSE)) {
                              $this->setAccountData($billing_account, $value);
                              return TRUE;
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
            if (isset($simple_auth_settings['activateDarCheck']) && $simple_auth_settings['activateDarCheck']) {
              // If all accounts (allAcc) aren't available in token.
              if (isset($payload->allAcc) && $payload->allAcc == "false") {
                $accounts_service = \Drupal::service('oneapp_convergent_accounts.v2_0.accounts');
                // Call Digital account record service to get all user accounts.
                $dar_response = $accounts_service->getAccountListByTokenPayload($payload, 'true');
                $this->account = $this->findAccount($dar_response, $id);
                return $this->validateAccessByAccountInDar($dar_response, $id, $account_id_type);
              }
            }
            if (isset($simple_auth_settings['activeTigoidCheck']) && $simple_auth_settings['activeTigoidCheck']) {
              // Check if authenticate with password and user.
              if (isset($payload->hasPwd) && $payload->hasPwd == "true") {
                return $this->validateAccessByAccountInTigoid($payload, $id);
              }
              // Check if HE, OTP or SL.
              if (isset($payload->phone) && $mobile_utils_service->getFormattedMsisdn($payload->phone) ==
                $mobile_utils_service->getFormattedMsisdn($id)) {
                return TRUE;
              }
              // Check if SL.
              if (isset($payload->{"custom:UUID"})) {
                return $this->validateAccessByAccountInTigoid($payload, $id);
              }
            }
          }
          else {
            if (isset($payload->{"custom:UUID"})) {
              return TRUE;
            }
          }
        }
      }
      catch (\Exception $e) {
        $error = new ErrorBase();
        $error->getError()->set('message', $e->getMessage());
        throw new UnauthorizedHttpException($error, $e);
      }
    }
    else {
      return TRUE;
    }
    $error = new ErrorBase();
    $error->getError()->set('message', 'You dont have permissions to check the account info.');
    throw new UnauthorizedHttpException($error);
  }


  /**
   * {@inheritdoc}
   */
  public function validateAccessByAccountInDar($dar_response, $id, $account_id_type = NULL) {
    if (isset($dar_response["accountList"]) && !empty($dar_response["accountList"])) {
      $mobile_utils_service = \Drupal::service('oneapp.mobile.utils');
      foreach ($dar_response["accountList"] as $account) {
        // Validate home account.
        if (isset($account["businessUnit"]) && $account["businessUnit"] == 'home') {
          if (isset($account_id_type) && $account_id_type != "") {
            if ($account_id_type == "billingaccounts") {
              if (isset($account["billingAccountId"]) && $account["billingAccountId"] == $id) {
                return TRUE;
              }
              elseif (isset($account["primarySubscriberId"]) &&
                $mobile_utils_service->isPrimarySubscriberId($id) && $account["primarySubscriberId"] == $id) {
                return TRUE;
              }
            }
            elseif ($account_id_type == "subscribers") {
              if (isset($account["primarySubscriberId"]) &&
                $mobile_utils_service->getFormattedMsisdn($account["primarySubscriberId"]) == $id) {
                return TRUE;
              }
            }
            elseif ($account_id_type == "contracts") {
              if (isset($account["contractNumber"]) && $account["contractNumber"] == $id) {
                return TRUE;
              }
            }
          }
        }
        if (isset($account["msisdn"])) {
          $formatted_msisdn = $mobile_utils_service->getFormattedMsisdn($account["msisdn"]);
          if ($formatted_msisdn == $id) {
            return TRUE;
          }
        }
      }
    }
    $error = new ErrorBase();
    $error->getError()->set('message', 'Your current account is not available in digital account record.');
    throw new UnauthorizedHttpException($error);
  }

}
