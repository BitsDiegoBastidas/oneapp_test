<?php
namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'VoiceBalanceEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v2_0_voice_balance_endpoint",
 * admin_label = @Translation("Voice Balance Prepaid by msisdn v2"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/upselling/subscribers/{msisdn}/balances",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class VoiceBalanceEndpoint extends EndpointBase {}
