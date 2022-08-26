<?php


namespace Drupal\oneapp_mobile_payment_gateway_packets_gt\Plugin\Endpoint\v2_0;

use Drupal\oneapp_endpoints\EndpointBase;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\oneapp\ApiResponse\ErrorBase;
use Drupal\oneapp\Exception\GoneHttpException;
use Drupal\oneapp\Exception\NotFoundHttpException;
use Drupal\oneapp\Exception\ConflictHttpException;
use Drupal\oneapp\Exception\BadRequestHttpException;
use Drupal\oneapp\Exception\AccessDeniedHttpException;
use Drupal\oneapp\Exception\UnauthorizedHttpException;
use Drupal\oneapp\Exception\NotAcceptableHttpException;
use Drupal\oneapp\Exception\LengthRequiredHttpException;
use Drupal\oneapp\Exception\TooManyRequestsHttpException;
use Drupal\oneapp\Exception\MethodNotAllowedHttpException;
use Drupal\oneapp\Exception\PreconditionFailedHttpException;
use Drupal\oneapp\Exception\UnprocessableEntityHttpException;
use Drupal\oneapp\Exception\UnsupportedMediaTypeHttpException;
use Drupal\oneapp\Exception\PreconditionRequiredHttpException;
use Drupal\oneapp\Exception\ServiceUnavailableHttpException;
use Drupal\oneapp\Exception\InternalServerErrorHttpException;

/**
 * Provides a 'CurrentEndpoint' entity.
 *
 * @Endpoint(
 * id = "oneapp_mobile_payment_gateway_packets_gt_v2_0_packets_list_endpoint",
 * admin_label = @Translation("Mobile Packets list v2.0"),
 *  defaults = {
 *    "endpoint" = "https://[endpoint:environment_prefix].api.tigo.com/v1/tigo/mobile/[endpoint:country_iso]/vas/subscribers/{id}/products",
 *    "method" = "GET",
 *    "timeout" = 60,
 *  },
 * )
 */
class PacketsListEndpoint extends EndpointBase {

  /**
   * {@inheritdoc}
   */
  public function on4xx(GuzzleException $exception) {
    $error = new ErrorBase();
    $error->getError()->set('developerMessage', $exception->getMessage());
    switch ($exception->getCode()) {
      case '400':
        $error->getError()->set('message', 'El número consultado no tiene paquetes disponibles');
        throw new BadRequestHttpException($error, $exception, $exception->getCode());

      case '401':
        throw new UnauthorizedHttpException($error, $exception, $exception->getCode());

      case '403':
        throw new AccessDeniedHttpException($error, $exception, $exception->getCode());

      case '404':
        throw new NotFoundHttpException($error, $exception, $exception->getCode());

      case '405':
        throw new MethodNotAllowedHttpException($error, $exception, $exception->getCode());

      case '406':
        throw new NotAcceptableHttpException($error, $exception, $exception->getCode());

      case '409':
        throw new ConflictHttpException($error, $exception, $exception->getCode());

      case '410':
        throw new GoneHttpException($error, $exception, $exception->getCode());

      case '411':
        throw new LengthRequiredHttpException($error, $exception, $exception->getCode());

      case '412':
        throw new PreconditionFailedHttpException($error, $exception, $exception->getCode());

      case '415':
        throw new UnsupportedMediaTypeHttpException($error, $exception, $exception->getCode());

      case '422':
        throw new UnprocessableEntityHttpException($error, $exception, $exception->getCode());

      case '428':
        throw new PreconditionRequiredHttpException($error, $exception, $exception->getCode());

      case '429':
        throw new TooManyRequestsHttpException($error, $exception, $exception->getCode());

      default:
        throw new BadRequestHttpException($error, $exception, $exception->getCode());
    }
  }
}
