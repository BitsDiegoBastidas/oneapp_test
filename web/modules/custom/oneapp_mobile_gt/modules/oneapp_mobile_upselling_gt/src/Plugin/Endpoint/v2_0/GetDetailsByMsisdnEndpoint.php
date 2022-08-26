<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'GetPostpaidContractDetailsEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v1_0_details_by_msisdn_endpoint",
 * admin_label = @Translation("Mobile upselling details by msisdn endpoint V1.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/atpa/subscribers/{msisdn}",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class GetDetailsByMsisdnEndpoint extends EndpointBase {

}
