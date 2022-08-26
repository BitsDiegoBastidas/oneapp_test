<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Drupal\oneapp\Exception\NotFoundHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *  id = "oneapp_home_scheduling_v2_0_visit_schedule_rest_resource",
 *  label = @Translation("ONEAPP Home Visit Schedule Gt Rest Resource v2_0"),
 *  uri_paths = {
 *   "canonical" = "/api/v2.0/{businessUnit}/appointments/{idType}/{id}/schedule/{appointmentId}"
 *  },
 *   block_id = "oneapp_home_scheduling_gt_v2_0_visit_schedule_block",
 *   api_response_version = "v2_0"
 * )
 */
class VisitScheduleGtRestResource extends ResourceBase {

  /**
   * {@inheritdoc}
   */
  public function get($idType, $id, $appointmentId) {
    $this->init();
    try {
      $reschedule_rest_logic = \Drupal::service('oneapp_home_scheduling.v2_0.visit_schedule_rest_logic');
      $reschedule_rest_logic->setConfig($this->configBlock);
      $data = $reschedule_rest_logic->get($this->accountId, $appointmentId);
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
   * Method Patch.
   */
  public function patch($idType, $id, $appointmentId) {
    $this->init();
    try {
      $schedule_rest_logic = \Drupal::service('oneapp_home_scheduling.v2_0.visit_schedule_rest_logic');
      $schedule_rest_logic->setConfig($this->configBlock);
      // Enviar data por PATCH a apigee.
      $query_params = $this->request->query->all();
      $response_data = $schedule_rest_logic->patch($this->accountId, $appointmentId, $query_params);
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->apiErrorResponse->getError()->set('message', isset($message) ? $message : t('Not Found'));
      throw new NotFoundHttpException($this->apiErrorResponse, $e);
    }

    // Seteamos los datos.
    $this->apiResponse->getData()->setAll([]);

    // Seteamos la configuración adicional.
    $this->apiResponse
      ->getConfig()
      ->set('status', $response_data['status'])
      ->set('message', $response_data['message'])
      ->set('actions', $response_data['actions']);
    // Seteamos los datos en el response.

    return new ModifiedResourceResponse($this->apiResponse, 200);
  }

  /**
   * Returns config data. (Optional)
   *
   * @param array $data
   *   Additional data.
   */
  public function responseConfig(array $data = NULL) {
    $service = \Drupal::service('oneapp_home_scheduling.v2_0.visit_schedule_rest_logic');
    if (isset($data['noData'])) {
      $this->apiResponse
        ->getConfig()
        ->set('message', $this->configBlock["message"]["empty"]["label"]);
    }
    else {
      $actions = $service->getActions();
      $message = $service->getMessage();
      $status = $service->getStatus();
      $this->apiResponse
        ->getConfig()
        ->set('status', $status)
        ->set('message', $message)
        ->set('actions', $actions);
    }
  }

}
