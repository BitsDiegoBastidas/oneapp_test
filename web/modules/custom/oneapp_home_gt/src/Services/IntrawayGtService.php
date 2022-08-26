<?php

namespace Drupal\oneapp_home_gt\Services;

use Drupal\oneapp_home\Services\IntrawayService;
use Firebase\JWT\JWT;

class IntrawayGtService extends IntrawayService {

  /**
   * Get intraway url to show iframe by billingAccountId.
   *
   * @param string $id
   *   Billing account Id.
   * @param string $idType
   *   Id type.
   * @param string $request
   *   Reuqest info.
   * @param string $blockConfig
   *   Block configuration.
   *
   * @return array
   *   return array with intraway url.
   */
  public function retrieveIntrawayUrl($id, $id_type, $request, $block_config = []) {
    if (\Drupal::hasService('oneapp_home_services.v2_0.services_rest_logic')) {
      $supplementary_services = [];
      $data = $this->homeUtils->getInfoTokenByPrimarySubscriberId($id);
      if (isset($data["billingAccount"])) {
        $logic = \Drupal::service('oneapp_home_services.v2_0.services_rest_logic');
        $supplementary_services = $logic->returnSupplementaryServices($data["billingAccount"]);
      }
      if (isset($supplementary_services->productFamily) && $supplementary_services->productFamily == 'Plume') {
        return [];
      }
    }
    try {
      $jwt_service = \Drupal::service('adf_simple_auth.jwt');
      $token_info = $jwt_service->getTokenPayload($request);
    }
    catch (\Exception $e) {
      throw new \Exception('Error getting token info.');
    }

    $contract_number = $this->getContractBySubscriberId($id);

    if ($request->query->get('anonId')) {
      $anonymous_id = $request->query->get('anonId');
    }
    else {
      $anonymous_id = $token_info->{'custom:UUID'};
    }


    if (isset($block_config) && !empty($block_config)) {
      $payload = [
        'iss' => $block_config['fields']["iss"],
        'iat' => time(), // {timestamp}
        'exp' => time() + 60 * $block_config['fields']['expiration_time'], // {timestamp} expiration time 30 minutes
        'aud' => $block_config['fields']['aud'],
        'sub' => $contract_number,
        'org_id' => $block_config['fields']['org_id'],
        'org_mapped_id' => $block_config['fields']['org_mapped_id'],
        'channel_id' => $block_config['fields']['channel_id'],
        'email' => $token_info->email ?? '',
        'first_name' => $token_info->fName ?? '',
        'last_name' => $token_info->gName ?? '',
        'customer_id' => $contract_number,
        'ip_addr' => $request->getClientIp(),
        'seg_user_id'=> $token_info->{'custom:UUID'},
        'seg_anonymous_id' => $anonymous_id,
        'seg_api_key'=> $block_config['fields']['seg_source'],
      ];
      $jwt_token = JWT::encode($payload,
        $block_config['fields']['secret_key'] . $contract_number, 'HS256'); //params(payload, yourSecret, alghorithm)
      $query_params = [
        $block_config['fields']['param_query_token_key'] => $jwt_token,
        'seg_source' => $block_config['fields']['seg_source'],
      ];
      $url_iframe = $block_config['fields']['url'] . '?' . http_build_query($query_params);
      return [
        'actions' => [
          'iframe' => [
            'show' => (bool) $block_config['actions']['iframe']['show'],
            'label' => $block_config['actions']['iframe']['label'],
            'type' => 'iframe url',
            'url' => $url_iframe,
          ],
        ],
      ];
    }
    else {
      throw new \Exception('Bad intraway configuration.');
    }
    return [];
  }

  public  function getContractBySubscriberId($subscriber_id) {
    $manager = \Drupal::service('oneapp_endpoint.manager');
    try {
      $response =  $manager
        ->load('oneapp_mobile_upselling_v1_0_details_by_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $subscriber_id])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      throw new \Exception($exception->getMessage(), $exception->getCode());
    }

    if (isset($response->Envelope->Body->Subscriber->contractNumber) && !empty($response->Envelope->Body->Subscriber->contractNumber)) {
      return $response->Envelope->Body->Subscriber->contractNumber;
    }

    return $subscriber_id;

  }

  /**
   * Redefining function hide of parent.
   *
   * @param array $blockConfig
   *   All data.
   * @param string $id
   *   Id.
   *
   * @return bool
   *   Response if id es available view intraway service.
   */
  public function hide(array $block_config, $id) {
    $home_services = \Drupal::service('oneapp_home_services.v2_0.services_rest_logic');
    return !$home_services->isAvailableForIntrawayService($id);
  }


}
