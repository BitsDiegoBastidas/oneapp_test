<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp_mobile_upselling\Services\v2_0\ActiveSubscriptionRestLogic;

/**
 * Class ActiveSubscriptionGtRestLogic.
 */
class ActiveSubscriptionGtRestLogic extends ActiveSubscriptionRestLogic {

  protected $packetTigos = [];

  /**
   * {@inheritDoc}
   */
  public function get($msisdn) {
    $data['products'] = $this->getSanitizedActiveSubscriptions($msisdn);
    return $this->getResponseFormatted($data['products'], $msisdn);
  }

  /**
   * Get Active subscriptions.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   Return array of subscriptions.
   *
   * @throws \ReflectionException
   * @throws \Exception
   */
  public function getSanitizedActiveSubscriptions($msisdn) {
    $arr = [];
    $result = $this->getActiveSubscriptions($msisdn);
    $subscriptions = $result->subscriptions;
    foreach ($subscriptions as $subscription) {
      if (isset($subscription->productId) && $this->isAbleSubscription($subscription->productId)) {
        $arr['products'][] = [
          'offerId' => [
            'label' => $this->configBlock['fields']['offerId']['label'],
            'show' => (bool) $this->configBlock['fields']['offerId']['show'],
            'value' => $subscription->productId,
            'formattedValue' => $subscription->productId,
          ],
          'offerName' => [
            'label' => $this->configBlock['fields']['offerName']['label'],
            'show' => (bool) $this->configBlock['fields']['offerName']['show'],
            'value' => $subscription->name,
            'formattedValue' => $subscription->name,
          ],
          'description' => [
            'label' => $this->configBlock['fields']['description']['label'],
            'show' => (bool) $this->configBlock['fields']['description']['show'],
            'value' => $subscription->name,
            'formattedValue' => $subscription->name,
          ],
          'validity' => [
            'label' => $this->configBlock['fields']['validity']['label'],
            'show' => $this->getValidityShow($subscription->nextRenewalDate),
            'value' => [
              'validity' => $subscription->nextRenewalDate,
              'validityUnit' => $subscription->recurrentTimeUnit,
            ],
            'formattedValue' => $this->getDateDiffLabel($subscription->nextRenewalDate),
          ],
          'price' => [
            'label' => $this->configBlock['fields']['precio']['label'],
            'show' => (bool) $this->configBlock['fields']['precio']['show'],
            'value' => [
              'amount' => $subscription->recurrentFee,
              'currencyId' => $this->getCurrencyId($subscription->currency),
            ],
            'formattedValue' => $subscription->currency . $subscription->recurrentFee,
          ],
          'fee' => [
            'label' => $this->configBlock['fields']['fee']['label'],
            'show' => (bool) $this->configBlock['fields']['fee']['show'],
            'value' => [
              'amount' => 0,
              'currencyId' => $this->getCurrencyId($subscription->currency),
            ],
            'formattedValue' => $subscription->currency . 0,
          ],
        ];
      }
    }
    return $arr;
  }

  /**
   * Get currency id.
   *
   * @param string $currency
   *   CurrencyId.
   *
   * @return string
   *   Return string currency id of GT.
   */
  public function getCurrencyId($currency) {
    if ($currency == 'Q') {
      return 'GTQ';
    }
    return '';
  }

  /**
   * Get validity date of subscription.
   *
   * @param string $endDate
   *   End date of the subscription.
   *
   * @return string
   *   Return string date formatted.
   *
   * @throws \Exception
   */
  public function getDateDiffLabel($endDate) {
    $obj = new \DateTime('now');
    $obj1 = new \DateTime($endDate);
    $diff = $obj->diff($obj1);

    if ($diff->d > 2) {
      $text = $diff->h == 1 ? '%a dias %h hora' : '%a dias %h horas';
      $value = $diff->format($text);
    }
    if ($diff->d <= 2 && $diff->h > 3) {
      $hours = $diff->d * 24 + $diff->h;
      $value = $hours . ' horas';
    }
    if ($diff->d == 0 && $diff->h <= 3) {
      $text = $diff->i == 1 ? '%h horas %i minuto' : '%h horas %i minutos';
      $value = $diff->format($text);
    }
    if ($diff->d == 0 && $diff->h < 1) {
      $text = $diff->i == 1 ? '%i minuto' : '%i minutos';
      $value = $diff->format($text);
    }

    return isset($value) ? $value : '';
  }

  /**
   * Get validity show.
   *
   * @param string $nextRenewalDate
   *   Next Renewal Data of the subscription.
   *
   * @return bool
   *   Return true or false.
   */
  public function getValidityShow($nextRenewalDate) {
    $timeNextRenewalDate = strtotime($nextRenewalDate);
    if ($timeNextRenewalDate < time()) {
      return FALSE;
    }
    else {
      return (bool) $this->configBlock['fields']['validity']['show'];
    }
  }

  /**
   * Implements getAvailableOffers.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return ResponseInterface
   *   The HTTP response object.
   *
   * @throws \ReflectionException
   */
  protected function getActiveSubscriptions($msisdn) {
    try {
      return $this->manager
        ->load('oneapp_mobile_v2_0_active_subscription_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      $messages = $this->configBlock['messages'];
      $title = !empty($this->configBlock['label']) ? $this->configBlock['label'] . ': ' : '';
      $message = ($exception->getCode() == '404') ? $title . $messages['empty'] : $title . $messages['error'];

      $reflectedObject = new \ReflectionClass(get_class($exception));
      $property = $reflectedObject->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      throw $exception;
    }
  }

  protected function isAbleSubscription($subscriptionId) {
    if (count($this->packetTigos) == 0) {
      $ids = \Drupal::entityQuery('paquetigos_entity')->execute();
      $this->packetTigos = \Drupal::entityTypeManager()->getStorage('paquetigos_entity')->loadMultiple($ids);
    }
    foreach ($this->packetTigos as $item) {
      if ($item->getIdOffer() == $subscriptionId) {
        return $item->isAbleCoreBalance();
      }
    }
    return TRUE;
  }
}
