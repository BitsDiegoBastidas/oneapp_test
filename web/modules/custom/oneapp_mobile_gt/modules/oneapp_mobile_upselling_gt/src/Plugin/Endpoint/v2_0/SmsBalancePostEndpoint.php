<?php
namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'SmsBalanceEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v2_0_sms_balance_gt_postpaid",
 * admin_label = @Translation("Sms Balance Postpaid v2 by Billing AccountId"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/billing/subscribers/{msisdn}/usage/summary",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class SmsBalancePostEndpoint extends EndpointBase{}
