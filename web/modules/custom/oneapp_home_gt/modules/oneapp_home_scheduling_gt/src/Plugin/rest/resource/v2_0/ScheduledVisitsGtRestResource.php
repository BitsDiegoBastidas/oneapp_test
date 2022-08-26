<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\oneapp\Exception\NotFoundHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *  id = "oneapp_home_scheduling_v2_0_scheduled_visits_rest_resource",
 *  label = @Translation("ONEAPP Home Scheduled Visits Gt Rest Resource v2_0"),
 *  uri_paths = {
 *   "canonical" = "/api/v2.0/{businessUnit}/appointments/{idType}/{id}/visits"
 *  },
 *   block_id = "oneapp_home_scheduling_gt_v2_0_scheduled_visits_block",
 *   api_response_version = "v2_0"
 * )
 */
class ScheduledVisitsGtRestResource extends ResourceBase {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->init();
    $is_hide = !empty($this->configBlock['others']['hide']['show']);
    $service_rest_logic = \Drupal::service('oneapp_home_scheduling.v2_0.scheduled_visits_rest_logic');
    if ($is_hide) {
      $data = $service_rest_logic->hideState();
    }
    else {
      try {
        $service_rest_logic->setConfig($this->configBlock);
        $data = $service_rest_logic->get($this->accountId);
      }
      catch (\Exception $e) {
        $body = json_decode($e->getMessage());
        $this->apiErrorResponse->getError()->set('message', isset($body->message) ? $body->message : 'Not Found');
        throw new NotFoundHttpException($this->apiErrorResponse, $e);
      }
    }
    // Seteamos la configuración adicional.
    $this->responseConfig($data);

    // Seteamos los datos.
    $this->apiResponse->getData()->setAll($data);

    // Seteamos los datos en el response.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * Returns config data. (Optional)
   *
   * @param array $data
   *   Additional data.
   */
  public function responseConfig(array $data = NULL) {
    if (isset($data['noData'])) {
      $message = $data['noData']['value'] == 'hide' ? $this->configBlock['others']['hide']['message'] :
        $this->configBlock["message"]["empty"]["label"];
      $this->apiResponse
        ->getConfig()
        ->set('message', $message);
    }
    else {
      $service_rest_logic = \Drupal::service('oneapp_home_scheduling.v2_0.scheduled_visits_rest_logic');
      $this->apiResponse
        ->getConfig()
        ->set('descriptionCard', $service_rest_logic->getDescriptionCard())
        ->set('visitMessage', $service_rest_logic->getNewVisit())
        ->set('actions', $service_rest_logic->getActions());
    }
  }

}
