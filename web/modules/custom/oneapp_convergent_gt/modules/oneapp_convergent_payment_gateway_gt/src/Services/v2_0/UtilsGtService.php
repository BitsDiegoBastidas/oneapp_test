<?php

namespace Drupal\oneapp_convergent_payment_gateway_gt\Services\v2_0;

use Symfony\Component\HttpFoundation\Response;
use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\BadRequestHttpException;
use Drupal\Core\Render\Markup;
use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_convergent_payment_gateway\Services\v2_0\UtilsService;


/**
 * Class UtilsService for paymentGateway.
 */
class UtilsGtService  extends UtilsService {

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $transactions;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $tokenAuthorization;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  private $token;

  /**
   * Default configuration general convergente.
   *
   * @var mixed
   */
  private $configConvergente;

  /**
   * Default manager.
   *
   * @var mixed
   */
  private $manager;

  /**
   * PaymentGatewayRestLogic constructor.
   */
  public function __construct($transactions, $tokenAuthorization) {
    $this->transactions = $transactions;
    $this->tokenAuthorization = $tokenAuthorization;
  }

  /**
   * Obtener el accountNumber del Token.
   */
  public function getAccountNumberForPaymentGatewayFromToken($businessUnit, $id, $accountType = 'billingAccountId') {
    try {
      if ($businessUnit == 'home') {    
        $accountNumber = $id;
        $accountNumber = (string) $this->getInfoBySubscriberId($id);
        return $accountNumber;
      }
      else {
        $utilsService = \Drupal::service('oneapp.mobile.utils');
        $accountNumber = $utilsService->getAccountNumberForPaymentGatewayByMsisdn($id, $accountType);
        return $accountNumber;
      }
    }
    catch (\Exception $exception) {
      $this->sendException(t("En este momento no podemos obtener información de la cuenta buscada."), $exception->getCode(), $exception);
    }
  }

  /**
   * Get info by suscriberID.
   *
   * @param string $string
   *   Suscriber ID.
   */   
  public  function getInfoBySubscriberId($subscriberId) {
    $manager = \Drupal::service('oneapp_endpoint.manager');
    try {
      $response =  $manager
        ->load('oneapp_mobile_upselling_v1_0_details_by_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $subscriberId])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      throw new \Exception($exception->getMessage(), $exception->getCode());
    }

    if (isset($response->Envelope->Body->Subscriber->Attache)) {
      return $response->Envelope->Body->Subscriber->Attache;
    }

    return $subscriberId;

  }

}

