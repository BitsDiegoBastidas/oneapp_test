<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;

/**
 * Provides a 'TechnicianLocationVisitEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_home_scheduling_v2_0_technician_location_visit_endpoint",
 * admin_label = @Translation("Home Scheduling Technician Location Visit v2.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/{businessUnit}/work-orders/country/[endpoint:country_iso]/events/{appointmentId}",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class TechnicianLocationVisitEndpoint extends EndpointBase{

}

//https://qa.api.tigo.com/v1/tigo/home/work-orders/country/gt/events/GTM-00000001
