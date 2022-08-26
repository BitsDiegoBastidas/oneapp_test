<?php

namespace Drupal\oneapp_mobile_plans_gt\Plugin\rest\resource\v2_0;

use Drupal\rest\ResourceResponse;
use Drupal\oneapp_rest\Plugin\ResourceBase;
use Drupal\oneapp\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   api_response_version = "v2_0",
 *   block_id = "oneapp_mobile_plans_gt_v2_0_icloud_promo_block",
 *   id = "oneapp_mobile_plans_gt_v2_0_icloud_promo_rest_resource",
 *   label = @Translation("ONEAPP Mobile iCloud Promo Rest Resource v2.0"),
 *   uri_paths = {
 *     "canonical" = "/api/v2.0/mobile/plans/{idType}/{id}/icloud/promo"
 *   }
 * )
 */
class IcloudPromoRestResource extends ResourceBase {

  /**
   * @var \Drupal\oneapp_mobile_plans_gt\Services\v2_0\IcloudPromoRestLogic
   */
  protected $restLogicService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->restLogicService = $container->get('oneapp_mobile_plans_gt.v2_0.icloud_promo_rest_logic');
    return $instance;
  }

  /**
   * @param string $idType
   * @param string $id
   * @return ResourceResponse
   */
  public function get($idType, $id) {

    $this->init();

    try {
      $this->restLogicService->setConfig($this->configBlock);
      $data = $this->restLogicService->getData($idType, $id);
    }
    catch (\Exception $e) {
      if (!empty($this->configBlock['message']['error']['show'])) {
        $message = $this->configBlock['message']['error']['label'];
      }
      $this->apiErrorResponse->getError()->set('message', $message ?? $e->getMessage());
      throw new HttpException($e->getCode(), $this->apiErrorResponse, $e);
    }

    // Set data in response API
    $this->apiResponse->getData()->setAll($data);
    // Set config in response API
    $this->responseConfig($data);

    // Build response with data.
    $response = new ResourceResponse($this->apiResponse);
    $response->addCacheableDependency($this->cacheMetadata);
    return $response;
  }

  public function responseConfig($data) {
    $pre_configs = $this->apiResponse->getConfig()->getAll();
    $new_configs = $this->restLogicService->getDataConfig($data);
    $config = array_merge($pre_configs, $new_configs);
    $this->apiResponse->getConfig()->setAll($config);
  }
}
