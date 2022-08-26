<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\AcquiredOffersRestLogic;

/**
 * Class AcquiredOffersRestLogic.
 */
class AcquiredOffersGtRestLogic extends AcquiredOffersRestLogic {

  /**
   * @var \Drupal\oneapp\Services\UtilsService
   */
  protected $utils;

  /**
   * @var \Drupal\oneapp_mobile_gt\Services\UtilsServiceGt
   */
  protected $mobileUtils;

  protected $tokenInfo = [];

  protected $isRoaming = false;

  /**
   * Responds to post requests.
   *
   * @param string $msisdn
   *   Msisdn.
   * @param array $data
   *   Data.
   *
   * @return Mixed
   *   The HTTP response object.
   *
   * @throws \ReflectionException
   */
  public function post($msisdn, $data) {
    $package_id =& $data['packageId'];
    $this->isRoaming = boolval(stristr($package_id, 'ROAM'));
    $package_id = $this->isRoaming ?  str_replace('ROAM', '', $package_id) : $package_id;
    $isFavorite = false;
    $data['packageId'] = $this->parsePackageId($data['packageId']);
    /** @var \Drupal\oneapp_mobile_upselling_gt\Services\v2_0\AvailableOffersGtRestLogic $availableOfferService */
    $availableOfferService = \Drupal::service('oneapp_mobile_upselling.v2_0.available_offers_rest_logic');
    if (\Drupal::request()->query->get('targetMSISDN')) {
      $target = \Drupal::request()->query->get('targetMSISDN');
    }
    elseif (\Drupal::request()->query->get('targetMsisdn')) {
      $target = \Drupal::request()->query->get('targetMsisdn');
    }
    $formattedMsisdn = \Drupal::service('oneapp.mobile.utils')->getFormattedMsisdn($msisdn);
    $this->tokenInfo = $this->mobileUtils->getInfoTokenByMsisdn($msisdn);
    if ($target == $formattedMsisdn) {
      $msisdn = $this->msisdnValid($msisdn);
      $isPostpaid = $availableOfferService->isPostpaid($msisdn);
      $product = $availableOfferService->findProductById($data['packageId'], $msisdn, $this->isRoaming);
      $isFavorite = $product['isFavorite'];
      if (isset($product['price']['value'])) {
        $response = $this->adquireOffers($msisdn, $data['paymentMethodName'],$data['packageId'], $product, $isPostpaid);
      }
      else {
        $result = [
          'result' => [
            'label' => $this->getTitleLabel($data['paymentMethodName'], FALSE),
            'formattedValue' => $this->getMessageLabel($data['paymentMethodName'], FALSE),
            'value' => false,
            'show' => (bool) $this->getTitleShow($data['paymentMethodName'], FALSE),
          ],
        ];
        $response = [
          'data' => $result,
          'isFavorite' => $isFavorite,
          'success' => false,
        ];
      }

    }
    else {
      $result = [
        'result' => [
          'label' => $this->getTitleLabel($data['paymentMethodName'], FALSE),
          'formattedValue' => $this->configBlock['config']['messages']['gift_not_allowed']['label'],
          'value' => false,
          'show' => (bool) $this->getTitleShow($data['paymentMethodName'], FALSE),
        ],
      ];
      $response = [
        'data' => $result,
        'isFavorite' => $isFavorite,
        'success' => false,
      ];
    }
    return $response;
  }

  public function adquireOffers($msisdn, $paymentMethodName, $packageId, $product, $isPostpaid) {
    $result = [];
    $success = false;
    if ($this->isRoaming) {
      $response = $this->acquiredRoamingOffer($msisdn, $product);
    }
    else {
      $response = $this->acquiredOffers($msisdn, $packageId, $isPostpaid);
    }
    if ($response->status == 'OK') {
      $success = true;
      $result['result'] = [
        'label' => $this->getTitleLabel($paymentMethodName),
        'formattedValue' => $response->responseMessage,
        'value' => true,
        'show' => (bool) $this->getTitleShow($paymentMethodName)
      ];
      $result['transactionDetails'] = [
        'title' => [
          'label' => $this->configBlock['config']['response']['postSuccess']['transactionDetailsTitle']['value'],
          'show' => (bool) $this->configBlock['config']['response']['postSuccess']['transactionDetailsTitle']['show'],
        ],
        'orderId' => [
          'label' => $this->configBlock['config']['response']['postSuccess']['transactionDetailsId']['value'],
          'value' => $response->transactionId,
          'formattedValue' => $response->transactionId,
          'show' => (bool) $this->configBlock['config']['response']['postSuccess']['transactionDetailsId']['show'],
        ],
        'detail' => [
          'label' => $this->configBlock['config']['response']['postSuccess']['transactionDetailsDetail']['value'],
          'formattedValue' => $product['description'],
          'show' => (bool) $this->configBlock['config']['response']['postSuccess']['transactionDetailsDetail']['show'],
        ],
        'targetMSISDN' => [
          'label' => $this->configBlock['config']['response']['postSuccess']['transactionDetailsMSISDN']['value'],
          'value' => $msisdn,
          'formattedValue' => $msisdn,
          'show' => (bool) $this->configBlock['config']['response']['postSuccess']['transactionDetailsMSISDN']['show'],
        ],
        'validity' => [
          'label' => $this->configBlock['config']['response']['postSuccess']['transactionDetailsValidity']['value'],
          'value' => $product['validity'],
          'formattedValue' => $product['validity'],
          'show' =>  intval($product['validity']) > 0 ? (bool) $this->configBlock['config']['response']['postSuccess']['transactionDetailsValidity']['show'] : false,
        ],
        'price' => [
          'label' => $this->configBlock['config']['response']['postSuccess']['transactionDetailsPrice']['value'],
          'value' => [
            'amount' => $product['price']['value'],
            'currencyId' => 'GTQ',
          ],
          'formattedValue' => $product['price']['formattedValue'],
          'show' => (bool) $this->configBlock['config']['response']['postSuccess']['transactionDetailsPrice']['show'],
        ],
      ];
      $result['paymentMethod'] = [
        'label' => $this->getPaymentMethodLabel($paymentMethodName),
        'formattedValue' => $this->getPaymentMethodValue($paymentMethodName),
        'value' => $this->getPaymentMethodValue($paymentMethodName),
        'show' => (bool) $this->getPaymentMethodShow($paymentMethodName),
      ];
    }
    elseif ($response->status == 'ERROR') {
      $result = [
        'result' => [
          'label' => $this->getTitleLabel($paymentMethodName, FALSE),
          'formattedValue' => $response->message,
          'value' => false,
          'show' => (bool) $this->getTitleShow($paymentMethodName, FALSE),
        ]
      ];
    }
    return [
      'data' => $result,
      'isFavorite' => $product['isFavorite'],
      'success' => $success,
    ];
  }

  public function getPaymentMethodValue($paymentMethodName) {
    $result = '';
    if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
      $result = $this->configBlock['config']['response']['postSuccessLoan']['paymentMethod']['value'];
    }
    else {
      $result = $this->configBlock['config']['actions'][$paymentMethodName]['label'];
      $result = str_replace(':', '', $result);
    }
    return $result;
  }
  public function getPaymentMethodLabel($paymentMethodName) {
    $result = '';
    if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
      $result = $this->configBlock['config']['response']['postSuccessLoan']['paymentMethod']['label'];
    }
    else {
      $result = $this->configBlock['config']['response']['postSuccess']['paymentMethod']['label'];
    }
    return $result;
  }

  public function getPaymentMethodShow($paymentMethodName) {
    $result = '';
    if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
      $result = $this->configBlock['config']['response']['postSuccessLoan']['paymentMethod']['show'];
    }
    else {
      $result = $this->configBlock['config']['response']['postSuccess']['paymentMethod']['show'];
    }
    return $result;
  }

  public function getTitleLabel($paymentMethodName, $success = TRUE) {
    $result = '';
    if ($success) {
      if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
        $result = $this->configBlock['config']['response']['postSuccessLoan']['title']['label'];
      }
      else {
        $result = $this->configBlock['config']['response']['postSuccess']['title']['label'];
      }
    }
    else {
      if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
        $result = $this->configBlock['config']['response']['postFailedLoan']['title']['label'];
      }
      else {
        $result = $this->configBlock['config']['response']['postFailed']['title']['label'];
      }
    }
    return $result;
  }

  public function getTitleShow($paymentMethodName, $success = TRUE) {
    $result = '';
    if ($success) {
      if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
        $result = $this->configBlock['config']['response']['postSuccessLoan']['title']['show'];
      } else {
        $result = $this->configBlock['config']['response']['postSuccess']['title']['show'];
      }
    }
    else {
      if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
        $result = $this->configBlock['config']['response']['postFailedLoan']['title']['show'];
      } else {
        $result = $this->configBlock['config']['response']['postFailed']['title']['show'];
      }
    }
    return $result;
  }

  public function getMessageLabel($paymentMethodName, $success = TRUE) {
    $result = '';
    if ($success) {
      if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
        $result = $this->configBlock['config']['response']['postSuccessLoan']['message']['label'];
      }
      else {
        $result = $this->configBlock['config']['response']['postSuccess']['message']['label'];
      }
    }
    else {
      if (strpos(strtolower($paymentMethodName), 'loan') !== FALSE) {
        $result = $this->configBlock['config']['response']['postFailedLoan']['message']['label'];
      }
      else {
        $result = $this->configBlock['config']['response']['postFailed']['message']['label'];
      }
    }
    return $result;
  }

  /**
   * Implements acquireOffers.
   *
   * @param string $billingAccountId
   *   Billing account value.
   * @param string $data
   *   Data value.
   *
   * @return object
   *   Data object.
   */
  private function acquiredOffers($msisdn, $packageId, $isPostpaid) {
    try {
      if ($isPostpaid) {
        return $this->manager
          ->load('oneapp_mobile_upselling_v2_0_acquired_offers_postpaid_user_endpoint')
          ->setHeaders(['Content-Type' => 'application/json'])
          ->setQuery([])
          ->setParams(['msisdn' => $msisdn, 'packageId' => $packageId])
          ->sendRequest();
      }
      else {
        return $this->manager
          ->load('oneapp_mobile_upselling_v2_0_acquired_offers_endpoint')
          ->setHeaders(['Content-Type' => 'application/json'])
          ->setQuery([])
          ->setParams(['msisdn' => $msisdn, 'packageId' => $packageId])
          ->sendRequest();
      }
    }
    catch (HttpException $exception) {
      if ($exception->getCode() == 403) {
        $resultObject = new \stdClass();
        $resultObject->status = 'ERROR';
        $resultObject->message = $exception->getMessage();
        return $resultObject;
      }
      else {
        $messages = $this->configBlock['messages'];
        $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
        $message = ($exception->getCode() == 404) ? $title . $messages['empty'] : $title . $messages['error'];
        $reflectedObject = new \ReflectionClass(get_class($exception));
        $property = $reflectedObject->getProperty('message');
        $property->setAccessible(TRUE);
        $property->setValue($exception, $message);
        $property->setAccessible(FALSE);
        throw $exception;
      }

    }
  }

  /**
   * @param $msisdn
   * @param array $product_data
   * @return \stdClass
   * @throws \ReflectionException
   */
  private function acquiredRoamingOffer($msisdn, $product_data) {
    try {
      $body = [
        'packageCode' => $product_data['packageId'],
        'packageName' => $product_data['description'],
        'packagePrice' => $product_data['price']['value'],
        'mcc' => '',
        'mnc' => ''
      ];
      $query = ['channelId' => 63];
      $response = $this->acquiredOffersServices->acquireRoamingOffer($msisdn, $body, $query);
      $response->status = 'OK';
      $response->responseMessage = $response->message;
      return $response;
    }
    catch (HttpException $exception) {
      if ($exception->getCode() == 403) {
        $resultObject = new \stdClass();
        $resultObject->status = 'ERROR';
        $resultObject->message = $exception->getMessage();
        return $resultObject;
      }
      else {
        $messages = $this->configBlock['messages'];
        $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
        $message = ($exception->getCode() == 404) ? $title . $messages['empty'] : $title . $messages['error'];
        $reflectedObject = new \ReflectionClass(get_class($exception));
        $property = $reflectedObject->getProperty('message');
        $property->setAccessible(TRUE);
        $property->setValue($exception, $message);
        $property->setAccessible(FALSE);
        throw $exception;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function msisdnValid($msisdn) {
    $mobileSettings = \Drupal::config('oneapp_mobile.config')->get('general');
    $globalSettings = \Drupal::config('oneapp_endpoints.settings')->getRawData();
    $msisdnLenght = $mobileSettings['msisdn_lenght'];
    $prefixCountry = $globalSettings['prefix_country'];
    if (strlen($msisdn) <= $msisdnLenght && !preg_match("/^{$prefixCountry}[0-9]{$msisdnLenght}$/", $msisdn)) {
      $msisdn = $prefixCountry . $msisdn;
    }
    return $msisdn;
  }

  /**
   * Parse Product Reference permite eliminar el prefijo NBO- para que no vaya a Payment Gateway
   */
  private function parsePackageId($package_id) {
    $result = str_replace('NBO-', '', $package_id);
    return $result;
  }
}
