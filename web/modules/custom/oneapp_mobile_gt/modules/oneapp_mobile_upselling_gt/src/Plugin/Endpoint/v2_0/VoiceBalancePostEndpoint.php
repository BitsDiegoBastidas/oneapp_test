<?php
namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'VoiceBalancePostEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v2_0_voice_balance_post_endpoint",
 * admin_label = @Translation("Voice Balance postpago by msisdn v2"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/billing/subscribers/{msisdn}/usage/summary",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class VoiceBalancePostEndpoint extends EndpointBase {}
