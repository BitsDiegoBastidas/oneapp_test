<?php

namespace Drupal\oneapp_mobile_premium_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'DeleteSubscribeVasEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_premium_v2_0_delete_subscribe_vas_endpoint",
 * admin_label = @Translation("Mobile premium Delete Subscribe VAS"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/mobile/gt/vas/subscribers/{id}/products/{offeringId}",
 *    "method" = "DELETE",
 *    "timeout" = 60,
 *  },
 * )
 */
class DeleteSubscribeVasEndpoint extends EndpointBase {

}
