<?php

namespace Drupal\oneapp_mobile_plans_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'CurrentPlanEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_plans_v2_0_current_plan_endpoint",
 * admin_label = @Translation("Mobile plans Get current plan v2.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/billing/subscribers/{msisdn}/usage/summary",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class CurrentPlanEndpoint extends EndpointBase {

}
