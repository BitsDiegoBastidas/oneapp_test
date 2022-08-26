<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Drupal\oneapp\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *  id = "oneapp_home_scheduling_v2_0_visit_details_rest_resource",
 *  label = @Translation("ONEAPP Home Visit Details Gt Rest Resource v2_0"),
 *  uri_paths = {
 *   "canonical" = "/api/v2.0/{businessUnit}/appointments/{idType}/{id}/visits/{appointmentId}"
 *  },
 *   block_id = "oneapp_home_scheduling_gt_v2_0_visit_details_block",
 *   api_response_version = "v2_0"
 * )
 */
class VisitDetailsGtRestResource extends ResourceBase {

  /**
   * {@inheritdoc}
   */
  public function get($idType, $id, $appointmentId) {
    $this->init();
    try {
      $service = \Drupal::service('oneapp_home_scheduling.v2_0.visit_details_rest_logic');
      $service->setConfig($this->configBlock);
      $data = $service->get($this->accountId, $appointmentId);
    }
    catch (\Exception $e) {
      $body = json_decode($e->getMessage());
      $this->apiErrorResponse->getError()->set('message', isset($body->message) ? $body->message : 'Not Found');
      throw new NotFoundHttpException($this->apiErrorResponse, $e);
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
    $service = \Drupal::service('oneapp_home_scheduling.v2_0.visit_details_rest_logic');
    if (isset($data['noData'])) {
      $this->apiResponse
        ->getConfig()
        ->set('message', $this->configBlock["message"]["empty"]["label"]);
    }
    else {
      $actions = $service->getActions($data['visitDetails']['appointmentStatus']['value']);
      /** @var \Drupal\oneapp_home_gt\Services\UtilsGtService $home_utils */
      $home_utils = \Drupal::service('oneapp.home.utils');
      $actions = $home_utils->searchAndgetUrlsByOrigin($this->request->headers->get('Origin'), $actions);
      $forms = $service->getForms();
      $this->apiResponse
        ->getConfig()
        ->set('contactMessage', $service->getContactMessage())
        ->set('actions', $actions)
        ->set('forms', $forms);
    }
  }

}
