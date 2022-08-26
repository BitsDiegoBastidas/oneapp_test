<?php

namespace Drupal\oneapp_home_services_gt\Services\v2_0;

use Drupal\oneapp_home_services\Services\v2_0\OutageNotificationRestLogic;

/**
 * Class OutageNotificationRestLogicGt.
 */
class OutageNotificationRestLogicGt extends OutageNotificationRestLogic {

  /**
   * Get status resource by documentNumber.
   *
   * @param string $payload
   *   Payload.
   *
   * @param string $id
   *   BillingAccountId.
   *
   * @return boolean $resource_status
   *   Status.
   */
  public function getResourceStatus($payload, $id) {
    $resource_status = NULL;
    if (!empty($id)) {
      $dui = $this->getDui($payload, $id);
      $subscriber_id = $this->homeUtils->getInfoTokenByBillingAccountId($id);
      if (!empty($dui) && isset($subscriber_id['subscriberId'])) {
        $response = $this->outage->getStatusResource('dpi', $dui);
        foreach ($response as $item) {
          foreach ($item->trackingRecord->extensionInfo as $info) {
            if ($info->valueType == 'subscriberId' && $info->value == $subscriber_id['subscriberId'] && isset($item->status)) {
              return $item->status == 'BAD' ? TRUE : FALSE;
            }
          }
        }
      }
    }
    return $resource_status;
  }

  /**
   * Returns response config.
   *
   * @param string $payload
   *   Payload.
   *
   * @param string $id
   *   Subscriber Id.
   *
   * * @return array $message
   *   Message.
   */
  public function responseConfig($payload, $id) {
    $message = [];
    if (!empty($id)) {
      $resource_status = $this->getResourceStatus($payload, $id);
      if (isset($this->configBlock["messages"]) && !is_null($resource_status)) {
        if ($resource_status) {
          $message['title'] = $this->configBlock["messages"]['label_title_error_response'];
          $message['class'] = 'error';
        }
        else {
          $message['title'] = $this->configBlock["messages"]['label_title_success_response'];
          $message['class'] = 'success';
        }
      }
      else {
        $message = $this->getErrorResponse();
      }
    }
    else {
      $message = $this->getErrorResponse();
    }
    return $message;
  }

  /**
   * Returns error response.
   */
  public function getErrorResponse() {
    return [
      'title' => $this->configBlock["message"]["error"]["label"],
      'class' => 'success',
    ];
  }

}
