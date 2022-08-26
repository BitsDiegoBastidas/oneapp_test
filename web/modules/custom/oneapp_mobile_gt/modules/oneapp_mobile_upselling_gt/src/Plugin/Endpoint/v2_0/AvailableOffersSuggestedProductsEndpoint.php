<?php

namespace Drupal\oneapp_mobile_upselling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'AvailableOffersSuggestedProductsEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_upselling_v1_0_available_offers_suggested_products_endpoint",
 * admin_label = @Translation("Mobile upselling available offers suggested products endpoint V1.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/mobile/[endpoint:country_iso]/profiling/subscribers/{msisdn}/suggestedProducts/",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class AvailableOffersSuggestedProductsEndpoint extends EndpointBase {

}
