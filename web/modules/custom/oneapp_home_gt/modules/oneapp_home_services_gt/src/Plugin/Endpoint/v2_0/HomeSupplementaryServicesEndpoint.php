<?php

namespace Drupal\oneapp_home_services_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'HomeSupplementaryServicesEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_home_services_v2_0_home_supplementary_services_endpoint",
 * admin_label = @Translation("Home Supplementary Services Endpoint V1.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/home/[endpoint:country_iso]/portfolio/subscribers/{msisdn}/supplementaryServices",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class HomeSupplementaryServicesEndpoint extends EndpointBase {

}
