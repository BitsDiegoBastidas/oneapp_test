<?php

namespace Drupal\oneapp_mobile_upselling_gt;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class OneAppMobileUpsellingGtServiceProvider.
 */
class OneappMobileUpsellingGtServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides cron class to use our own service.
    $change_msisdn = $container->getDefinition('oneapp_mobile_upselling.v2_0.change_msisdn_rest_logic');
    $change_msisdn->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\ChangeMsisdnGtRestLogic');

    $packets_order_details = $container->getDefinition('oneapp_mobile_upselling.v2_0.packets_order_details_rest_logic');
    $packets_order_details->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\PacketsOrderDetailsGtRestLogic');

    $recharge_order_details = $container->getDefinition('oneapp_mobile_upselling.v2_0.recharge_order_details_rest_logic');
    $recharge_order_details->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\RechargeOrderDetailsGtRestLogic');

    $upselling_balances = $container->getDefinition('oneapp_mobile_upselling.v2_0.balances_rest_logic');
    $upselling_balances->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\BalancesGtRestLogic');

    $data_balance = $container->getDefinition('oneapp_mobile_upselling.v2_0.data_balance_rest_logic');
    $data_balance->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\DataBalanceGtRestLogic');

    $data_balance_detail = $container->getDefinition('oneapp_mobile_upselling.v2_0.data_balance_detail_rest_logic');
    $data_balance_detail->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\DataBalanceDetailGtRestLogic');

    $offer_details = $container->getDefinition('oneapp_mobile_upselling.v2_0.offer_details_rest_logic');
    $offer_details->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\OfferDetailsGtRestLogic');

    $voice_balance = $container->getDefinition('oneapp_mobile_upselling.v2_0.voice_balance_rest_logic');
    $voice_balance->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\VoiceBalanceGtRestLogic');

    $sms_balance = $container->getDefinition('oneapp_mobile_upselling.v2_0.sms_balance_rest_logic');
    $sms_balance->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\SmsBalanceGtRestLogic');

    $available_offers_service = $container->getDefinition('oneapp_mobile_upselling.v2_0.available_offers_services');
    $available_offers_service->setClass('Drupal\oneapp_mobile_upselling_gt\Services\AvailableOffersGtServices');

    $available_offers = $container->getDefinition('oneapp_mobile_upselling.v2_0.available_offers_rest_logic');
    $available_offers->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\AvailableOffersGtRestLogic');

    $acquired_offers = $container->getDefinition('oneapp_mobile_upselling.v2_0.acquired_offers_rest_logic');
    $acquired_offers->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\AcquiredOffersGtRestLogic');

    $unsubscribed_offers = $container->getDefinition('oneapp_mobile_upselling.v2_0.unsubscribe_offers_rest_logic');
    $unsubscribed_offers->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\UnsubscribeOffersGtRestLogic');

    $recharge_lending_amounts = $container->getDefinition('oneapp_mobile_upselling.v2_0.recharge_lending_amounts_rest_logic');
    $recharge_lending_amounts->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\RechargeLendingAmountsGtRestLogic');

    $active_subscription = $container->getDefinition('oneapp_mobile_upselling.v2_0.active_subscription_rest_logic');
    $active_subscription->setClass('Drupal\oneapp_mobile_upselling_gt\Services\v2_0\ActiveSubscriptionGtRestLogic');

    $recharge_lending_amounts_services = $container->getDefinition('oneapp_mobile_upselling.v2_0.recharge_lending_amount_services');
    $recharge_lending_amounts_services->setClass('Drupal\oneapp_mobile_upselling_gt\Services\RechargeLendingAmountsGtServices');
  }

}
