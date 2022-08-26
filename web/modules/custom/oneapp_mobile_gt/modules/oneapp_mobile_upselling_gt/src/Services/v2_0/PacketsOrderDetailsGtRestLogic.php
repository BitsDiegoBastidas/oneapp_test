<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\PacketsOrderDetailsRestLogic;

/**
 * Class PacketsOrderDetailsRestLogic.
 */
class PacketsOrderDetailsGtRestLogic extends PacketsOrderDetailsRestLogic {

  /**
   * Const.
   */
  const  LOAN_PACKET = 1;

  /**
   * Const.
   */
  const  EMERGENCY_LOAN = 2;

  /**
   * Const.
   */
  const  FREE_PACKET = 3;

  /**
   * Default configuration.
   *
   * @var \Drupal\oneapp\Services\UtilsService
   */
  protected $utils;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $loanOption;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $offerLoan;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $loanPackageId;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $ableCoreBalance;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $balance;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $myNumber;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $isAutopack;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $configAutopack;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $offer;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $primaryNumber;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $targetNumber;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $allowedGift;

  /**
   * ChangeMsisdn.
   *
   * @var string
   */
  protected $tigoInvalido;

  /**
   * AutopacksService.
   *
   * @var \Drupal\oneapp_mobile_payment_gateway_autopackets\Services\AutoPacketsServices
   */
  protected $autopacksService;

  /**
   * UtilsServiceGt
   *
   * @var \Drupal\oneapp_mobile_gt\Services\UtilsServiceGt
   */
  protected $mobileUtils;

  /**
   * TokenService
   *
   * @var \Drupal\oneapp_convergent_payment_gateway\Services\v2_0\TokenService
   */
  protected $tokenAuthorization;

  /**
   * OrderDetailsService
   *
   * @var \Drupal\oneapp_mobile_upselling\Services\OrderDetailsService
   */
  protected $packetsOrderDetailsServices;

  /**
   * @var \Drupal\oneapp_mobile_upselling_gt\Services\AvailableOffersGtServices
   */
  protected $availableOffersService;

  protected $tokenInfo;

  protected $atpaInfo;

  protected $isCorporate;

  /**
   * IsRoaming.
   *
   * @var string
   */
  protected $isRoaming = false;

  public function __construct($manager, $utils, $mobile_utils, $token_authorization, $packets_order_details_services) {
    $this->availableOffersService = \Drupal::service('oneapp_mobile_upselling.v2_0.available_offers_services');
    parent::__construct($manager, $utils, $mobile_utils, $token_authorization, $packets_order_details_services);
  }

  /**
   * Responds to GET requests.
   *
   * @param string $id
   *   Id.
   * @param string $package_id
   *   packageId.
   * @param bool $target_msisdn
   *   Id.
   *
   * @return array
   *   The HTTP response object.
   *
   * @throws \ReflectionException
   */
  public function get($id, $package_id, $target_msisdn = FALSE) {
    $this->tokenInfo = $this->mobileUtils->getInfoTokenByMsisdn($id);
    $this->primaryNumber['accountId'] = $id;
    $this->targetNumber['accountId'] = $target_msisdn;
    $this->myNumber = FALSE;
    $this->getTypeLine();
    $this->isRoaming = boolval(stristr($package_id, 'ROAM'));
    $this->isCorporate = $this->isCorporate($this->primaryNumber['accountId']);
    $package_id = $this->isRoaming ?  str_replace('ROAM', '', $package_id) : $package_id;
    $this->isAutopack = $this->getValidAutoPacket($package_id);
    if (($this->myNumber && !isset($this->tigoInvalido['value'])) ||
      !isset($this->tigoInvalido['value']) && $this->getPossibilityToGift()['value']) {
      $query_params = (!$this->allowedGift['value']) ? TRUE : FALSE;
      $this->getOfferById($id, $package_id, $query_params);
    }
    $bug = $this->bugMapping();
    if ($bug['value']) {
      return [
        'data' => $this->getData($this->targetNumber['accountId'], $this->configBlock),
        'config' => $this->getConfigurationsBugs($bug),
      ];
    }
    else {
      return [
        'data' => $this->getData($this->targetNumber['accountId'], $this->configBlock),
        'config' => $this->getConfigurationsResponse($package_id),
      ];
    }
  }

   /**
   * validate if there is a credit card method.
   */
  public function validitycreditPackagePrice($credit_package_price) {
    $amount_config = \Drupal::config('oneapp_mobile.config')->get('cardPayment_from');
    $min = intval($amount_config['min']);
    $show_max = (bool) $amount_config['show'];
    if ($show_max === TRUE) {
      $max = intval($amount_config['max']);
      $price = floatval($credit_package_price);
      $credit_pac_price = ($price >= $min && $price <= $max) ? $this->configBlock['fields']['creditPackagePrice'] : FALSE;
    }
    else {
      $price = floatval($credit_package_price);
      $credit_pac_price = ($price >= $min) ? $this->configBlock['fields']['creditPackagePrice'] : FALSE;
    }
    return $credit_pac_price;
  }

  /**
   * Get Roaming Offer
   *
   * @param string|int $msisdn
   * @param string|int $package_id
   * @param string|int $billing_type
   * @return boolean|object
   */
  protected function getRoamingOfferGt($msisdn, $package_id, $billing_type, $loan_id = NULL) {
    $this->offer = false;
    $msisdn = $this->msisdnValid($msisdn);
    $offers = $this->availableOffersService->getAvailableRoamingOffers($msisdn, $billing_type) ?? [];
    foreach ($offers as $offer) {
      if ($offer->packageId == $package_id) {
        $this->offer = $offer;
        $this->offer->founded = TRUE;
      }
      if (isset($loan_id) && ($offer->packageId == $loan_id)) {
        $this->offerLoan = $offer;
      }
    }
    if (!is_object($this->offer)) {
      $this->offer = (object) [
        'accountId' => $this->primaryNumber['accountId'],
        'founded' => FALSE,
      ];
    }
    return $this->offer;
  }

  /**
   * Get offer.
   */
  protected function getOfferGt($msisdn, $package_id, $query_params, $loan_id = NULL) {
    $this->offer = FALSE;
    $msisdn = $this->msisdnValid($msisdn);
    try {
      if ($query_params) {
        $offers = $this->manager
          ->load('oneapp_mobile_upselling_v1_0_available_offers_postpaid_endpoint')
          ->setHeaders([])
          ->setQuery([
            'category' => 'TURBOBUTTONS',
          ])
          ->setParams(['msisdn' => $msisdn])
          ->sendRequest();
      }
      else {
        $offers = $this->manager
          ->load('oneapp_mobile_upselling_v2_0_available_offers_endpoint')
          ->setHeaders([])
          ->setQuery([])
          ->setParams(['msisdn' => $msisdn])
          ->sendRequest();
      }
      if ($offers->products != []) {
        foreach ($offers->products as $offer) {
          if ($offer->packageId == $package_id) {
            $this->offer = $offer;
            $this->offer->founded = TRUE;
          }
          if (isset($loan_id) && ($offer->packageId == $loan_id)) {
            $this->offerLoan = $offer;
          }
        }
      }
      if (!is_object($this->offer)) {
        $this->offer->accountId = $this->primaryNumber['accountId'];
        $this->offer->founded = FALSE;
      }
      return $this->offer;
    }
    catch (HttpException $exception) {
      return FALSE;
    }

  }

  /**
   * Implements getCurrentPlan.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return mixed
   *   Msisdn value.
   *
   * @throws \ReflectionException
   */
  public function getBalance($msisdn) {
    $config = $this->configBlock;
    $message_error = $config['config']['messages']['number_error']['label'];
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      $message = $message_error;
      $reflected_object = new \ReflectionClass(get_class($exception));
      $property = $reflected_object->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      throw $exception;
    }
  }

  /**
   * get loan packets.
   */
  protected function findLoanPackets($packet_id) {
    $ids = \Drupal::entityQuery('paquetigos_entity')->execute();
    $items = \Drupal::entityTypeManager()->getStorage('paquetigos_entity')->loadMultiple($ids);
    $packet_loan = NULL;
    foreach ($items as $item) {
      if ($item->getIdOffer() == $packet_id) {
        $packet_loan = $item;
      }
    }
    return $packet_loan;
  }

  /**
   * Get offer or loan packets.
   */
  protected function getOfferById($msisdn, $package_id, $query_params = []) {
    $package_id = $this->parsePackageId($package_id);
    $packet_loan = $this->findLoanPackets($package_id);
    $this->ableCoreBalance = isset($packet_loan) ? $packet_loan->isAbleCoreBalance() : TRUE;
    $this->offer = NULL;
    $packet_loan_id = isset($packet_loan) ? $packet_loan->getIdLoan() : NULL;
    if (isset($packet_loan_id)) {
      $this->offerLoan = NULL;
      if ($this->isRoaming) {
        $this->offer = $this->getRoamingOfferGt($msisdn, $package_id, $this->billingType, $packet_loan_id);
      }
      else {
        $this->offer = $this->getOfferGt($msisdn, $package_id, $query_params, $packet_loan_id);
      }
      if (isset($this->offerLoan)) {
        $this->loanPackageId = $packet_loan_id;
        $this->loanOption = self::LOAN_PACKET;
      }
      else {
        $this->loanPackageId = NULL;
        $this->loanOption = NULL;
        $this->findAvailableLoans($msisdn, $package_id);
      }
    }
    else {
      if ($this->isRoaming) {
        $this->offer = $this->getRoamingOfferGt($msisdn, $package_id, $this->billingType);
      }
      else {
        $this->offer = $this->getOfferGt($msisdn, $package_id, $query_params);
      }
      if (isset($this->offer->price) && $this->offer->price == 0) {
        $this->loanOption = self::FREE_PACKET;
      }
      else {
       $this->findAvailableLoans($msisdn, $package_id);
      }
    }
    if ($this->isAutopack && is_object($this->offer)) {
      if (isset($this->offer->creditPackagePrice)) {
        $this->isAutopack = $this->autopacksService->isAllowedValityAutopacks($this->offer->validityNumber, $this->offer->validityType);
      }
      else {
        $this->isAutopack = FALSE;
      }

    }
    return $this->offer;
  }

  /**
   * Get available loans.
   */
  public function findAvailableLoans($msisdn, $package_id) {
    $service = \Drupal::service('oneapp_mobile_upselling.v2_0.available_offers_rest_logic');
    $is_available_loans = $service->findAvailableLoans($package_id, $msisdn);
    if ($is_available_loans) {
      $this->loanOption = self::EMERGENCY_LOAN;
    }
  }

  /**
   * Return a valid msisdn.
   */
  public function msisdnValid($msisdn) {
    $mobile_settings = \Drupal::config('oneapp_mobile.config')->get('general');
    $global_settings = \Drupal::config('oneapp_endpoints.settings')->getRawData();
    $msisdn_lenght = $mobile_settings['msisdn_lenght'];
    $prefix_country = $global_settings['prefix_country'];
    if (strlen($msisdn) <= $msisdn_lenght && !preg_match("/^{$prefix_country}[0-9]{$msisdn_lenght}$/", $msisdn)) {
      $msisdn = $prefix_country . $msisdn;
    }
    return $msisdn;
  }

  /**
   * Validate if packageId is autopacket.
   */
  public function getValidAutoPacket($packet_id) {
    $this->isAutopack = FALSE;
    $this->configAutopack = FALSE;
    if (\Drupal::hasService('oneapp_mobile_payment_gateway_autopackets.v2_0.autopackets_services')) {
      $this->autopacksService = \Drupal::service('oneapp_mobile_payment_gateway_autopackets.v2_0.autopackets_services');
      $this->isAutopack = $this->autopacksService->isValidAutoPacket($packet_id);
      $this->configAutopack = \Drupal::config("oneapp.payment_gateway.mobile_autopackets.config")->get("orderDetails");
    }
    return $this->isAutopack;
  }

  /**
   * Get payment methods for autopackets.
   */
  public function getPaymentMethodsAutoPacks($packet_id) {
    $data_config = [];
    $is_allowed_core_balance = $this->verifyCoreBalanceAutopackets();
    foreach ($this->configAutopack["paymentMethods"]['fields'] as $key => $value) {
      $payment_method_show = (bool) $value['show'];
      $show = FALSE;
      switch ($this->primaryNumber['info']) {
        case 'PREPAGO':
        case 'KIT':
          if ($payment_method_show && (bool) $value["show_prepaid"]) {
            if ($value['machine_name_target'] == 'coreBalance') {
              $show = $is_allowed_core_balance['value'];
            }
            elseif ($value['machine_name_target'] == 'creditCard') {
              $show = $this->verifyAutoPacksCreditCards($packet_id);
            }
            else {
              $show = TRUE;
            }
          }
          break;

        case 'STAFF DE COMCEL':
        case 'CREDITO':
          if ($payment_method_show && (bool) $value["show_postpaid"]) {
            if ($value['machine_name_target'] == 'coreBalance') {
              $show = FALSE;
            }
            elseif ($value['machine_name_target'] == 'creditCard') {
              $show = $this->verifyAutoPacksCreditCards($packet_id);
            }
            else {
              $show = TRUE;
            }
          }
          break;

        case 'FACTURA FIJA':
          if ($payment_method_show && (bool) $value["show_hybrid"]) {
            if ($value['machine_name_target'] == 'coreBalance') {
              $show = $is_allowed_core_balance['value'];
            }
            elseif ($value['machine_name_target'] == 'creditCard') {
              $show = $this->verifyAutoPacksCreditCards($packet_id);
            }
            else {
              $show = TRUE;
            }
          }
          break;
      }

      $data_config[$value['machine_name_target']] = [
        "paymentMethodName" => $value['machine_name_target'],
        "label" => $value['label'],
        "url" => "/",
        "type" => "button",
        "show"  => $show,
        'isRecurrent' => ($show) ? TRUE : FALSE,
      ];
      if ($show) {
        if ($value['machine_name_target'] == 'coreBalance') {
          $row = [];
          $message_success = $this->configBlock['config']['messages']['verifyCoreBalance'];
          $message_failure = $this->configBlock['config']['messages']['package_error'];
          $tu_saldo = $this->configBlock['config']['actions']['coreBalanceSumary'];
          $utils_oneapp = \Drupal::service('oneapp.utils');
          $currency_id = $utils_oneapp->getCurrencySign(TRUE);
          $currency_code = $utils_oneapp->getCurrencyCode(TRUE);
          $format_balance = $currency_id . $this->balance;
          $row[$value['machine_name_target']]['description'] = [
            'label' => $tu_saldo['title'],
            'formattedValue' => $format_balance,
            'show' => (bool) $tu_saldo['show'],
          ];
          $row[$value['machine_name_target']]['description']['value'] = [
            'amount' => $this->balance,
            'currencyId' => $currency_code,
          ];
          $row[$value['machine_name_target']]['description']['validForPurchase'] = ($this->balance['value'] === '') ?
            TRUE : $this->balance['value'] >= $is_allowed_core_balance['currentPrice'];

          if ($row[$value['machine_name_target']]['description']['validForPurchase']) {
            $formatted_price = $this->utils->formatCurrency($is_allowed_core_balance['currentPrice'], FALSE);
            $message = str_replace('@amount', $formatted_price, $message_success['label']);
            $show = (bool) $message_success['show'];
            $purchase = TRUE;
          }
          else {
            $message = $message_failure['label'];
            $show = (bool) $message_failure['show'];
            $purchase = FALSE;
          }
          $row[$value['machine_name_target']]['confirmation'] = $this->confirmationCoreBalanceResponse($message, $purchase, $show);
          $data_config[$value['machine_name_target']]['description'] =
            $row[$value['machine_name_target']]['description'];
          $data_config[$value['machine_name_target']]['confirmation'] =
            $row[$value['machine_name_target']]['confirmation'];
        }
        if ($value['machine_name_target'] == 'creditCard') {
          if ($this->myNumber) {
            $data_config[$value['machine_name_target']]['description'] = [
              'label' => $this->configAutopack["paymentMethods"]['description']['label'],
              'show' => (bool) $this->configAutopack["paymentMethods"]['description']['show'],
            ];
          }
          else {
            $credit_pac_prom = $this->configBlock['fields']['creditPackagePromotion'];
            $description = (isset($this->offer->creditPackagePromotion)) ?
              $this->offer->creditPackagePromotion : $credit_pac_prom['label'];
            $data_config[$value['machine_name_target']]['description'] = [
              'label' => $description,
              'show' => (bool) $credit_pac_prom['show'],
            ];
          }
        }
      }
    }

    if (empty($data_config)) {
      $data_config = [];
    }
    return $data_config;
  }

  /**
   * Returns if exist corebalance payment méthod.
   */
  public function verifyCoreBalanceAutopackets() {
    if (isset($this->offer)) {
      if ($this->myNumber === TRUE && $this->ableCoreBalance) {
        return [
          'value' => TRUE,
          'currentPrice' => $this->offer->price,
        ];
      } else {
        return [
          'value' => FALSE,
        ];
      }
    }
  }

  /**
   * Returns if exist credit card payment méthod for autopackets.
   */
  public function verifyAutoPacksCreditCards($package_id) {
    if (isset($this->offer->creditPackagePrice)) {
      return \Drupal::service('oneapp_mobile_payment_gateway_autopackets.v2_0.autopackets_services')
        ->isValidAutoPacket($package_id, $this->offer->creditPackagePrice);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Order the payment methods.
   */
  public function orderPaymentMethods($rows) {
    $order = ['coreBalance', 'invoiceCharge', 'creditCard'];
    $order_methods[0] = array_merge(array_flip($order), $rows[0]);
    return $order_methods;
  }

  /**
   * Returns configurations for actions.
   */
  public function getActions($bug = FALSE) {
    $config = $this->configBlock['config']['actions'];
    $actions['changeMsisdn'] = [
      'label' => $config['changeMsisdn']['label'],
      'url' => $config['changeMsisdn']['url'],
      'type' => $config['changeMsisdn']['type'],
      'show' => ($bug) ? FALSE : $this->isAllowedChangeMsisdn(),
    ];
    $actions['fullDescription'] = [
      'label' => $config['fulldescription']['label'],
      'url' => $config['fulldescription']['url'],
      'type' => $config['fulldescription']['type'],
      'show' => ($bug) ? FALSE : (bool) $config['fulldescription']['show'],
    ];
    if ($this->isAutopack && !$bug && $this->isTypeClientAllowed($this->primaryNumber['info'])) {
      $actions += $this->getPurchaseFrecuency();
    }
    return $actions;
  }

  /**
   * Get if a PlanType is allowed for autopackets.
   */
  protected function isTypeClientAllowed($account_info) {
    $array = [];
    $config_autopack = \Drupal::config("oneapp_mobile.config")->get("autopackets");
    switch ($account_info) {
      case 'PREPAGO':
      case 'KIT':
        if ($config_autopack['autopackets_plan_types']['prepaid']) {
          $array = ['PREPAGO', 'KIT'];
        }
        break;

      case 'FACTURA FIJA':
        if ($config_autopack['autopackets_plan_types']['hybrid']) {
          $array = ['FACTURA FIJA'];
        }
        break;

      case 'CREDITO':
      case 'STAFF DE COMCEL':
        if ($config_autopack['autopackets_plan_types']['postpaid']) {
          $array = ['CREDITO', 'STAFF DE COMCEL'];
        }
        break;

      default:
        $array = [];
        break;
    }
    return in_array($account_info, $array);
  }

  /**
   * Return if is posible changeMsisdn.
   */
  public function isAllowedChangeMsisdn() {
    $show = (bool) $this->configBlock['config']['actions']['changeMsisdn']['show'];
    return ($show && isset($this->offer->creditPackagePrice)) ? TRUE : FALSE;
  }

  /**
   * Get frecuency options.
   */
  public function getPurchaseFrecuency() {
    $data_config = [];
    if ($this->isAutopack) {
      $frecuency_msg = ($this->myNumber) ? $this->configAutopack["frecuency"]['recurrentDescription'] :
        $this->configAutopack["frecuency"]['onceDescription'];
      $data_config['purchaseFrecuency'] = [
        "show"  => (bool) $this->configAutopack["frecuency"]['show'],
        "label" => $this->configAutopack["frecuency"]['label'],
        "description"  => $frecuency_msg,
      ];
      $default_onces = ($this->configAutopack["frecuency"]['actions']['type'] == 'once') ? TRUE :
        FALSE;
      $default_recurrent = ($this->configAutopack["frecuency"]['actions']['type'] == 'recurrent') ? TRUE :
        FALSE;
      $data_config['purchaseFrecuency']['options']['once'] = [
        "value"  => ($this->myNumber) ? $default_onces : TRUE,
        "label" => $this->configAutopack["frecuency"]['once']['label'],
        "type"  => 'radio',
        'show'  => TRUE,
      ];
      $data_config['purchaseFrecuency']['options']['recurrent'] = [
        "value"  => ($this->myNumber) ? $default_recurrent : FALSE,
        "label" => $this->configAutopack["frecuency"]['recurrent']['label'],
        "type"  => 'radio',
        'show'  => ($this->myNumber) ? TRUE : FALSE,
      ];
    }

    return $data_config;
  }

  /**
   * Returns planType for msisdn.
   */
  public function getTypeLine() {
    try {
      $plan = $this->getBalance($this->primaryNumber['accountId']);
      $this->balance = $plan->balance;
      $this->primaryNumber['info'] = $plan->typeClient;
      $this->billingType = $this->packetsOrderDetailsServices->getBillingTypeByClientType($plan->typeClient);
      $this->allowedGift['value'] = ($this->primaryNumber['info'] == 'PREPAGO' || $this->primaryNumber['info'] == 'KIT') ? TRUE : FALSE;
      if (!$this->primaryNumber['info']) {
        $this->tigoInvalido['accountId'] = $this->primaryNumber['accountId'];
        $this->tigoInvalido['value'] = TRUE;
      }
      elseif (str_replace(' ', '', $this->primaryNumber['accountId']) === str_replace(' ', '', $this->targetNumber['accountId'])) {
        $this->myNumber = TRUE;
        $this->targetNumber['info'] = $this->primaryNumber['info'];
      }
      else {
        $this->targetNumber['info'] = $this->getBalance($this->targetNumber['accountId'])->typeClient;
        if (!$this->targetNumber['info']) {
          $this->tigoInvalido['accountId'] = $this->targetNumber['accountId'];
          $this->tigoInvalido['value'] = TRUE;
        }
      }
    }
    catch (HttpException $exception) {
      if (isset($this->primaryNumber['info'])) {
        $this->tigoInvalido['accountId'] = $this->targetNumber['accountId'];
      }
      else {
        $this->tigoInvalido['accountId'] = $this->primaryNumber['accountId'];
      }
      $this->tigoInvalido['value'] = TRUE;
    }
  }

  /**
   * Returns if its posibly gift packets.
   */
  public function getPossibilityToGift() {
    if (!$this->myNumber) {
      switch ($this->primaryNumber['info']) {
        case 'PREPAGO':
        case 'KIT':
          $array = ['PREPAGO', 'KIT'];
          break;

        default:
          $array = [];
          break;
      }
      $this->allowedGift['value'] = in_array($this->targetNumber['info'], $array);
      if (!$this->allowedGift['value']) {
        $this->allowedGift['accountId'] = $this->targetNumber['accountId'];
      }
      return $this->allowedGift;
    }
  }

  /**
   * Mapping errors.
   */
  protected function bugMapping() {
    $rows = [];
    if (isset($this->tigoInvalido['value']) && $this->tigoInvalido['value']) {
      $rows = [
        'label' => $this->configBlock['config']['messages']['number_error']['label'],
        'show' => (bool) $this->configBlock['config']['messages']['number_error']['show'],
      ];
    }
    elseif (!$this->myNumber && isset($this->allowedGift['value']) && !$this->allowedGift['value']) {
      $rows = [
        'label' => $this->configBlock['config']['messages']['gift_invalid']['label'],
        'show' => (bool) $this->configBlock['config']['messages']['gift_invalid']['show'],
      ];
    }
    elseif (!$this->offer->founded) {
      $rows = [
        'label' => $this->configBlock['config']['messages']['offer_error']['label'],
        'show' => (bool) $this->configBlock['config']['messages']['offer_error']['show'],
      ];
    }
    if (empty($rows)) {
      return [
        'value' => false,
      ];
    }
    return [
      'value' => true,
      'bug' => $rows,
    ];
  }

  /**
   * Returns configurations for actions.
   */
  public function getConfigurationsBugs($bug) {
    $actions = $this->getActions($bug['value']);
    $actions['paymentMethods'] = $bug['bug'];
    $dataconfig['titleDetails'] = [
      'label' => $this->configBlock['fields']['title']['label'],
      'show' => (bool) $this->configBlock['fields']['title']['show'],
    ];
    $dataconfig['paymentMethods'] = [
      'label' => $this->configBlock['config']['actions']['paymentMethodsTitle']['value'],
      'show' => (bool) $this->configBlock['config']['actions']['paymentMethodsTitle']['show'],
    ];
    return [
      'actions' => $actions,
      'dataconfig' => $dataconfig,
    ];
  }

  /**
   * Returns configurations for json response.
   */
  public function getConfigurationsResponse($packet_id) {
    $config = $this->configBlock['config']['actions'];
    $utils_oneapp = \Drupal::service('oneapp.utils');
    $currency_id = $utils_oneapp->getCurrencySign(TRUE);
    $currency_code = $utils_oneapp->getCurrencyCode(TRUE);
    $format_currency = (isset($this->offer->price)) ? $utils_oneapp->formatCurrency($this->offer->price, TRUE, FALSE) : FALSE;
    $actions = $this->getActions();
    $index = 0;
    if ($this->myNumber && $this->isAutopack && $this->isTypeClientAllowed($this->primaryNumber['info'])) {
      $payment_methods_autopacks = $this->getPaymentMethodsAutoPacks($packet_id);
      if ($payment_methods_autopacks != []) {
        $row = $payment_methods_autopacks;
      }
    }
    foreach ($config as $id => $field) {
      if (($id == 'coreBalance' && !isset($row['coreBalance'])) ||
        ($id == 'coreBalance' && isset($row['coreBalance']) && $row['coreBalance']['show'] === FALSE)) {
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => FALSE,
          'isRecurrent' => FALSE,
        ];
        if ($this->myNumber === TRUE && $this->ableCoreBalance && $this->loanOption != self::FREE_PACKET) {
          if ($this->allowedGift['value'] === TRUE ||
            $this->primaryNumber['info'] === 'FACTURA FIJA' && $this->loanOption != self::FREE_PACKET && !$this->isRoaming) {
            $row[$id]['show'] = (bool) $field['show'];
            $core_balance = $this->configBlock['config']['actions']['coreBalanceSumary'];
            $format_balance = $currency_id . $this->balance;
            $row[$id]['description'] = [
              'label' => $core_balance['title'],
              'formattedValue' => $format_balance,
              'show' => (bool) $core_balance['show'],
            ];
            $row[$id]['description']['value'] = [
              'amount' => $this->balance,
              'currencyId' => $currency_code,
            ];
            $message_success = $this->configBlock['config']['messages']['verifyCoreBalance'];
            $message_failure = $this->configBlock['config']['messages']['package_error'];
            if (floatval($this->balance) >= floatval($this->offer->price)) {
              $message = str_replace('@amount', $format_currency, $message_success['label']);
              $show = (bool) $message_success['show'];
              $purchase = TRUE;
            }
            else {
              $message = $message_failure['label'];
              $show = (bool) $message_failure['show'];
              $purchase = FALSE;
            }
            $row[$id]['description']['validForPurchase'] = $purchase;
            $verify = $this->configBlock['config']['response']['coreBalanceVerify'];
            $row[$id]['confirmation']['confirmationTitle'] = [
              'label' => $verify['title']['label'],
              'show' => (bool) $verify['title']['show'],
            ];
            $row[$id]['confirmation']['message'] = [
              'label' => $message,
              'show' => $show,
            ];
            $row[$id]['confirmation']['orderDetailsTitle'] = [
              'label' => $verify['coreBalanceVerify']['label'],
              'show' => (bool) $verify['coreBalanceVerify']['show'],
            ];
            $row[$id]['confirmation']['typeProduct'] = [
              'label' => $verify['productType']['label'],
              'show' => (bool) $verify['productType']['show'],
            ];
            $row[$id]['confirmation']['paymentMethodsTitle'] = [
              'label' => $verify['paymentMethodTitle']['label'],
              'show' => (bool) $verify['paymentMethodTitle']['show'],
            ];
            $row[$id]['confirmation']['paymentMethod'] = [
              'label' => $verify['paymentMethod']['label'],
              'formattedValue' => $verify['paymentMethod']['value'],
              'show' => (bool) $verify['paymentMethod']['show'],
            ];
            $row[$id]['confirmation']['coreBalancePayment'] = [
              'label' => $verify['coreBalance']['label'],
              'show' => (bool) $verify['coreBalance']['show'],
            ];
            $row[$id]['confirmation']['actions']['change'] = [
              'label' => $verify['changeButtons']['label'],
              'url' => $verify['changeButtons']['url'],
              'type' => $verify['changeButtons']['type'],
              'show' => (bool) $verify['changeButtons']['show'],
            ];
            $row[$id]['confirmation']['actions']['cancel'] = [
              'label' => $verify['cancelButtons']['label'],
              'url' => $verify['cancelButtons']['url'],
              'type' => $verify['cancelButtons']['type'],
              'show' => (bool) $verify['cancelButtons']['show'],
            ];
            $row[$id]['confirmation']['actions']['purchase'] = [
              'label' => $verify['purchaseButtons']['label'],
              'url' => $verify['purchaseButtons']['url'],
              'type' => $verify['purchaseButtons']['type'],
              'show' => ($purchase) ? (bool) $verify['purchaseButtons']['show'] : FALSE,
            ];
            $row[$id]['confirmation']['actions']['termsOfServices'] = [
              'label' => $verify['termsAndConditions']['label'],
              'url' => $verify['termsAndConditions']['url'],
              'type' => $verify['termsAndConditions']['type'],
              'show' => (bool) $verify['termsAndConditions']['show'],
            ];
          }
          else if ($this->primaryNumber['info'] === 'FACTURA FIJA' && $this->loanOption != self::FREE_PACKET && $this->isRoaming && $this->isCorporate($this->primaryNumber['accountId'])) {
            $row[$id]['show'] = (bool) $field['show'];
            $core_balance = $this->configBlock['config']['actions']['coreBalanceSumary'];
            $format_balance = $currency_id . $this->balance;
            $row[$id]['description'] = [
              'label' => $core_balance['title'],
              'formattedValue' => $format_balance,
              'show' => (bool) $core_balance['show'],
            ];
            $row[$id]['description']['value'] = [
              'amount' => $this->balance,
              'currencyId' => $currency_code,
            ];
            $message_success = $this->configBlock['config']['messages']['verifyCoreBalance'];
            $message_failure = $this->configBlock['config']['messages']['package_error'];
            if (floatval($this->balance) >= floatval($this->offer->price)) {
              $message = str_replace('@amount', $format_currency, $message_success['label']);
              $show = (bool) $message_success['show'];
              $purchase = TRUE;
            }
            else {
              $message = $message_failure['label'];
              $show = (bool) $message_failure['show'];
              $purchase = FALSE;
            }
            $row[$id]['description']['validForPurchase'] = $purchase;
            $verify = $this->configBlock['config']['response']['coreBalanceVerify'];
            $row[$id]['confirmation']['confirmationTitle'] = [
              'label' => $verify['title']['label'],
              'show' => (bool) $verify['title']['show'],
            ];
            $row[$id]['confirmation']['message'] = [
              'label' => $message,
              'show' => $show,
            ];
            $row[$id]['confirmation']['orderDetailsTitle'] = [
              'label' => $verify['coreBalanceVerify']['label'],
              'show' => (bool) $verify['coreBalanceVerify']['show'],
            ];
            $row[$id]['confirmation']['typeProduct'] = [
              'label' => $verify['productType']['label'],
              'show' => (bool) $verify['productType']['show'],
            ];
            $row[$id]['confirmation']['paymentMethodsTitle'] = [
              'label' => $verify['paymentMethodTitle']['label'],
              'show' => (bool) $verify['paymentMethodTitle']['show'],
            ];
            $row[$id]['confirmation']['paymentMethod'] = [
              'label' => $verify['paymentMethod']['label'],
              'formattedValue' => $verify['paymentMethod']['value'],
              'show' => (bool) $verify['paymentMethod']['show'],
            ];
            $row[$id]['confirmation']['coreBalancePayment'] = [
              'label' => $verify['coreBalance']['label'],
              'show' => (bool) $verify['coreBalance']['show'],
            ];
            $row[$id]['confirmation']['actions']['change'] = [
              'label' => $verify['changeButtons']['label'],
              'url' => $verify['changeButtons']['url'],
              'type' => $verify['changeButtons']['type'],
              'show' => (bool) $verify['changeButtons']['show'],
            ];
            $row[$id]['confirmation']['actions']['cancel'] = [
              'label' => $verify['cancelButtons']['label'],
              'url' => $verify['cancelButtons']['url'],
              'type' => $verify['cancelButtons']['type'],
              'show' => (bool) $verify['cancelButtons']['show'],
            ];
            $row[$id]['confirmation']['actions']['purchase'] = [
              'label' => $verify['purchaseButtons']['label'],
              'url' => $verify['purchaseButtons']['url'],
              'type' => $verify['purchaseButtons']['type'],
              'show' => ($purchase) ? (bool) $verify['purchaseButtons']['show'] : FALSE,
            ];
            $row[$id]['confirmation']['actions']['termsOfServices'] = [
              'label' => $verify['termsAndConditions']['label'],
              'url' => $verify['termsAndConditions']['url'],
              'type' => $verify['termsAndConditions']['type'],
              'show' => (bool) $verify['termsAndConditions']['show'],
            ];
          }

        }
      }
      elseif (($id == 'invoiceCharge' && !isset($row['invoiceCharge'])) ||
        ($id == 'invoiceCharge' && isset($row['invoiceCharge']) && $row['invoiceCharge']['show'] === FALSE)) {
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => FALSE,
          'isRecurrent' => FALSE,
        ];
        if ($this->primaryNumber['info'] === 'STAFF DE COMCEL' || $this->primaryNumber['info'] === 'CREDITO'
          || ($this->primaryNumber['info'] === 'FACTURA FIJA' && $this->isRoaming && !$this->isCorporate )) {
          $row[$id]['show'] = (bool) $field['show'];
          $message_success = $this->configBlock['config']['messages']['verifyinvoiceCharge'];
          $message = str_replace('@amount', $format_currency, $message_success['label']);
          $show = (bool) $message_success['show'];

          $verify = $this->configBlock['config']['response']['invoiceChargeVerify'];
          $row[$id]['confirmation']['confirmationTitle'] = [
            'label' => $verify['title']['label'],
            'show' => (bool) $verify['title']['show'],
          ];
          $row[$id]['confirmation']['message'] = [
            'label' => $message,
            'show' => $show,
          ];
          $row[$id]['confirmation']['orderDetailsTitle'] = [
            'label' => $verify['invoiceChargeVerify']['label'],
            'show' => (bool) $verify['invoiceChargeVerify']['show'],
          ];
          $row[$id]['confirmation']['productType'] = [
            'label' => $verify['productType']['label'],
            'show' => (bool) $verify['productType']['show'],
          ];
          $row[$id]['confirmation']['paymentMethodTitle'] = [
            'label' => $verify['paymentMethodTitle']['label'],
            'show' => (bool) $verify['paymentMethodTitle']['show'],
          ];
          $row[$id]['confirmation']['paymentMethod'] = [
            'label' => $verify['paymentMethod']['label'],
            'formattedValue' => $verify['paymentMethod']['value'],
            'show' => (bool) $verify['paymentMethod']['show'],
          ];
          $row[$id]['confirmation']['actions']['cancel'] = [
            'label' => $verify['cancelButtons']['label'],
            'url' => $verify['cancelButtons']['url'],
            'type' => $verify['cancelButtons']['type'],
            'show' => (bool) $verify['cancelButtons']['show'],
          ];
          $row[$id]['confirmation']['actions']['purchase'] = [
            'label' => $verify['purchaseButtons']['label'],
            'url' => $verify['purchaseButtons']['url'],
            'type' => $verify['purchaseButtons']['type'],
            'show' => (bool) $verify['purchaseButtons']['show'],
          ];
          $row[$id]['confirmation']['actions']['termsOfServices'] = [
            'label' => $verify['termsAndConditions']['label'],
            'url' => $verify['termsAndConditions']['url'],
            'type' => $verify['termsAndConditions']['type'],
            'show' => (bool) $verify['termsAndConditions']['show'],
          ];
        }
      }
      elseif (empty($this->isRoaming) && (($id == 'creditCard' && !isset($row['creditCard'])) ||
          ($id == 'creditCard' && isset($row['creditCard']) && $row['creditCard']['show'] === FALSE))) {
        $show_credit = FALSE;
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => $show_credit,
          'isRecurrent' => FALSE,
        ];
        if ($this->allowedGift['value']) {
          if (isset($this->offer->creditPackagePrice)) {
            $show_credit = $this->validitycreditPackagePrice($this->offer->creditPackagePrice);
            $row[$id]['show'] = (bool) $show_credit['show'];
            if ($row[$id]['show'] === TRUE) {
              $credit_pac_prom = $this->configBlock['fields']['creditPackagePromotion'];
              $description = (isset($this->offer->creditPackagePromotion)) ?
                $this->offer->creditPackagePromotion : $credit_pac_prom['label'];
              $row[$id]['description'] = [
                'label' => $description,
                'show' => (bool) $credit_pac_prom['show'],
              ];
            }
          }
        }
      }
      elseif (empty($this->isRoaming) &&
        ($id == 'Loan_Packets' && $this->loanOption == self::LOAN_PACKET && $this->myNumber == TRUE)) {
        $summary_config = $this->configBlock['config']['actions']['loanPacketSumary'];
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => (bool) $field['show'],
          'isRecurrent' => FALSE,
        ];
        $row[$id]['description'] = [
          'label' => $summary_config['title'],
          'formattedValue' => $summary_config['title'],
          'show' => (bool) $summary_config['show'],
        ];
        $row[$id]['offerId'] = $this->offerLoan->packageId;
        $verify = $this->configBlock['config']['response']['loanPacketsVerify'];
        $message_success = $this->configBlock['config']['messages']['verifyLoanPackets'];
        $offer_loan_format_currency = $utils_oneapp->formatCurrency($this->offerLoan->price, TRUE, FALSE);
        $message = str_replace('@amount', $offer_loan_format_currency, $message_success['label']);
        $show = (bool) $message_success['show'];
        $row[$id]['confirmation']['message'] = [
          'label' => $message,
          'show' => $show,
        ];
        $row[$id]['confirmation']['orderDetailsTitle'] = [
          'label' => $verify['loanPacketsVerify']['label'],
          'show' => (bool) $verify['loanPacketsVerify']['show'],
        ];
        $row[$id]['confirmation']['targetAccountNumber'] = [
          'label' => $verify['targetAccountNumber']['label'],
          'value' => $this->targetNumber['accountId'],
          'formattedValue' => $this->targetNumber['accountId'],
          'show' => (bool) $verify['targetAccountNumber']['show'],
        ];
        $row[$id]['confirmation']['purchaseDetail'] = [
          'label' => $verify['purchaseDetail']['label'],
          'value' => $this->offer->description,
          'formattedValue' => $this->offer->description,
          'show' => (bool) $verify['purchaseDetail']['show'],
        ];
        $row[$id]['confirmation']['loanAmount'] = [
          'label' => $verify['loanAmount']['label'],
          'value' => $this->offerLoan->price,
          'formattedValue' => $utils_oneapp->formatCurrency($this->offerLoan->price, TRUE, FALSE),
          'show' => (bool) $verify['loanAmount']['show'],
        ];
        $row[$id]['confirmation']['feeAmount'] = [
          'label' => $verify['feeAmount']['label'],
          'value' => $this->offerLoan->fee,
          'formattedValue' => $utils_oneapp->formatCurrency($this->offerLoan->fee, TRUE, FALSE),
          'show' => (bool) $verify['feeAmount']['show'],
        ];
        $row[$id]['confirmation']['paymentMethodTitle'] = [
          'label' => $verify['paymentMethodTitle']['label'],
          'show' => (bool) $verify['paymentMethodTitle']['show'],
        ];
        $row[$id]['confirmation']['paymentMethod'] = [
          'label' => $verify['paymentMethod']['label'],
          'formattedValue' => $verify['paymentMethod']['value'],
          'show' => (bool) $verify['paymentMethod']['show'],
        ];
        $row[$id]['confirmation']['actions']['cancel'] = [
          'label' => $verify['cancelButtons']['label'],
          'url' => $verify['cancelButtons']['url'],
          'type' => $verify['cancelButtons']['type'],
          'show' => (bool) $verify['cancelButtons']['show'],
        ];
        $row[$id]['confirmation']['actions']['purchase'] = [
          'label' => $verify['purchaseButtons']['label'],
          'url' => $verify['purchaseButtons']['url'],
          'type' => $verify['purchaseButtons']['type'],
          'show' => (bool) $verify['purchaseButtons']['show'],
        ];
        $row[$id]['confirmation']['actions']['termsOfServices'] = [
          'label' => $verify['termsAndConditions']['label'],
          'url' => $verify['termsAndConditions']['url'],
          'type' => $verify['termsAndConditions']['type'],
          'show' => (bool) $verify['termsAndConditions']['show'],
        ];
      }
      elseif (empty($this->isRoaming) &&
        ($id == 'emergencyLoan' && $this->loanOption == self::EMERGENCY_LOAN && $this->myNumber == TRUE)) {
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => (bool) $field['show'],
          'isRecurrent' => FALSE,
        ];
      }
      elseif (empty($this->isRoaming) &&
        ($id == 'freePacket' && $this->loanOption == self::FREE_PACKET && $this->myNumber == TRUE)) {
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'offerId' => $this->offer->packageId,
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => (bool) $field['show'],
          'isRecurrent' => FALSE,
        ];
      }
      elseif (empty($this->isRoaming) && ($id == 'tigoMoney' && in_array($this->targetNumber['info'], ['PREPAGO', 'KIT', 'FACTURA FIJA']))) {
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => (bool) $field['show'],
          'isRecurrent' => FALSE,
        ];
        $row[$id]['show'] = $this->myNumber ? $row[$id]['show'] : FALSE;
      }
      $rows[$index] = $row;
    }
    $rows = $this->orderPaymentMethods($rows);
    $actions['paymentMethods'] = $rows;
    $dataconfig['titleDetails'] = [
      'label' => $this->configBlock['fields']['title']['label'],
      'show' => (bool) $this->configBlock['fields']['title']['show'],
    ];
    $dataconfig['paymentMethods'] = [
      'label' => $this->configBlock['config']['actions']['paymentMethodsTitle']['value'],
      'show' => (bool) $this->configBlock['config']['actions']['paymentMethodsTitle']['show'],
    ];
    return [
      'actions' => $actions,
      'dataconfig' => $dataconfig,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isPostpaid() {
    return ($this->tokenInfo['billingType'] !== 'prepaid');
  }

  /**
   * @param $msisdn
   * @return bool
   */
  public function isCorporate($msisdn) {
    $is_corporate = false;
    $this->atpaInfo = empty($this->atpaInfo->Envelope->Body->Subscriber)
      ? $this->availableOffersService->getAtpaInfoByMsisdn($msisdn)
      : $this->atpaInfo;
    if (!empty($this->atpaInfo->Envelope->Body->Subscriber)) {
      $is_corporate = ($this->atpaInfo->Envelope->Body->Subscriber->CustomerRating === 'E');
    }
    return $is_corporate;
  }

  /**
   * Returns data values.
   */
  public function getData($msisdn, $config) {
    $data = [];
    $msisdn = $this->getMsisdn($msisdn);
    $data['msisdn'] = [
      'label' => $config['fields']['msisdn']['label'],
      'value' => $msisdn,
      'formattedValue' => $msisdn,
      'show' => (bool) $config['fields']['msisdn']['show'],
    ];
    if ($this->offer->founded) {
      $utils_oneapp = \Drupal::service('oneapp.utils');
      $format_currency = $utils_oneapp->formatCurrency($this->offer->price, TRUE, FALSE);
      $currency_code = $utils_oneapp->getCurrencyCode(TRUE);
      $value = [
        'amount' => $this->offer->price,
        'currencyId' => $currency_code,
      ];
      $data['amount'] = [
        'label' => $config['fields']['price']['label'],
        'value' => [$value],
        'formattedValue' => $format_currency,
        'show' => (bool) $config['fields']['price']['show'],
      ];
      $data['detail'] = [
        'label' => $config['fields']['description']['label'],
        'formattedValue' => $this->offer->description,
        'show' => (bool) $config['fields']['description']['show'],
      ];
      $validity = $this->sanitizeLabelOfValidity($this->offer);
      $data['period'] = [
        'label' => $config['fields']['period']['label'],
        'formattedValue' => $validity,
        'show' => (bool) $config['fields']['period']['show'],
      ];
    }
    return $data;
  }

  /**
   * Returns data values.
   */
  public function sanitizeLabelOfValidity($package) {
    $validity_type = $package->validityType;
    $validity_number = $package->validityNumber;
    $validity_text = "";
    if ($validity_type == 'Hasta las 23:59:59' || $validity_type == 'hoy a la medianoche (cobro diario)') {
      $validity_text = 'Hoy hasta la Medianoche';
    }
    else {
      switch (strtoupper($validity_type)) {
        case "DIA":
        case "DIAS":
          $validity_type = ($validity_number > 1) ? 'días' : 'dia';
          break;

        case "SEMANA":
        case "SEMANAS":
          $validity_type = ($validity_number > 1) ? 'semanas' : 'semana';
          break;

        case "MES":
        case "MESES":
          $validity_type = ($validity_number > 1) ? 'meses' : 'mes';
          break;
      }

      $validity_text = $validity_number . ' '. $validity_type;
    }
    return $validity_text;
  }

  /**
   * Get Msisdn.
   */
  public function getMsisdn($msisdn) {
    if (isset($this->tigoInvalido['accountId'])) {
      return $this->tigoInvalido['accountId'];
    }
    elseif (isset($this->allowedGift['accountId'])) {
      return $this->allowedGift['accountId'];
    }
    elseif (!$this->offer->founded) {
      return $this->offer->accountId;
    }
    else {
      return $msisdn;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function confirmationCoreBalanceResponse($message, $purchase, $show) {
    $row = [];
    $verify = $this->configBlock['config']['response']['coreBalanceVerify'];
    $row['confirmationTitle'] = [
      'label' => $verify['title']['label'],
      'show' => (bool) $verify['title']['show'],
    ];
    $row['message'] = [
      'label' => $message,
      'show' => $show,
    ];
    $row['orderDetailsTitle'] = [
      'label' => $this->configBlock['config']['response']['coreBalanceVerify']['coreBalanceVerify']['label'],
      'show' => (bool) $this->configBlock['config']['response']['coreBalanceVerify']['coreBalanceVerify']['show'],
    ];
    $row['paymentMethodsTitle'] = [
      'label' => $this->configBlock['config']['response']['coreBalanceVerify']['paymentMethodTitle']['label'],
      'show' => (bool) $this->configBlock['config']['response']['coreBalanceVerify']['paymentMethodTitle']['show'],
    ];
    $row['paymentMethod'] = [
      'label' => $this->configBlock['config']['response']['coreBalanceVerify']['paymentMethod']['label'],
      'show' => (bool) $this->configBlock['config']['response']['coreBalanceVerify']['paymentMethod']['show'],
      "formattedValue" => $this->configBlock['config']['response']['coreBalanceVerify']['paymentMethod']['value'],
    ];
    $row['coreBalancePayment'] = [
      'label' => $this->configBlock['config']['response']['coreBalanceVerify']['coreBalance']['label'],
      'show' => (bool) $this->configBlock['config']['response']['coreBalanceVerify']['coreBalance']['show'],
    ];
    $row['actions']['change'] = [
      'label' => $this->configBlock['config']['response']['coreBalanceVerify']['changeButtons']['label'],
      'url' => $this->configBlock['config']['response']['coreBalanceVerify']['changeButtons']['url'],
      'type' => $this->configBlock['config']['response']['coreBalanceVerify']['changeButtons']['type'],
      'show' => (bool) $this->configBlock['config']['response']['coreBalanceVerify']['changeButtons']['show'],
    ];
    $row['actions']['cancel'] = [
      'label' => $this->configBlock['config']['response']['coreBalanceVerify']['cancelButtons']['label'],
      'url' => $this->configBlock['config']['response']['coreBalanceVerify']['cancelButtons']['url'],
      'type' => $this->configBlock['config']['response']['coreBalanceVerify']['cancelButtons']['type'],
      'show' => (bool) $this->configBlock['config']['response']['coreBalanceVerify']['cancelButtons']['show'],
    ];
    $row['actions']['purchase'] = [
      'label' => $this->configBlock['config']['response']['coreBalanceVerify']['purchaseButtons']['label'],
      'url' => $this->configBlock['config']['response']['coreBalanceVerify']['purchaseButtons']['url'],
      'type' => $this->configBlock['config']['response']['coreBalanceVerify']['purchaseButtons']['type'],
      'show' => ($purchase) ? (bool) $this->configBlock['config']['response']['coreBalanceVerify']['purchaseButtons']['show'] : FALSE,
    ];
    $verify = $this->configBlock['config']['response']['coreBalanceVerify'];
    $row['actions']['termsOfServices'] = [
      'label' => $verify['termsAndConditions']['label'],
      'url' => $verify['termsAndConditions']['url'],
      'type' => $verify['termsAndConditions']['type'],
      'show' => (bool) $verify['termsAndConditions']['show'],
    ];
    return $row;
  }

  /**
   * Parse Product Reference permite eliminar el prefijo NBO- para que no vaya a Payment Gateway
   */
  private function parsePackageId($package_id) {
    $result = str_replace('NBO-', '', $package_id);
    return $result;
  }

}
