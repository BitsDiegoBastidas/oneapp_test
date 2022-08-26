<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'GetPostpaidContractDetailsEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v1_0_postpaid_contract_details_endpoint",
 * admin_label = @Translation("Mobile upselling postpaid contract details endpoint V1.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v2/tigo/mobile/[endpoint:country_iso]/atpa/subscribers/{msisdn}/contracts",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class GetPostpaidContractDetailsEndpoint extends EndpointBase {

}
