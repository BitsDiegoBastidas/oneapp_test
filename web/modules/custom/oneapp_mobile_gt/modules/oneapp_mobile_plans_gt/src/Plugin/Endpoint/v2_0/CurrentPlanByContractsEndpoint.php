<?php

namespace Drupal\oneapp_mobile_plans_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'CurrentPlanByContractsEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_plans_v2_0_current_by_contracts_endpoint",
 * admin_label = @Translation("Mobile plans current by contracts v2.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/atpa/subscribers/{msisdn}/contracts",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class CurrentPlanByContractsEndpoint extends EndpointBase {

}
