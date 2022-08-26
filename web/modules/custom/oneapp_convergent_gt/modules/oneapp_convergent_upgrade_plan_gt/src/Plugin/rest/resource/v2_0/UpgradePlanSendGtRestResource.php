<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Plugin\rest\resource\v2_0;

use Drupal\oneapp_rest\Plugin\ResourceBase;
use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 *
 * @RestResource(
 *   api_response_version = "v2_0",
 *   block_id = "oneapp_convergent_upgrade_plan_v2_0_upgrade_block",
 *   id = "oneapp_convergent_upgrade_plan_plan_send_v2_0_rest_resource",
 *   label = @Translation("Oneapp Convergent Upgrade Plan Home Send Gt v2_0"),
 *   uri_paths = {
 *     "canonical" = "/api/v2.0/home/plans/{idType}/{id}/currentplan",
 *   }
 * )
 */
class UpgradePlanSendGtRestResource extends ResourceBase {

  /**
   * @var \Drupal\oneapp_convergent_upgrade_plan_gt\Services\v2_0\UpgradePlanSendGtRestLogic
   */
  protected $planSendRestLogic;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->planSendRestLogic = $container->get('oneapp_convergent_upgrade_plan.v2_0.plan_send_rest_logic');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function put($idType, $id, Request $request) {
    $this->init();
    $data = json_decode($request->getContent(), true);
    $response_data = [];
    try {
      $this->planSendRestLogic->setConfig($this->configBlock);
      $this->planSendRestLogic->validationData($data);
      $response_data = $this->planSendRestLogic->updateCurrentPlanHome($id, $data, $idType);
    }
    catch (\Exception $e) {
      $this->apiErrorResponse->getError()->set('message', $e->getMessage());
      throw new NotFoundHttpException($this->apiErrorResponse, $e);
    }

    $this->responseConfig($response_data);
    $this->apiResponse->getData()->setAll($response_data);

    // Seteamos los datos en el response.
    $response = new ResourceResponse($this->apiResponse, empty($response_data['result']['value']) ? 404 : 200);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function responseConfig(array $data = []) {

    $data_config = $this->planSendRestLogic->getDataConfig($data);

    if (!empty($data_config['message'])) {
      $this->apiResponse
        ->getConfig()
        ->set('message', $data_config['message']);
    }

    if (!empty($data_config['result'])) {
      $this->apiResponse
        ->getConfig()
        ->set('result', $data_config['result']);
    }

    if (!empty($data_config['actions'])) {
      $this->apiResponse
        ->getConfig()
        ->set('actions', $data_config['actions']);
    }
  }
}
