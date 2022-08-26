<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'AcquiredOffersEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v2_0_acquired_offers_postpaid_user_endpoint",
 * admin_label = @Translation("Mobile upselling acquired offers for postpaid user v2.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/mobile/[endpoint:country_iso]/vas/subscribers/{msisdn}/products/{packageId}",
 *    "method" = "POST",
 *    "timeout" = 60,
 *  },
 * )
 */
class AcquiredOffersPostpaidEndpoint extends EndpointBase {

}
