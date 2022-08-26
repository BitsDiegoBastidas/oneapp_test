<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\UnsubscribeOffersRestLogic;

/**
 * Class UnsubscribeOffersRestLogic.
 */
class UnsubscribeOffersGtRestLogic extends UnsubscribeOffersRestLogic {

  /**
   * Responds to delete requests.
   *
   * @param string $msisdn
   *   Msisdn.
   * @param string $data
   *   Data.
   *
   * @return Mixed
   *   The HTTP response object.
   *
   * @throws \ReflectionException
   */
  public function delete($msisdn, $data) {
    $msisdn = $this->msisdnValid($msisdn);
    return $this->deleteSubscription($msisdn, $data['offerId']);
  }

  public function deleteSubscription($msisdn, $package_id) {
    $data = [];

    $response = $this->unSubscribeOffers($msisdn, $package_id);
    $success = ($response->status === "OK") ? TRUE : FALSE;
    if ($success) {
      $data['result'] = [
        'label' => $this->configBlock['config']['response']['deleteSubscribeSuccess']['label'],
        'formattedValue' => $this->configBlock['config']['response']['deleteSubscribeSuccess']['label'],
        'value' => TRUE,
        'show' => (bool) $this->configBlock['config']['response']['deleteSubscribeSuccess']['show'],
      ];
    }
    else {
      $data['result'] = [
        'label' => $this->configBlock['config']['response']['deleteSubscribeFailed']['label'],
        'formattedValue' => $this->configBlock['config']['response']['deleteSubscribeFailed']['label'],
        'value' => FALSE,
        'show' => (bool) $this->configBlock['config']['response']['deleteSubscribeFailed']['show'],
      ];
    }
    return [
      'data' => $data,
      'success' => $success,
    ];
  }

  /**
   * Implements unSubscribeOffers.
   *
   * @param string $msisdn
   *   Billing account value.
   * @param string $package_id
   *   Data value.
   *
   * @return object
   *   Data object.
   */
  private function unSubscribeOffers($msisdn, $package_id) {
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_unsubsribe_offers_endpoint')
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn, 'packageId' => $package_id])
        ->setBody([])
        ->setHeaders(['Content-Type' => 'application/json'])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      if ($exception->getCode() == 403) {
        $obj = new \stdClass();
        $obj->status = 'ERROR';
        return $obj;
      }
      $messages = $this->configBlock['config']['response']['getInfo'];
      $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
      $message = ($exception->getCode() == '404') ? $title . $messages['notFound'] : $title . $messages['error'];

      $reflected_object = new \ReflectionClass(get_class($exception));
      $property = $reflected_object->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);
      throw $exception;
    }
  }

  /**
   * {@inheritdoc}
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

}
