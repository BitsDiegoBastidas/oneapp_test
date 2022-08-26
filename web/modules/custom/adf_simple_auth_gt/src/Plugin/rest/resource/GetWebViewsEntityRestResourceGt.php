<?php

namespace Drupal\adf_simple_auth_gt\Plugin\rest\resource;

use Drupal\adf_simple_auth\Plugin\rest\resource\GetWebViewsEntityRestResource;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "get_web_views_entity_rest_resource",
 *   label = @Translation("Get web views entity rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/entity/{businessUnit}/{accountId}/webviews"
 *   }
 * )
 */
class GetWebViewsEntityRestResourceGt extends GetWebViewsEntityRestResource {

  /**
   * {@inheritdoc}
   */
  public function get($businessUnit, $accountId, Request $request) {
    try {
      $adf_common = \Drupal::service('adf_simple_auth.common');
      $anon_id = $request->query->get('anonId') ? $request->query->get('anonId') : '';
      $base_url = \Drupal::config('adf_simple_auth.settings')->get('baseURL');
      $app_id = \Drupal::config('adf_simple_auth.settings')->get('appID');
      $pre_shared_secret = \Drupal::config('adf_simple_auth.settings')->get('preSharedSecret');
      $expires_in = \Drupal::config('adf_simple_auth.settings')->get('expiresIn');

      $expires_in = ($expires_in) ? $expires_in : '+1 day';

      $date_now = new DrupalDateTime();
      $exp = $date_now->modify($expires_in)->format('c');

      $jwt = \Drupal::service('adf_simple_auth.jwt');
      $payload = $jwt->getTokenPayload($request);

      $email = isset($payload->email) ? $payload->email : NULL;
      $sub = $payload->{'custom:UUID'};

      // GT obtener primarySubscriberId.
      if ($businessUnit == 'home') {
        $account_services = \Drupal::service('oneapp_convergent_accounts.v2_0.accounts');
        $info = $account_services->getAccountListByTokenPayload($payload);
        foreach ($info["accountList"] as $account) {
          if ($account["businessUnit"] == $businessUnit && $account["billingAccountId"] == $accountId) {
            $accountId = $account["displayId"];
            break;
          }
        }
      }

      $formatted_string = $businessUnit . $accountId . $exp . $app_id;
      if ($email) {
        $formatted_string .= $email;
      }
      $formatted_string .= $sub . $pre_shared_secret;
      $hash = hash('sha256', $formatted_string);

      $query = \Drupal::entityQuery('webviews_entity')->sort('id', 'ASC')->sort('weight', 'ASC');
      $nids = $query->execute();
      $endpoints_entities = \Drupal::entityTypeManager()->getStorage('webviews_entity')->loadMultiple($nids);
      $endpoints = [];
      foreach ($endpoints_entities as $entity) {
        $plan_type = $entity->get('plan_type');
        $planes = [];
        foreach ($plan_type as $key => $id) {
          if ($id) {
            $planes[] = $id;
          }
        }
        $endpoint = [];
        $endpoint['title'] = trim($entity->get('webview_title'));
        $endpoint['description'] = (trim($entity->get('description'))) ? trim($entity->get('description')) : NULL;
        $endpoint['button'] = trim($entity->get('button_title')) ? trim($entity->get('button_title')) : NULL;
        $endpoint['redirect'] = $redirect = trim($entity->get('redirect')) ? trim($entity->get('redirect')) : NULL;
        $endpoint['full_url_details'] = ($adf_common->isDomainUrl($endpoint['redirect'])) ? $endpoint['redirect'] : $adf_common->getFullUrl($businessUnit, $accountId, $endpoint['redirect']);
        $query = ['businessUnit' => $businessUnit, 'accountID' => $accountId, 'hash' => $hash, 'appID' => $app_id, 'exp' => $exp, 'sub' => $sub, 'anonId' => $anon_id];
        if ($email) {
          $query['email'] = $email;
        }
        $query['redirect'] = $redirect;

        $endpoint['url'] = ($adf_common->isDomainUrl($endpoint['redirect'])) ? $endpoint['redirect'] : $adf_common->getFullUrl($businessUnit, $accountId, $endpoint['redirect']);
        $endpoint['expirationDate'] = $exp;
        $endpoint['icon'] = trim($entity->get('icon_file_name')) ? trim($entity->get('icon_file_name')) : NULL;
        $endpoint['image'] = trim($entity->get('image')) ? trim($entity->get('image')) : NULL;
        $endpoint['enable'] = $entity->get('enable_web');
        $endpoint['plan_type'] = !empty($planes) ? $planes : NULL;
        $endpoint['accountType'] = $entity->get('account_type');
        $endpoint['section'] = $entity->get('app_selection');
        array_push($endpoints, $endpoint);
        unset($endpoint);
      }
      $final_response = [
        "weblinks" => $endpoints,
      ];
      $response = new ResourceResponse($final_response);
      $cache_meta_data = new CacheableMetadata();
      $cache_meta_data->setCacheMaxAge(0);
      $response->addCacheableDependency($cache_meta_data);
      return $response;
    }
    catch (\Exception $e) {
      $error = [
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage(),
      ];
      return new ResourceResponse($error, 500);
    }
  }

}
