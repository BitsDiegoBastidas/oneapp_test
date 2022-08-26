<?php

namespace Drupal\oneapp_mobile_premium_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'DocomoSupermarketSendLicenseOffice' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_premium_v2_0_docomo_office_send_license_endpoint",
 * admin_label = @Translation("Mobile premium docomo Office Send License"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/docomo/supermarket/mpay-ws/v2/country/[endpoint:country_iso]/site/{serviceId}/subscription/{confirmationId}/license/send",
 *    "method" = "POST",
 *    "timeout" = 60,
 *  },
 * )
 */
class DocomoOfficeSendLicenseEndpoint extends EndpointBase {

}
