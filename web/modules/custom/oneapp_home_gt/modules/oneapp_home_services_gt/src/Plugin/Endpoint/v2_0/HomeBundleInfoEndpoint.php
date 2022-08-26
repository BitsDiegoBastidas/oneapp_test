<?php

namespace Drupal\oneapp_home_services_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'HomeBundleInfoEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_home_services_v2_0_home_bundle_info_endpoint",
 * admin_label = @Translation("Home Bundle Info Endpoint V1.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/home/[endpoint:country_iso]/portfolio/contracts/{subscriberId}/plan/bundle",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class HomeBundleInfoEndpoint extends EndpointBase {

}
