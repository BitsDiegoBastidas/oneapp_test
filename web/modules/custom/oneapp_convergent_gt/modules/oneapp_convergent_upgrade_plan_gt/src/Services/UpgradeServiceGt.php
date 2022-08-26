<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\oneapp_convergent_upgrade_plan\Services\UpgradeService;
use Drupal\oneapp_mailer\Services\v1_0\OneappMailerService;

/**
 * Class UpgradeServiceGt.
 *
 * @package Drupal\oneapp_convergent_upgrade_plan_gt\Services;
 */
class UpgradeServiceGt extends UpgradeService {

  /**
   * @var array|object
   */
  protected $DarInfo;
  /**
   * @var \Drupal\oneapp_endpoints\Services\Manager
   */
  protected $manager;
  /**
   * @var \Drupal\oneapp_home_gt\Services\UtilsGtService
   */
  protected $utils;
  /**
   * @var array|object
   */
  protected $configBlock;
  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactoryService;
  /**
   * @var \Drupal\adf_simple_auth\Services\AdfJwtService
   */
  protected $adfSimpleAuth;
  /**
   * @var \Drupal\aws_service\Services\v2_0\AwsApiManager
   */
  protected $aws;
  /**
   * @var \Drupal\oneapp_mailer\Services\v1_0\OneappMailerService
   */
  protected $oneappMailerSend;
  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * @var \Drupal\oneapp_convergent_accounts_gt\Services\v2_0\AccountsServiceGt
   */
  protected $accounts;
  /**
   * @var \Drupal\token\Token
   */
  protected $token;
  /**
   * @var \Drupal\oneapp_convergent_upgrade_plan\Services\UtilService
   */
  protected $upgradeUtils;
  /**
   * @var \Drupal\oneapp_mobile_billing_gt\Services\BillingServiceGt
   */
  protected $mobileBillingService;

  /**
   * @return \Drupal\oneapp_convergent_accounts_gt\Services\v2_0\AccountsServiceGt
   */
  public function getAccountsServiceGt() {
    return $this->accounts;
  }

  /**
   * @param integer $id subscriber_id
   * @param bool $count_records
   * @return array|bool
   */
  public function getRecommendProductsData($id, $count_records = FALSE) {

    $mobile_utils_service = \Drupal::service('oneapp.mobile.utils');
    $id = $mobile_utils_service->modifyMsisdnCountryCode($id, TRUE);

    try {
      $recommend_products = $this->getRecommendedPlansByScoringApi($id);
    }
    catch (\Exception $e) {
      return [];
    }

    if ($count_records) {
      if (!empty($recommend_products->residentialOffers)) {
        return TRUE;
      }
      return [];
    }

    if (!empty($recommend_products->residentialOffers)) {
      return $recommend_products->residentialOffers;
    }

    return [];
  }

  /**
   * @param $billing_account_id
   * @return object|null
   */
  public function getCustomerAccountByBillingAccountId($billing_account_id) {
    $customer_account_list = null;
    $dar_info = $this->getDarInfo($billing_account_id);
    if (!empty($dar_info)) {
      $customer_account_list = $dar_info->customerAccountList;
    }
    return $customer_account_list;
  }

  /**
   * @param string $billing_account_id
   * @return mixed
   */
  public function getDarInfo($billing_account_id = '') {
    if (!empty($this->DarInfo)) {
      return $this->DarInfo;
    }
    $this->DarInfo = $this->getDARByBillingaccountId($billing_account_id);
    return $this->DarInfo;
  }

  /**
   * @param $subscriber_id
   * @return object|null
   */
  public function getCustomerAccountBySubscriberId($subscriber_id) {
    $customer_account_list = null;
    $response = $this->getMasterAccount($subscriber_id);
    if (!empty($response)) {
      $customer_account_list = $response->customerAccountList;
    }
    return $customer_account_list;
  }

  /**
   * @param $id_type
   * @param $id
   * @return object
   * @throws \Exception
   */
  public function getCustomerAccountList($id_type, $id) {
    // Get customer account information
    if ($id_type == 'billingaccounts') {
      $customer_account_list = $this->getCustomerAccountByBillingAccountId($id);
    }
    elseif ($id_type == 'subscribers') {
      $customer_account_list = $this->getCustomerAccountBySubscriberId($id);
    }
    else {
      throw new \Exception('Missing billingaccounts as url param.', 400);
    }

    if (empty($customer_account_list)) {
      throw new \Exception('Customer Account List empty', 500);
    }

    return $customer_account_list;
  }

  /**
   * @param object $customer_account_list
   * @return array
   */
  public function formatCustomerAccountList($customer_account_list) {
    return [
      'customerAccountId' => $customer_account_list[0]->customerAccountId,
      'country' => $customer_account_list[0]->country,
      'partyOwnerType' => $customer_account_list[0]->partyOwner->partyType,
      'fullName' => $customer_account_list[0]->partyOwner->formattedName,
      'documentType' => $customer_account_list[0]->partyOwner->identificationPartyOwner->documentType,
      'documentNumber' => $customer_account_list[0]->partyOwner->identificationPartyOwner->documentNumber,
      'phone' => $customer_account_list[0]->partyOwner->contactMediumPartyOwner->phoneList[0]->phone ?? '',
      'email' => $customer_account_list[0]->partyOwner->contactMediumPartyOwner->emailList[0]->email ?? '',
      'billingAccountId' => $customer_account_list[0]->accountList[0]->billingAccountId ?? '',
      'businessUnit' => $customer_account_list[0]->accountList[0]->businessUnit ?? '',
      'billingType' => $customer_account_list[0]->accountList[0]->billingType ?? '',
      'primarySubscriberId' => $customer_account_list[0]->accountList[0]->primarySubscriberId ?? '',
      'msisdn' => $customer_account_list[0]->accountList[0]->subscriptionList[0]->msisdnList[0]->msisdn ?? '',
      'displayId' => $customer_account_list[0]->accountList[0]->displayId ?? '',
      'agreementId' => $customer_account_list[0]->accountList[0]->subscriptionList[0]->agreementId ?? '',
      'serviceAddress' => $customer_account_list[0]->accountList[0]->serviceAddress ?? '',
      'isActive' => $customer_account_list[0]->lifecycle->isActive,
      'status' => $customer_account_list[0]->lifecycle->status,
    ];
  }

  /**
   * @return OneappMailerService
   */
  public function getOneappMailer() {
    return $this->oneappMailerSend ?? \Drupal::service('oneapp.mailer.send');
  }

  /**
   * {@inheritdoc}
   */
  public function sendEmail($data = [], $error_case = FALSE) {

    $block = empty($error_case) ? 'single' : 'error';

    $email_setting = (!empty($this->configBlock['emailSetting'])) ?
      $this->configBlock['emailSetting'] : [];

    $from_name = (!empty($email_setting['config']['fromname'])) ?
      $email_setting['config']['fromname'] : '';

    $from_email = (!empty($email_setting['config']['from'])) ?
      $email_setting['config']['from'] : '';

    $adf_jwt_service = $this->adfSimpleAuth;
    $token_service = $this->token;

    $tokens = [
      'newPlan' => $data['confirmationDetails']['plan']['formattedValue'] ?? '{newPlan}',
      'userName' => "{$adf_jwt_service->getGivenNameUser()} {$adf_jwt_service->getFirstNameUser()}",
      'amount' => $data['confirmationDetails']['price']['formattedValue'] ?? '{amount}',
      'date' => $data['confirmationDetails']['activateDate']['value'] ?? '{date}'
    ];

    if (!empty($data['idType']) && $data['idType'] == 'subscribers') {
      if (isset($data['inmediate'])) {
        if ($data['inmediate']) {
          $tokens['textChangePlan'] = (!empty($email_setting[$block]['inmediate'])) ?
            $email_setting[$block]['inmediate'] : '';
        } else {
          $tokens['textChangePlan'] = (!empty($email_setting[$block]['notInmediate'])) ?
            $email_setting[$block]['notInmediate'] : '';
        }
      }
      if (!empty($data['data'])) {
        $data = $data['data'];
        $tokens['textConfirmationTitle'] = (!empty($data['confirmationDetails']['title']['value'])) ?
          $data['confirmationDetails']['title']['value'] : '';

        $tokens['textPlanLabel'] = (!empty($data['confirmationDetails']['plan']['label'])) ?
          $data['confirmationDetails']['plan']['label'] : '';

        $tokens['textPlanValue'] = (!empty($data['confirmationDetails']['plan']['value'])) ?
          $data['confirmationDetails']['plan']['value'] : '';

        $tokens['textPlanFormatted'] = (!empty($data['confirmationDetails']['plan']['formattedValue'])) ?
          $data['confirmationDetails']['plan']['formattedValue'] : '';

        $tokens['textAccountLabel'] = (!empty($data['confirmationDetails']['account']['label'])) ?
          $data['confirmationDetails']['account']['label'] : '';

        $tokens['textAccountValue'] = (!empty($data['confirmationDetails']['account']['value'])) ?
          $data['confirmationDetails']['account']['value'] : '';

        $tokens['textPriceLabel'] = (!empty($data['confirmationDetails']['price']['label'])) ?
          $data['confirmationDetails']['price']['label'] : '';

        $tokens['textPriceValue'] = (!empty($data['confirmationDetails']['price']['formattedValue'])) ?
          $data['confirmationDetails']['price']['formattedValue'] : '';

        $tokens['textActivateDateLabel'] = (!empty($data['confirmationDetails']['activateDate']['label'])) ?
          $data['confirmationDetails']['activateDate']['label'] : '';

        $tokens['textActivateDateValue'] = (!empty($data['confirmationDetails']['activateDate']['value'])) ?
          $data['confirmationDetails']['activateDate']['value'] : '';

      }
    }

    $subject = (!empty($email_setting[$block]['subject'])) ?
      $email_setting[$block]['subject'] : '';

    $body = (!empty($email_setting[$block]['body']['value'])) ?
      $email_setting[$block]['body']['value'] : '';

    $html_body = $token_service->replace($body, $tokens, [], new BubbleableMetadata());

    try {
      $params = [
        'from_name' => $from_name,
        'from_email' => $from_email,
        'to' => $adf_jwt_service->getEmail(),
        'subject' => $subject,
        'body' => $html_body,
      ];

      $mail_service = $this->oneappMailerSend;
      $mail_service->sendMail(
        $params['from_name'],
        $params['from_email'],
        $params['to'],
        $params['subject'],
        $params['body'],
        'email'
      );
      return TRUE;
    } catch (\Exception $exception) {
      return FALSE;
    }
  }

  public function getDetailsProfilingPlan($id) {
    try {
      return $this->manager
      ->load('oneapp_convergent_upgrade_plan_v2_0_upgrade_plan_profiling_endpoint')
      ->setParams(
        [
          'id' => $id,
        ]
      )
      ->setHeaders([])
      ->setQuery(['transaction' => 'upgrade'])
      ->sendRequest();
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
  }


  public function getRenewalsOffersDetails($id) {
    try {
      return $this->manager
      ->load('oneapp_convergent_upgrade_plan_v2_0_upgrade_plan_renewals_offers_endpoint')
      ->setParams(
        [
          'id' => $id,
        ]
      )
      ->setHeaders([])
      ->setQuery([])
      ->sendRequest();
    }
    catch (\Exception $e) {
      return $e->getMessage();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentPlanMobileByMsisdn($id) {
    try {
      return $this->manager
        ->load('oneapp_convergent_upgrade_plan_v2_0_update_mobile_current_plan_endpoint')
        ->setParams(['subscriberId' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $error_code = $e->getCode();
      throw new \Exception($message, $error_code);
    }
  }

  public function getCurrentDataPlanMobile($id) {
    try {
      return $this->manager
        ->load('oneapp_mobile_plans_v2_0_current_plan_endpoint')
        ->setParams(['msisdn' => $id])
        ->setHeaders([])
        ->setQuery([])
        ->sendRequest();
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $error_code = $e->getCode();
      throw new \Exception($message, $error_code);
    }
  }

  /**
   * @param $msisdn
   * @param $data
   * @return mixed
   */
  public function sendPlanMobile($msisdn, $data) {
    try {
      return $this->manager
        ->load('oneapp_convergent_upgrade_plan_v2_0_upgrade_plan_mobile_msisdn_endpoint')
        ->setParams(
          ['msisdn' => $msisdn]
        )
        ->setHeaders(['Content-Type' => 'application/json'])
        ->setQuery([])
        ->setBody($data)
        ->sendRequest();
    }
    catch (\Exception $e) {
      return (object) [
        'error' => [
          'code' => $e->getCode(),
          'errorCode' => $e->getCode(),
          'statusCode' => $e->getCode(),
          'message' => $e->getMessage(),
          'description' => $e->getMessage(),
        ]
      ];
    }
  }


  public function getContractedPlanDetails($msisdn) {
    try {
      $contracted_plan_details = $this->manager
        ->load('oneapp_mobile_plans_v2_0_current_by_contracts_endpoint')
        ->setParams(['msisdn' => $msisdn])
        ->setHeaders(['Content-Type' => 'application/json'])
        ->sendRequest();

      if (empty($contracted_plan_details->Envelope->Body->GetPostpaidContractDetailsResponse)) {
        throw new \Exception('La propiedad GetPostpaidContractDetailsResponse no existe', 500);
      }

      return $contracted_plan_details->Envelope->Body->GetPostpaidContractDetailsResponse;
    }
    catch (\Exception $e) {
      return (object) [
        'error' => [
          'code' => $e->getCode(),
          'errorCode' => $e->getCode(),
          'statusCode' => $e->getCode(),
          'message' => $e->getMessage(),
          'description' => $e->getMessage(),
        ]
      ];
    }
  }


  public function getCurrentPlanDetails($msisdn) {
    try {
      $current_plan_details = $this->manager
        ->load('oneapp_mobile_plans_v2_0_current_plan_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();

      return $current_plan_details;
    }
    catch (\Exception $e) {
      return (object) [
        'error' => [
          'code' => $e->getCode(),
          'errorCode' => $e->getCode(),
          'statusCode' => $e->getCode(),
          'message' => $e->getMessage(),
          'description' => $e->getMessage(),
        ]
      ];
    }
  }
}
