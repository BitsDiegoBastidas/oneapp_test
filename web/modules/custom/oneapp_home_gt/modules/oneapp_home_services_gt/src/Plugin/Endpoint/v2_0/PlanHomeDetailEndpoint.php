<?php

namespace Drupal\oneapp_home_services_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'PlanHomeDetailEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_home_services_v2_0_plan_home_detail_endpoint",
 * admin_label = @Translation("Plan Home Detail Endpoint V1.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/home/[endpoint:country_iso]/portfolio/plans/{planCode}",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class PlanHomeDetailEndpoint extends EndpointBase {

}
