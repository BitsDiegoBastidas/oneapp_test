<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0;

use Dompdf\Dompdf;
use Drupal\oneapp\Services\UtilsService;
use Drupal\oneapp_home_gt\Services\UtilsGtService;
use Drupal\oneapp_convergent_upgrade_plan\Services\v2_0\UpgradePlanSendRestLogic;
use Drupal\oneapp_convergent_upgrade_plan\Services\UtilService;
use Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradeServiceGt;
use Drupal\oneapp_mailer\Services\v1_0\OneappMailerService;

/**
 * Class UpgradePlanSendRestLogic.
 */
class UpgradePlanSendGtRestLogic extends UpgradePlanSendRestLogic {

  /**
   * @var UtilsService
   */
  protected $utils;

  /**
   * @var UtilsGtService;
   */
  protected $homeUtils;

  /**
   * @var UtilService
   */
  protected $upgradeUtils;

  /**
   * @var UpgradeServiceGt
   */
  protected $service;

  /**
   * upgrade plan endpoint response
   * @var array|object
   */
  protected $response;

  /**
   * @var string
   */
  protected $content;

  /**
   * @param array $data
   * @return object
   * @throws \Exception
   */
  public function updateClientCurrentPlan($data) {
    $this->request = $this->getUpdateClientCurrentPlanBody($data);
    return $this->service->updateClientCurrentPlanApi($this->request);
  }

  /**
   * {@inheritdoc}
   */
  public function getUpdateClientCurrentPlanBody($data) {
    return [
      "processLead" => TRUE,
      "contractId" => $data['agreementId'],
      "comments" => "",
      "contact" => [
        [
          "clientCode" => $data["displayId"],
          "name" => $data['fullName'],
          "email" => $data['email'],
          "phone" => $data['displayId'],
          "benefitsPhoneNumber" => $data['displayId'],
        ]
      ],
      "scoreValidation" => [
        [
          "hasDebt" => FALSE,
          "qualificationScore" => TRUE,
        ]
      ],
      "channel" => 'oneApp',
      "quota" => t('Q. ') . $data['price'],
      "productName" => $data['productName'],
      'offer' => $data['plans'],
    ];
  }

  /**
   * @param $data
   * @throws \Exception
   */
  public function validationData($data) {
    $message = "";
    if (empty($data['bundle_id'])) {
      $message = "Missing bundle_id param in request body.";
    }
    elseif (empty($data['name'])) {
      $message = "Missing name param in request body.";
    }
    elseif (empty($data['price'])) {
      $message = "Missing price param in request body.";
    }
    elseif (empty($data['plans'])) {
      $message = "Missing plans param in request body.";
    }
    if (!empty($message)) {
      throw new \Exception($message, 400);
    }
  }

  /**
   * @param array $residential_offers Array returned from UpgradeServiceGt::getRecommendProductsData($id)
   * @return array Array of offers formated to send to upgrade
   */
  public static function formatRecommendedOffers($residential_offers = []) {
    $offers = [];
    if (!empty($residential_offers)) {
      foreach ($residential_offers as $bundle) {
        if (is_object($bundle)) {
          $bundle = (array) $bundle;
        }
        if (!empty($bundle['plans'])) {
          $bundle_key = &$bundle['bundle'];
          $plans = (array) $bundle['plans'];
          foreach ($plans as $plan) {
            if (is_object($plan)) {
              $plan = (array) $plan;
            }
            $offers[$bundle_key][] = [
              'planId' => $plan['planCode'],
              'addOnId' => $plan['productCode'],
              'distributor' => '',
              'vendor' => '',
              'supplementaryOffering' => [],
              'type' => 'PRIMARY',
              'technology' => $plan['planType'],
            ];
          }
          $additional_plans = !empty($bundle['additionalPlans']) ? (array) $bundle['additionalPlans'] : [];
          foreach ($additional_plans as $plan) {
            if (is_object($plan)) {
              $plan = (array) $plan;
            }
            $offers[$bundle_key][] = [
              'planId' => $plan['planCode'],
              'addOnId' => $plan['productCode'],
              'distributor' => '',
              'vendor' => '',
              'supplementaryOffering' => [],
              'type' => 'SECONDARY',
              'technology' => $plan['planType'],
            ];
          }
        }
      }
    }
    return $offers;
  }

  /**
   * updateCurrentPlan
   * @param string $id
   * @param array $body
   * @param string $id_type
   * @return mixed
   * @throws \Exception
   */
  public function updateCurrentPlanHome($id, $body, $id_type) {
    $query_params = \Drupal::request()->query->all();
    $customer_account_list = $this->service->getCustomerAccountList($id_type, $id);
    $customer_account_info = & $customer_account_list[0];
    $customer_account_info->request = (object) $body;
    $notified = FALSE;
    try {
      // Format customer account information
      $account_info_formatted = $this->service->formatCustomerAccountList($customer_account_list);
      $account_info_formatted['plans'] = $body['plans'];
      $account_info_formatted['price'] = $body['price'];
      $account_info_formatted['productName'] = $body['name'];
      // Call update plan method
      $this->response = $this->updateClientCurrentPlan($account_info_formatted);

      $customer_account_info->response = $this->response;

      if (!empty($this->response->requestProcessed)) {
        $email_to = !empty($query_params['email_to'])
          ? $query_params['email_to']
          : $this->adfSimpleAuth->getEmail();
        $notified = $this->sendNotification($email_to, $customer_account_info);
        // Send copies
        if (!empty($this->configBlock['emailSetting']['config']['cc_to'])) {
          $cc_to = explode(',', $this->configBlock['emailSetting']['config']['cc_to']);
          foreach ($cc_to as $email_to) {
            $this->sendNotification(trim($email_to), $customer_account_info);
          }
        }
      }

      $this->response->planType = 'bundle';
      $this->response->planName = $body['name'];
      $this->response->customerAccountId = $account_info_formatted['customerAccountId'];
      $this->response->productsPrice = $body['price'];

      $response_data = $this->getData((array) $this->response);
    }
    catch (\Exception $e) {
      $config_error = (isset($this->configBlock['confirmationUpgradePlan']['error']['fields']))
        ? $this->configBlock['confirmationUpgradePlan']['error']['fields']
        : [];

      $response_data = [
        'result' => [
          'label' => (!empty($config_error['title']['label'])) ? $config_error['title']['label'] : '',
          'formattedValue' => (!empty($config_error['desc']['label'])) ? $config_error['desc']['label'] : '',
          'value' => FALSE,
          'show' => (!empty($config_error['title']['show'])) ? TRUE : FALSE,
        ],
      ];
    }

    $fields_to_log = [
      'transaction_id' => $customer_account_info->response->transactionId ?? '',
      'client_name' => $account_info_formatted['fullName'] ?? 'Error',
      'service_number' => $account_info_formatted['primarySubscriberId'] ?? 'Error',
      'bundle_plan' => $body['bundle_id'] ?? '',
      'name_plan' => $body['name'] ?? '',
      'data' => NULL,
      'plan' => $body['name'] ?? '',
      'lead_id' => $this->response->leadId ?? 'Error',
      'contract_id' => $this->response->contractId ?? 'Error',
    ];

    $this->addLog($fields_to_log);

    $response_data['result']['notified'] = $notified;

    return $response_data;
  }

  /**
   * {@inheritdoc}
   */
  public function getData($data) {
    $config = (!empty($this->configBlock['confirmationUpgradePlan'])) ?
      $this->configBlock['confirmationUpgradePlan'] : [];

    $ticket_zendesk = FALSE;

    $config_result = (!empty($config['cardConfirmation']['fields'])) ?
      $config['cardConfirmation']['fields'] : [];

    $config_details = (!empty($config['cardDetail']['fields'])) ?
      $config['cardDetail']['fields'] : [];

    $date = new \DateTime('now');
    $format_date = (!empty($config_details['activateDate']['formatDate'])) ? $config_details['activateDate']['formatDate'] : 'short';

    if ($appointment_date = (!empty($data['appointmentRequired']) && !empty($data['startInstallationDate']))) {
      $installation_time = \DateTime::createFromFormat(DATE_ATOM, $data['finishInstallationDate']);
      $installation_date = \DateTime::createFromFormat(DATE_ATOM, $data['startInstallationDate']);
      $installation_format_date = !empty($config_details['activateDate']['formatDate'])
        ? $config_details['activateDate']['formatDate']
        : 'short';
    }

    $email = $this->adfSimpleAuth->getEmail();

    if (empty($email)) {
      $email = FALSE;
    }

    $result = [
      'label' => (!empty($config_result['title']['label'])) ? $config_result['title']['label'] : '',
      'formattedValue' => (!empty($config_result['desc']['label'])) ? $config_result['desc']['label'] : '',
      'show' => (!empty($config_result['title']['show'])) ? TRUE : FALSE,
    ];

    return [
      'planType' => $data['planType'],
      'ticketZendesk' => $ticket_zendesk,
      'result' => [
        'label' => $result['label'],
        'formattedValue' => $result['formattedValue'],
        'value' => TRUE,
        'email' => $email,
        'show' => $result['show'],
      ],
      'confirmationDetails' => [
        'title' => [
          'value' => (!empty($config_details['title']['label'])) ? $config_details['title']['label'] : '',
          'show' => (!empty($config_details['title']['show'])) ? TRUE : FALSE,
        ],
        'plan' => [
          'label' => (!empty($config_details['plan']['label'])) ? $config_details['plan']['label'] : '',
          'value' => $this->upgradeUtils->getFormatLowerCase($data['planName'], TRUE),
          'show' => (!empty($config_details['plan']['show'])) ? TRUE : FALSE,
        ],
        'account' => [
          'label' => (!empty($config_details['account']['label'])) ? $config_details['account']['label'] : '',
          'value' => $this->upgradeUtils->getFormatAccount($data['customerAccountId']),
          'show' => (!empty($config_details['account']['show'])) ? TRUE : FALSE,
        ],
        'price' => [
          'label' => (!empty($config_details['price']['label'])) ? $config_details['price']['label'] : '',
          'value' => $data['productsPrice'],
          'formattedValue' => $this->utils->formatCurrency($data['productsPrice'], TRUE),
          'show' => (!empty($config_details['price']['show'])) ? TRUE : FALSE,
        ],
        'activateDate' => [
          'label' => (!empty($config_details['activateDate']['label'])) ? $config_details['activateDate']['label'] : '',
          'value' => $this->homeUtils->formatDate($date->getTimestamp(), $format_date),
          'show' => (!empty($config_details['activateDate']['show'])) ? TRUE : FALSE,
        ],
        'footer' => [
          'value' => (!empty($config_details['footer']['label'])) ? $config_details['footer']['label'] : '',
          'show' => (!empty($config_details['footer']['show'])) ? TRUE : FALSE,
        ],
        'installationDate' => [
          'label' => $config_details['installationDate']['label'] ?? '',
          'value' => $appointment_date ? $this->homeUtils->formatDate($installation_date->getTimestamp(), $installation_format_date) : '',
          'show' => $appointment_date && !empty($config_details['installationDate']['show']),
        ],
        'installationTime' => [
          'label' => $config_details['installationTime']['label'] ?? '',
          'value' => $appointment_date ? $installation_time->format('h:i A') : '',
          'show' => $appointment_date && !empty($config_details['installationTime']['show']),
        ],
        'appointmentId' => [
          'label' => $config_details['appointmentId']['label'] ?? '',
          'value' => $data['appointmentID'],
          'show' => $appointment_date && !empty($config_details['appointmentId']['show']),
        ],
        'installationNotice' => [
          'value' => $config_details['installationNotice']['label'] ?? '',
          'show' => $appointment_date && !empty($config_details['installationNotice']['show']),
        ],
      ],
    ];
  }


  /**
   * Añade un log de una transacción (Insert Logs)
   * @param $fields
   * @return bool|\Drupal\Core\Database\StatementInterface|int|null
   */
  public function addLog($fields) {
    $fields['date'] = date('Y-m-d H:i:s');
    $fields['business_unit'] = 'HOME';
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
   * @param object $data
   * @return string
   */
  public function buildContent($template, $data) {

    if (!empty($this->content)) {
      return $this->content;
    }

    $values = [
      '{{data.DocDateRequest}}' => date('Y-m-d'),
      '{{data.DocDepart}}' => '',
      '{{data.DocState}}' => '',
      '{{data.DocClientType}}' => !empty($data->partyOwner->partyType) ? 'Existente' : '',
      '{{data.DocTypeOfRequest}}' => '',
      '{{data.DocName}}' => $data->partyOwner->givenName ?? '',
      '{{data.DocSurname}}' => $data->partyOwner->familyName ?? '',
      '{{data.DocBirthDate}}' => $data->partyOwner->dateOfBirth ?? '',
      '{{data.DocDateFormat}}' => !empty($data->partyOwner->dateOfBirth) ? (date('Y') - substr($data->partyOwner->dateOfBirth, 0, 4)) : '',
      '{{data.DocSex}}' => !empty($data->partyOwner->gender) ? substr($data->partyOwner->gender, 0, 1) : '',
      '{{data.DocNationality}}' => '',
      '{{data.DocCivilStatus}}' => '',
      '{{data.DocTypeDocument}}' => $data->partyOwner->identificationPartyOwner->documentType ?? '',
      '{{data.docDpi}}' => $data->partyOwner->identificationPartyOwner->documentNumber ?? '',
      '{{data.ExpeditionPlace}}' => '',
      '{{data.DocNit}}' => '',
      '{{data.DocPhone}}' => $data->partyOwner->contactMediumPartyOwner->phoneList[0]->phone ?? '',
      '{{data.DocEmailAddress}}' => $data->partyOwner->contactMediumPartyOwner->emailList[0]->email ?? '',
      '{{data.DocAddressInstall}}' => $data->response->installationAddress,
      '{{data.DocZone}}' => '',
      '{{data.docNumNodo}}' => '',
      '{{data.docNumPoste}}' => '',
      '{{data.docNumSector}}' => '',
      '{{data.docNumContador}}' => '',
      '{{data.docCoordenadas}}' => '',
      '{{data.DocPlanName}}' => $data->request->name ?? '',
      '{{DocPlanName}}' => '',
      '{{manual}}' => '',
      '{{data.DocTotal|number_format(2, \'.\')}}' => $data->request->price ?? '',
    ];

    $this->content = str_replace(array_keys($values), $values, $template);
    return $this->content;
  }

  /**
   * @param object $data
   * @return bool
   */
  public function sendNotification($email_to, $data) {

    $name_from = $this->configBlock['emailSetting']['config']['fromname'] ?? '';
    $email_from = $this->configBlock['emailSetting']['config']['from'] ?? '';
    $subject = $this->configBlock['emailSetting']['single']['subject'] ?? '';
    $template = $this->configBlock['emailSetting']['single']['body']['value'] ?? '';
    $template = str_replace(['{{ ',' }}'], ['{{','}}'], $template);
    $body = $this->buildContent($template, $data);

    /** @var OneappMailerService $mailer */
    $mailer = $this->service->getOneappMailer();
    try {
      return $mailer->sendMail($name_from, $email_from, $email_to, $subject, $body);
    }
    catch (\Exception $e) {
      return FALSE;
    }
  }
}
