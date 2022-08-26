<?php

namespace Drupal\oneapp_home_scheduling_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\ResourceResponse;
use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\oneapp_home_scheduling\Plugin\rest\resource\v2_0\VisitRescheduleRestResource;
use Drupal\oneapp_home_scheduling_gt\Services\v2_0\VisitRescheduleGtRestLogic;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *  id = "oneapp_home_scheduling_v2_0_visit_reschedule_gt_rest_resource",
 *  label = @Translation("ONEAPP Home Visit Reschedule Gt Rest Resource v2_0"),
 *  uri_paths = {
 *   "canonical" = "/api/v2.0/{businessUnit}/appointments/{idType}/{id}/reschedule/{appointmentId}/{externalId}"
 *  },
 *   block_id = "oneapp_home_scheduling_gt_v2_0_visit_reschedule_block",
 *   api_response_version = "v2_0"
 * )
 */
class VisitRescheduleGtRestResource extends VisitRescheduleRestResource {

  /**
   * {@inheritdoc}
   */
  public function get($idType, $id, $appointmentId, $externalId = '') {
    $this->init();
    try {
      /** @var VisitRescheduleGtRestLogic $reschedule_rest_logic */
      $reschedule_rest_logic = \Drupal::service('oneapp_home_scheduling.v2_0.visit_reschedule_rest_logic');
      $reschedule_rest_logic->setConfig($this->configBlock);
      $reschedule_rest_logic->origin = $this->request->headers->get('Origin');
      $data = $reschedule_rest_logic->get($this->accountId, $appointmentId, $externalId);
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
   * @param $id_type
   * @param $id
   * @param $appointment_id
   * @param string $external_id
   * @param array $body
   * @param Request|null $request
   * @return ModifiedResourceResponse
   */
  public function patch($idType, $id, $appointmentId, $externalId = '', $body = [], Request $request = null) {
    $this->init();
    try {
      /** @var VisitRescheduleGtRestLogic $reschedule_rest_logic */
      $reschedule_rest_logic = \Drupal::service('oneapp_home_scheduling.v2_0.visit_reschedule_rest_logic');
      $reschedule_rest_logic->origin = $this->request->headers->get('Origin');
      $reschedule_rest_logic->setConfig($this->configBlock);
      // Enviar data por PATCH a apigee.
      $query_params = $this->request->query->all();
      $response_data = $reschedule_rest_logic->patch($this->accountId, $appointmentId, $query_params, $externalId);
    } catch (\Exception $e) {
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
    return new ModifiedResourceResponse($this->apiResponse, $response_data['status'] == 'failed' ? 404 : 200);

  }

  /**
   * Returns config data. (Optional)
   *
   * @param array $data
   *   Additional data.
   */
  public function responseConfig(array $data = NULL) {
    $service = \Drupal::service('oneapp_home_scheduling.v2_0.visit_reschedule_rest_logic');
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
