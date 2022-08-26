<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0;

use Drupal\oneapp_convergent_upgrade_plan\Services\v2_0\UpgradePlanMobileSendRestLogic;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UpgradePlanMobileSendRestLogic.
 */
class UpgradePlanMobileSendGtRestLogic extends UpgradePlanMobileSendRestLogic {

  /**
   * Default utils oneapp.
   *
   * @var \Drupal\oneapp\Services\UtilsService
   */
  protected $utils;

  /**
   * Default utils oneapp mobile.
   *
   * @var \Drupal\oneapp_mobile_gt\Services\UtilsServiceGt
   */
  protected $mobileUtils;

  /**
   * Default utils oneapp convergent - Upgrade.
   *
   * @var \Drupal\oneapp_convergent_upgrade_plan\Services\UtilService
   */
  protected $upgradeUtils;

  /**
   * Default utils oneapp home.
   *
   * @var \Drupal\oneapp_home_gt\Services\UtilsGtService
   */
  protected $homeUtils;

  /**
   * Default configuration block.
   *
   * @var mixed
   */
  protected $configBlock;

  /**
   * Data Service.
   *
   * @var \Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradeServiceGt
   */
  protected $service;

  /**
   * Upgrade Recommended.
   *
   * @var \Drupal\oneapp_convergent_upgrade_plan\Services\v2_0\UpgradeRecommendedOffersMobileRestLogic
   */
  protected $upgradeRecommended;


  /**
   * The Drupal Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactoryService;

  /**
   * Default adf_simple_auth.
   *
   * @var \Drupal\adf_simple_auth\Services\AdfJwtService
   */
  protected $adfSimpleAuth;

  /**
   * Default mobile accounts.
   *
   * @var \Drupal\oneapp_mobile_gt\Services\AccountsServiceGt
   */
  protected $mobileAccounts;

  public function sendUpgradePlan($msisdn, $body) {
    $msisdn = $this->mobileUtils->modifyMsisdnCountryCode($msisdn, false);
    return $this->service->sendPlanMobile($msisdn, $body);
  }

  public function sendUpgradePlanTest($msisdn, $body, $error = false) {
    if ($error) {
      return (object) array(
        'error' => (object) array(
          'errorCode' => 503,
          'errorType' => 'COM',
          'code' => '003',
          'description' => 'No hay comunicacion con el servicio. (test)'
        )
      );
    }
    return (object) array(
      'msisdn' => $msisdn,
      'planId' => '1',
      'status' => 'OK',
      'description' => 'OK (test)'
    );
  }

  public function getAccountData(Request $request, $id_type, $id, $body = null) {
    $token_payload = $this->adfSimpleAuth->getTokenPayload($request);
    $contracted_plan_details = $this->service->getContractedPlanDetails($id);
    $client_account_general_info = $this->mobileAccounts->getClientAccountGeneralInfo($id);

    $account_data = (object) [
      // Token
      'tokenData' => $token_payload,
      // Client Account General Info => Client
      'clientData' => !empty($client_account_general_info->ClientType)
        ? $client_account_general_info->ClientType
        : (object) [],
      // Client Account General Info => Contract
      'currentPlanData' => !empty($client_account_general_info->contracts->ContractType->accounts->AssetType)
        ? $client_account_general_info->contracts->ContractType->accounts->AssetType
        : (object) [],
      // Body
      'nextPlanData' => (object) $body,
      // Customer Account List
      'customerAccountList' => $this->service->getCustomerAccountList($id_type, $id),
    ];

    $account_data->currentPlanData->contractDetails = $contracted_plan_details;

    return $account_data;
  }

  /**
   * updateCurrentPlan
   * @param $id
   * @param $body
   * @param string $id_type
   * @param Request|null $request
   * @return mixed
   * @throws \Exception
   */
  public function updateCurrentPlanMobile($id, $body, $id_type = '') {
    $request = \Drupal::request();
    $account_data = $this->getAccountData($request, $id_type, $id, $body);
    $customer_account_list = &$account_data->customerAccountList;
    $customer_account_info = &$customer_account_list[0];
    $account_data->request = (object) $body;
    $request = $request ?? \Drupal::request();
    $query_params = $request->query->all() ?? [];
    $this->service->setConfig($this->configBlock);
    $notified = false;
    try {

      $config_error = (isset($this->configBlock['confirmationUpgradePlanMobile']['error']['fields'])) ?
        $this->configBlock['confirmationUpgradePlanMobile']['error']['fields'] : [];

      $account_info_formatted = $this->service->formatCustomerAccountList($customer_account_list);

      foreach ($account_info_formatted as $key => $value) {
        if (empty($customer_account_info->$key)) {
          $customer_account_info->$key = $value;
        }
      }

      if (!empty($query_params['test'])) {
        $this->response = $this->sendUpgradePlanTest($customer_account_info->msisdn, $body['requestBody'], !empty($query_params['error']));
      }
      else {
        $this->response = $this->sendUpgradePlan($customer_account_info->msisdn, $body['requestBody']);
      }

      $error = (empty($this->response) || !empty($this->response->error) || http_response_code() < 200 || http_response_code() >= 400)
        ? TRUE
        : FALSE;
    }
    catch (\Exception $e) {
      $error = TRUE;
    }

    $response_data = $this->getData($account_data);
    $notified = $this->service->sendEmail($response_data, $error);

    if ($error) {
      $this->response->contractId = 'Error';
      $response_data = [
        'result' => [
          'label' => (!empty($config_error['title']['label'])) ? $config_error['title']['label'] : '',
          'formattedValue' => (!empty($config_error['desc']['label'])) ? $config_error['desc']['label'] : '',
          'value' => FALSE,
          'show' => (!empty($config_error['title']['show'])) ? TRUE : FALSE,
        ],
      ];
    }
    else {
      $this->response->contractId = $account_info_formatted['agreementId'];
      $account_data->response = $this->response;
    }

    $response_data['result']['notified'] = $notified;

    $fields_to_log = [
      'client_name' => $account_info_formatted['fullName'] ?? 'Error',
      'service_number' => $account_info_formatted['primarySubscriberId'] ?? 'Error',
      'bundle_plan' => $body['bundle_id'] ?? '',
      'name_plan' => $body['name'] ?? '',
      'data' => null,
      'plan' => $body['name'] ?? '',
      'lead_id' => $this->response->leadId ?? '',
      'contract_id' => $this->response->contractId ?? 'Error',
    ];

    $this->addLog($fields_to_log);

    return $response_data;
  }

  /**
   * Añade un log de una transacción (Insert Logs)
   * @param $fields
   * @return bool|\Drupal\Core\Database\StatementInterface|int|null
   */
  public function addLog($fields) {
    $fields['date'] = date('Y-m-d H:i:s');
    $fields['business_unit'] = 'MOBILE';
    try {
      $return = \Drupal::database()
        ->insert('oneapp_convergent_upgrade_plan_gt_log')
        ->fields($fields)
        ->execute();
      return $return;
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getData($data) {

    $_data['id']               = $data->customerAccountList[0]->msisdn ?? '';
    $_data['billingAccountId'] = $data->customerAccountList[0]->billingAccountId ?? '';
    $_data['inmediate']        = false;
    $_data['planName']         = $data->nextPlanData->bundle_id ?? '';
    $_data['formattedValue']   = $data->nextPlanData->name ?? '';
    $_data['monthlyAmount']    = $data->nextPlanData->fee ?? '';
    $_data['activateDate']     = $data->currentPlanData->contractDetails->nextBillingDate ?? '';

    return parent::getData($_data);
  }

  /**
   * {@inheritdoc}
   */
  public function validationData($data) {
    $required = [
      'currentPlanId',
      'planId',
      'plantType',
      'planDescription',
      'planResource',
      'parameterName',
      'parameterValue',
    ];
    foreach ($required as $item) {
      if (empty($data['requestBody'][$item])) {
        throw new \Exception('Missing \'' . $item . '\' param in request body.', 400);
      }
    }
    return $data;
  }
}
