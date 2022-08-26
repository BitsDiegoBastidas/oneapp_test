<?php

namespace Drupal\oneapp_home_services_gt\Plugin\rest\resource\v2_0;

use Drupal\rest\ModifiedResourceResponse;
use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\oneapp\ApiResponse\ErrorBase;
use Symfony\Component\HttpFoundation\Cookie;
use Drupal\oneapp_home_services\Plugin\rest\resource\v2_0\SubscriptionRestResource;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "oneapp_home_services_v2_0_products_rest_resource",
 *   label = @Translation("ONEAPP home services products rest resource v2_0"),
 *   uri_paths = {
 *     "canonical" = "/api/v2.0/home/services/{idType}/{id}/products"
 *   },
 *   block_id = "oneapp_home_services_v2_0_products_block",
 *   api_response_version = "v2_0"
 * )
 */
class SubscriptionGtRestResource extends SubscriptionRestResource {

  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->init();
    try {
      $services = \Drupal::service('oneapp_home_services.v2_0.services_rest_logic');
      $services->setConfigBlock($this->configBlock);
      $data = $services->getAllProducts($this->accountId);
      if (isset($data->code)) {
        $error = new ErrorBase();
        $error->getError()->set('message', 'Error calling products service.');
        $error->getError()->set('code', $data->code);
        throw new NotFoundHttpException($error);
      }
    }
    catch (\Exception $e) {
      $body = json_decode($e->getMessage());
      $this->apiErrorResponse->getError()->set('message', isset($body->message) ? $body->message : 'Not Found');
      throw new NotFoundHttpException($this->apiErrorResponse, $e);
    }

    // Obtiene el subtitle config.
    $subtitle = $data['config']['subtitle'] ?? '';
    unset($data['config']['subtitle']);

    $response_data = [];
    $response_data = $services->formatPortfolio($data, $this->configBlock);

    $this->responseConfig();
    $this->apiResponse->getConfig()->set('subtitle', $subtitle);

    // Seteamos los datos.
    $this->apiResponse->getData()->setAll($response_data);

    // Seteamos los datos en el response.
    $response = new ModifiedResourceResponse($this->apiResponse);
    $cookie = new Cookie("SESSION", time());
    $response->headers->setCookie($cookie);
    return $response;
  }

}
