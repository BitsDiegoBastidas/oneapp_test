<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'AvailableOffersPostpaidEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v1_0_available_offers_postpaid_endpoint",
 * admin_label = @Translation("Mobile upselling available offers postpaid endpoint V1.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/mobile/[endpoint:country_iso]/vas/subscribers/{msisdn}/products",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class AvailableOffersPostpaidEndpoint extends EndpointBase {

}
