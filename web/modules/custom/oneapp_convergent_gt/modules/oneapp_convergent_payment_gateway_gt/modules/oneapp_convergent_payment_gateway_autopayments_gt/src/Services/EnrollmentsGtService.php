<?php

namespace Drupal\oneapp_convergent_payment_gateway_autopayments_gt\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Drupal\oneapp_convergent_payment_gateway_autopayments\Services\EnrollmentsService;

/**
 * Class EnrollmentsService.
 */
class EnrollmentsGtService extends EnrollmentsService {

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $endpointManager;

  /**
   * Manager.
   *
   * @var \Drupal\oneapp_endpoints\Services\Manager
   */
  protected $oneappEndpointManager;

  /**
   * AwsApiManager.
   *
   * @var \Drupal\aws_service\Services\v2_0\AwsApiManager
   */
  protected $awsManager;

  /**
   * Params for request.
   *
   * @var array
   */
  protected $params = [];

  /**
   * Params for request.
   *
   * @var array
   */
  protected $tokenAuthorization = [];

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $config;

  /**
   * Default utilsPayment.
   *
   * @var mixed
   */
  protected $utilsPayment;

  /**
   * Type page.
   *
   * @var string|null
   */
  private $typePage;

  /**
   * Business unit.
   *
   * @var string|null
   */
  private $businessUnit;

  

  /**
   * AccountsService constructor.
   */
  public function __construct($one_app_endpoint_manager, $aws_manager, $tokenAuthorization, $utilsPayment, ConfigFactoryInterface $config_factory = NULL ) {
    $this->oneappEndpointManager = $one_app_endpoint_manager;
    $this->awsManager = $aws_manager;
    $this->tokenAuthorization = $tokenAuthorization;
    $this->utilsPayment = $utilsPayment;
    if ($config_factory != NULL) {
      $this->configFactoryMobile = $config_factory->get('oneapp.payment_gateway_recurring.mobile_invoices.config');
      $this->configFactoryHome = $config_factory->get('oneapp.payment_gateway_recurring.home_invoices.config');
    }
  }

  /**
   * Set config.
   */
  public function setConfig($config) {
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function setParams(array $params) {
    $this->params = $params;
  }

  /**
   * Get the payment enrollments by params.
   */
  public function getEnrollmentsByParams($businessUnit, $idType, $id, $accountNumber = NULL) {

    if (empty($accountNumber)) {
      $accountNumber = $this->utilsPayment->getAccountNumberForPaymentGatewayFromToken($businessUnit, $id);
      $enrrollmentAccounId = $accountNumber;
    }
    else {
      $enrrollmentAccounId = $accountNumber;
    }

    $config = $this->tokenAuthorization->getApplicationSettings("configuration_app");
    $params['billingSystem'] = $config["setting_app_payment"]["billingSystemName"];
    $params['apiHost'] = $config["setting_app_payment"]["api_path"];
    $awsService = isset($config["setting_app_payment"]["aws_service"]) ? $config["setting_app_payment"]["aws_service"] : 'payment';
    if (isset($id)) {
      $params['id'] = $enrrollmentAccounId;
    }
    else {
      $error = new ErrorBase();
      $error->getError()->set('message', 'The account Id does not exist in the current request.');
      throw new UnauthorizedHttpException($error);
    }
    $loadAccountsEndpoint = $this->oneappEndpointManager
      ->load('oneapp_convergent_payment_gateway_v2_0_enrollments_endpoint')
      ->setParams($params);
    $configEndpoint = [
      'url' => $loadAccountsEndpoint->getReplacedUrlEndpoint(),
      'method' => $loadAccountsEndpoint->getMethod(),
    ];
    $headersAws = $this->awsManager->headersAwsTreatment($configEndpoint, $awsService, '', []);
    $response = $loadAccountsEndpoint
      ->setHeaders($headersAws)
      ->setQuery([])
      ->setBody([])
      ->sendRequest(FALSE);

    return $response;
  }

  /**
   * Get the payment enrollments by params.
   */
  public function createEnrollment($businessUnit, $productType, $idType, $id, $request) {

    if (!$this->config['he_otp']['enable'] && $this->tokenAuthorization->isHe()) {
      throw new \Exception($this->config['he_otp']['message'], Response::HTTP_BAD_REQUEST);
    }

    $typeEnrollment = 'success';
    $this->params['uuid'] = $this->tokenAuthorization->getUserIdPayment();
    $this->params['tokenUuId'] = $this->tokenAuthorization->getTokenUuid();
    // Orden de prioridad para correo a utilizar.
    $this->params['email'] = (empty($this->tokenAuthorization->getEmail()) && isset($this->params['email'])) ? $this->params['email'] : $this->tokenAuthorization->getEmail();
    $moduleConfig = (object) $this->tokenAuthorization->getApplicationSettings("configuration_app");
    $awsService = isset($moduleConfig->setting_app_payment["aws_service"]) ? $this->params['aws_service'] = $moduleConfig->setting_app_payment["aws_service"] : 'payment';

    if (!isset($this->params['accountNumber'])) {
      $accountNumber = $this->utilsPayment->getAccountNumberForPaymentGatewayFromToken($businessUnit, $id);
      $this->params['accountNumber'] = $accountNumber;
    }

    try {
      $responseEnrollments = $this->getEnrollmentsByParams($businessUnit, $idType, $id, $accountNumber);
      if (isset($responseEnrollments->body)
        && $responseEnrollments->body->id) {
        $isOtherUserEnrollment = ($this->params['email'] !== $responseEnrollments->body->trace->email) ? TRUE : FALSE;
        $typeEnrollment = 'change';
      }
    }
    catch (\Exception $e) {
    }
    if (isset($isOtherUserEnrollment) && $isOtherUserEnrollment && !$moduleConfig->setting_app_payment['allowEditOtherUserEnrollment']) {
      throw new \Exception(t('Ya cuenta con una afiliación con otro usuario'), Response::HTTP_BAD_REQUEST);
    }
    $bodyEnrollment = $this->getBody($businessUnit, $productType, $idType, $id);

    $createEnrollment = $this->oneappEndpointManager
      ->load('oneapp_convergent_payment_gateway_v2_0_create_enrollments_endpoint')
      ->setParams($this->params);
    $configCreateEnrollment = [
      'url' => $createEnrollment->getReplacedUrlEndpoint(),
      'method' => $createEnrollment->getMethod(),
    ];
    $strtokenizedCardBody = json_encode($bodyEnrollment);
    $headersAws = $this->awsManager->headersAwsTreatment($configCreateEnrollment, $awsService, $strtokenizedCardBody, []);
    $mailService = \Drupal::service('oneapp_convergent_payment_gateway.v2_0.email_callbacks_service');
    $moduleConfigMails = $this->tokenAuthorization->getApplicationSettings("configuration_mail_recurring");

    $tokens = [
      'username' => $this->params['customerName'],
      'mail_to_send' => $this->params['email'],
      'accountId' => $id,
    ];
    if ($moduleConfigMails['cc_mail'] !== '') {
      $tokens['cc_mail'] = $moduleConfigMails['cc_mail'];
    }
    try {
      $createEnrollment = $createEnrollment
        ->setHeaders($headersAws)
        ->setBody($bodyEnrollment)
        ->setQuery([])
        ->sendRequest(FALSE);
    }
    catch (\Exception $e) {
      $configMail = [
        'subject' => $moduleConfigMails['fail']['subject'],
        'body' => $moduleConfigMails['fail']['body']['value'],
      ];
      $mailService->apiPaymentSendMail($tokens, $configMail);
      throw new \Exception('La transacción no esta válida', Response::HTTP_BAD_REQUEST);
    }
    $createEnrollment->body->typeEnrollment = $typeEnrollment;
    $typeMail = ($typeEnrollment == 'success') ? $typeEnrollment : "change_card_success";
    $configMail = [
      'subject' => $moduleConfigMails[$typeMail]['subject'],
      'body' => $moduleConfigMails[$typeMail]['body']['value'],
    ];
    try {
      $mailService->apiPaymentSendMail($tokens, $configMail);
    }
    catch (\Exception $e) {}
    return (array) $createEnrollment->body;
  }

  public function getBody($businessUnit, $productType, $idType, $id) {
    $configApp = (object) $this->tokenAuthorization->getApplicationSettings('configuration_app');
    $configConvergente = (object) $this->tokenAuthorization->getConvergentPaymentGatewaySettings('fields_default_values');
    $emailDefault = $configConvergente->email["send_default_value_email"] ? $configConvergente->email["email_default_value"] : '';
    $nameDefault = $configConvergente->name["send_default_value_name"] ? $configConvergente->name["name_default_value"] . ' ' . $configConvergente->name["last_name_default_value"] : '';
    $this->params['email'] = (!isset($this->params['email']) || empty($this->params['email'])) ? $emailDefault : $this->params['email'];
    $customerName = $this->params['customerName'] ?? $this->tokenAuthorization->getGivenNameUser() . ' ' . $this->tokenAuthorization->getFirstNameUser();
    $customerName = !empty(trim($customerName))? $customerName: $nameDefault;
    $customerName = $this->params['customerName'] = $this->utilsPayment->clearString($customerName);
    $configFields = $this->utilsPayment->validateConfigPaymentForms($this->params, 'invoices_autopayments');
    $this->params['apiHost'] = $configApp->setting_app_payment['api_path'];


    $deviceId = (isset($this->params['deviceId'])) ? $this->params['deviceId'] : $this->utilsPayment->getDeviceId($this->params['uuid'], $this->params['userAgent']);

    if (isset($this->params['numberCard'])) {
      if (!$configFields->newCardForm['address']['show']) {
        $this->params['street'] = $configConvergente->address['address_default_value'];
      }
      if ($configConvergente->address['send_default_value_address']) {
        $this->params['street'] = $configConvergente->address['address_default_value'];
      }
      $tokenizedCardBody = [
        'accountNumber' => $id,
        'accountType' => $configApp->setting_app_payment['typePay'],
        'deviceId' => $deviceId,
        'applicationName' => $configApp->setting_app_payment["applicationName"],
      ];
      if ($configFields->newCardForm['identificationType']['show']) {
        $fietokenizedCardBodyldBody['documentType'] = $this->params['documentType'];
      }
      if ($configFields->newCardForm['identificationNumber']['show']) {
        $tokenizedCardBody['documentNumber'] = $this->params['documentNumber'];
      }
      $number_card = $this->getNumberCard($this->params['numberCard']);
      $tokenizedCardBody['creditCardDetails'] = [
        'expirationYear' => trim($this->params['expirationYear']),
        'cvv' => trim($this->params['cvv']),
        'cardType' => $this->params['cardType'],
        'expirationMonth' => trim($this->params['expirationMonth']),
        'accountNumber' => trim($number_card),
      ];
      $userName = $this->utilsPayment->getNameAndLastname($customerName);

      $tokenizedCardBody['billToAddress'] = [
        'firstName' => trim($userName['firstName']),
        'lastName' => trim($userName['lastName']),
        'country' => trim($configConvergente->address["payment_country"]),
        'city' => trim($configConvergente->address["payment_city"]),
        'street' => $this->params['street'],
        'postalCode' => trim($configConvergente->address["payment_postal_code"]),
        'state' => trim($configConvergente->address["payment_state"]),
        'email' => !empty($this->tokenAuthorization->getEmail()) ? $this->tokenAuthorization->getEmail() : $emailDefault,
      ];
      $tokenizedCard = $this->oneappEndpointManager
        ->load('oneapp_convergent_payment_gateway_v2_0_addcards_endpoint')
        ->setParams($this->params);
      $configTokenizedCard = [
        'url' => $tokenizedCard->getReplacedUrlEndpoint(),
        'method' => $tokenizedCard->getMethod(),
      ];
      $strtokenizedCardBody = json_encode($tokenizedCardBody);
      $headersAws = $this->awsManager->headersAwsTreatment($configTokenizedCard, 'payment', $strtokenizedCardBody, []);
      $responseTokenizedCard = $tokenizedCard
        ->setHeaders($headersAws)
        ->setQuery([])
        ->setBody($tokenizedCardBody)
        ->sendRequest(FALSE);
      $this->params['tokenizedCardId'] = $responseTokenizedCard->body->id;
    }
    if (!$configFields->newCardForm['phone']['show']) {
      $this->params['phoneNumber'] = $this->utilsPayment->cutOraddPhone($id);
    }
    if (is_null($this->params['accountNumber'])) {
      throw new \Exception('Error accountNumber', Response::HTTP_BAD_REQUEST);
    }

    return [
      'billingSystemName' => $configApp->setting_app_payment['billingSystemName'],
      'paymentTokenId' => $this->params['tokenizedCardId'],
      'accountNumber' => $this->params['accountNumber'],
      'accountType' => $configApp->setting_app_payment['typePay'],
      'productType' => $configApp->setting_app_payment['ProductType'],
      'trace' => [
        'deviceId' => $deviceId,
        'applicationName' => $configApp->setting_app_payment['applicationName'],
        'paymentChannel' => $configApp->setting_app_payment['paymentChannel'],
        'phoneNumber' => $this->params['phoneNumber'],
        'customerIpAddress' => $this->params['customerIpAddress'],
        'customerName' => $customerName,
        'email' => $this->params['email'],
      ],
    ];
  }

  /**
   * Delete the payment enrollment by ID.
   */
  public function deleteEnrollmentById($businessUnit, $idType, $id, $enrollmentId) {
    $config = $this->tokenAuthorization->getApplicationSettings('configuration_app');
    $params['apiHost'] = $config["setting_app_payment"]["api_path"];
    $params['bsname'] = $config["setting_app_payment"]["billingSystemName"];
    $params['accountnumber'] = $this->utilsPayment->getAccountNumberForPaymentGatewayFromToken($businessUnit, $id);

    // Get enrollment email.
    $responseEnrollments = $this->getEnrollmentsByParams($businessUnit, $idType, $id, $params['accountnumber']);
    if (isset($responseEnrollments->body)
      && $responseEnrollments->body->id) {
      $this->params['email'] = $responseEnrollments->body->trace->email;
    }
    $this->params['email'] = (empty($this->tokenAuthorization->getEmail()) && isset($this->params['email'])) ?
      $this->params['email'] : $this->tokenAuthorization->getEmail();

    $deleteEnrollment = $this->oneappEndpointManager
      ->load('oneapp_convergent_payment_gateway_v2_0_delete_enrollment_by_params_endpoint')
      ->setParams($params);
    $configDeleteEnrollment = [
      'url' => $deleteEnrollment->getReplacedUrlEndpoint(),
      'method' => $deleteEnrollment->getMethod(),
    ];
    $awsService = isset($config["setting_app_payment"]["aws_service"]) ? $config["setting_app_payment"]["aws_service"] : 'payment';
    $headersAws = $this->awsManager->headersAwsTreatment($configDeleteEnrollment, $awsService, '', []);
    $deleteEnrollment = $deleteEnrollment
      ->setHeaders($headersAws)
      ->setQuery([])
      ->sendRequest(FALSE);

    $mailService = \Drupal::service('oneapp_convergent_payment_gateway.v2_0.email_callbacks_service');
    $moduleConfigMails = $this->tokenAuthorization->getApplicationSettings('configuration_mail_recurring');
    $tokens = [
      'username' => $this->tokenAuthorization->getGivenNameUser() . ' ' . $this->tokenAuthorization->getFirstNameUser(),
      'mail_to_send' => $this->params['email'],
      'accountId' => $id,
    ];
    if ($moduleConfigMails['cc_mail'] !== '') {
      $tokens['cc_mail'] = $moduleConfigMails['cc_mail'];
    }
    $configMail = [
      'subject' => $moduleConfigMails['delete_payment']['subject'],
      'body' => $moduleConfigMails['delete_payment']['body']['value'],
    ];
    $mailService->apiPaymentSendMail($tokens, $configMail);
    return (array) $deleteEnrollment;
  }

  /**
   * Get user IP address.
   */
  public function getUserIP() {
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = $_SERVER['REMOTE_ADDR'];
    if (filter_var($client, FILTER_VALIDATE_IP)) {
      $ip = $client;
    }
    elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
      $ip = $forward;
    }
    else {
      $ip = $remote;
    }

    return $ip;
  }

  public function isHe() {
    return $this->tokenAuthorization->isHe();
  }

}
