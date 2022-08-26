<?php

namespace Drupal\oneapp_home_billing_gt\Services\v2_0;

use Drupal\oneapp_home_billing\Services\v2_0\ElectronicInvoiceRestLogic;
/**
 * class ElectronicInvoiceGtRestLogic
 */
class ElectronicInvoiceGtRestLogic extends ElectronicInvoiceRestLogic {
  /**
   * Get response data
   *
   * @param string $id
   * @return array
   */
  public function get($id) {
    $response = [];

    $this->loadBillingInfo($id);

    if (empty($this->infoByBillingAccountId) || empty($this->infoByBillingAccountId["subscriberId"]) || empty($this->billingInfo)) {
      $response = $this->billingService::EMPTY_STATE;
      $response["noData"]['message'] = $this->configBlock["messages"]['home']["message"]["label"];
      return $response;
    }

    if (empty($this->billingInfo->eMailElectroniceInvoice) || $this->billingInfo->eMailElectroniceInvoice == "null") {
      return $this->billingService::HIDE_STATE;
    }

    $response['invoice'] = $this->createInvoiceAttributes($this->billingInfo);

    return $response;
  }

  /**
   * Load electronic invoice data from attributes
   *
   * @return array
   */
  public function createInvoiceAttributes($data) {
    $row = [];
    foreach ($this->configBlock['fields']['home'] as $id_field => $field) {
      $row[$id_field] = [
        'label' => $field['label'],
        'show' => ($field['show']) ? TRUE : FALSE,
      ];

      switch ($id_field) {

        case 'userName':
          $row[$id_field]['value'] = isset($data->billingName) ? $data->billingName : " ";
          $row[$id_field]['formattedValue'] = isset($data->billingName) ? $data->billingName : " ";
          break;

        case 'userAddress':
          $row[$id_field]['value'] = $data->billingAddress;
          $row[$id_field]['formattedValue'] = $data->billingAddress;
          break;

        case 'userId':
          $row[$id_field]['value'] = isset($data->billingNit) ? $data->billingNit : " ";
          $row[$id_field]['formattedValue'] = isset($data->billingNit) ? $data->billingNit : " ";
          break;

        case 'electronicInvoice':
          $row[$id_field]['value'] = isset($data->hasElectronicInvoice) && $data->hasElectronicInvoice ? $data->hasElectronicInvoice : '';
          $row[$id_field]['formattedValue'] = isset($data->hasElectronicInvoice) && $data->hasElectronicInvoice ? $data->hasElectronicInvoice : '';
          $row[$id_field]['show'] = isset($data->hasElectronicInvoice) && $data->hasElectronicInvoice === "Activa" ? FALSE : TRUE ;
          break;

        case 'userEmail':
          $email = isset($data->eMailElectroniceInvoice) ? $data->eMailElectroniceInvoice : '';
          if (str_contains($data->eMailElectroniceInvoice, ':')) {
            $email = explode(':', $data->eMailElectroniceInvoice)[1];
          }
          $row[$id_field]['value'] = $email;
          $row[$id_field]['formattedValue'] = $email;
          break;

      }
    }

    $row['description']['label'] = 'Descripción:';
    $row['description']['show'] = TRUE;
    $row['description']['value'] = $this->configBlock['messages']['home']['message_card']['label'];
    $row['description']['formattedValue'] = $this->configBlock['messages']['home']['message_card']['label'];

    return $row;
  }

  /**
   * Update user data
   *
   * @param string $id
   * @param object $queryParams
   * @return array
   */
  public function put($id, $queryParams) {
    if (empty($this->infoByBillingAccountId) || empty($this->infoByBillingAccountId["agreementId"]) || empty($this->billingInfo)) {
      $this->loadBillingInfo($id);
    }

    $response = [];
    $body = [
      "billingDepartmentCode"   => trim($this->billingInfo->billingDepartmentCode),
      "billingZoneCode"         => trim($this->billingInfo->billingZoneCode),
      "billingProvinceCode"     => trim($this->billingInfo->billingProvinceCode),
      "billingNit"              => $queryParams->billingNit,
      "billingName"             => $queryParams->billingName,
      "billingAddress"          => $queryParams->billingAddress,
      "billingDistrictCode"     => trim($this->billingInfo->billingDistrictCode)
    ];

    $response_billing_info_code = $this->billingService->updateBillingInfo($this->infoByBillingAccountId["agreementId"], $body);

    $data = [
      "updateAll" => TRUE,
      "email"     => $queryParams->eMailElectroniceInvoice
    ];

    $response_updated_email_code = $this->billingService->updatePaperlessInvoiceInfo(
      $this->infoByBillingAccountId["agreementId"],
      $this->infoByBillingAccountId["subscriberId"],
      $data
    );

    if($response_billing_info_code != '200' || $response_updated_email_code != '200') {
      return $response['updatedInvoice'] = [
        "status"      => "failed",
        "title"       => "Mensaje fallido",
        "body"        => $this->configBlock["messages"]['home']["failedUpdateMessage"]["label"],
        "icon_class"  => $this->configBlock["messages"]['home']["failedUpdateMessage"]["icon"]
      ];
    }

    return $response['updatedInvoice'] = [
      "status"      => "success",
      "title"       => "Mensaje exitoso",
      "body"        => $this->configBlock["messages"]['home']["successfulUpdateMessage"]["label"],
      "icon_class"  => $this->configBlock["messages"]['home']["successfulUpdateMessage"]["icon"]
    ];
  }

  /**
   * Load data from api dar using id
   *
   * @param string $id
   * @return void
   */
  public function loadBillingInfo($id) {
    $this->infoByBillingAccountId = $this->utils->getInfoTokenByBillingAccountId($id);
    $this->billingInfo = $this->billingService->getBillingInfo($this->infoByBillingAccountId['subscriberId']);
  }

  /**
   * Check if there is a method with the vale of the parameter
   *
   * @param string $name
   * @return bool
   */
  public function hasMakeMethod($name) {
    $method = 'make'.ucfirst($name).'Attribute';
    return method_exists($this, $method);
  }

  /**
   * Invoke the method to use
   *
   * @param string $name
   * @param array $attributes
   * @param array $infoValue
   * @return string
   */
  public function getMakeAttributeMethod($name, $attributes, $infoValue) {
    return $this->{'make' . ucfirst($name) . 'Attribute'}($attributes, $infoValue);
  }

  /**
   * Check if the attribute data is taken from the config block
   *
   * @param string $name
   * @return bool
   */
  public function isDataWillBeLoadedFromConfigurationBlock($name){
    $block_attributes = ['description','home'];
    return in_array($name, $block_attributes);
  }

  /**
   * Make username data
   *
   * @param array $attributes
   * @param object $infoValue
   * @return array
   */
  public function makeUserNameAttribute(array $attributes, $infoValue) {
    return [
        "label"           => $attributes['label'],
        "show"            => (bool) $attributes['show'],
        "value"           => $infoValue->billingName,
        "formattedValue"  => $infoValue->billingName
      ];
  }

  /**
   * Make user address data
   *
   * @param array $attributes
   * @param object $infoValue
   * @return array
   */
  public function makeUserAddressAttribute(array $attributes, $infoValue) {
    return [
      "label"           => $attributes['label'],
      "show"            => (bool) $attributes['show'],
      "value"           => $infoValue->billingAddress,
      "formattedValue"  => $infoValue->billingAddress
    ];
  }

  /**
   * Make user Nit data
   *
   * @param array $attributes
   * @param object $infoValue
   * @return array
   */
  public function makeUserIdAttribute(array $attributes, $infoValue) {
    return [
      "label"           => $attributes['label'],
      "show"            => (bool) $attributes['show'],
      "value"           => $infoValue->billingNit,
      "formattedValue"  => $infoValue->billingNit
    ];
  }

  /**
   * Make Electronic invoice data
   *
   * @param array $attributes
   * @param object $infoValue
   * @return array
   */
  public function makeElectronicInvoiceAttribute(array $attributes, $infoValue) {
    return [
      "label"           => $attributes['label'],
      "show"            => (bool) $attributes['show'],
      "value"           => $infoValue->hasElectronicInvoice,
      "formattedValue"  => $infoValue->hasElectronicInvoice
    ];
  }

  /**
   * Make user email data
   *
   * @param array $attributes
   * @param object $infoValue
   * @return array
   */
  public function makeUserEmailAttribute(array $attributes, $infoValue) {
    return [
      "label"           => $attributes['label'],
      "show"            => (bool) $attributes['show'],
      "value"           => $infoValue->eMailElectroniceInvoice,
      "formattedValue"  => $infoValue->eMailElectroniceInvoice
    ];
  }

  /**
   * Make description data
   *
   * @param array $attributes
   * @param object $infoValue
   * @return array
   */
  public function makeDescriptionAttribute(array $attributes, $infoValue) {
    return [
      "label"           => $attributes['label'],
      "show"            => (bool) $attributes['show'],
      "value"           => $infoValue['description'],
      "formattedValue"  => $infoValue['description']
    ];
  }

  /**
   * Format the reponse with the block configuarion values (In action section).
   *
   * @return array
   *   Return fields as array of objects.
   */
  public function getActions() {
    $actions = $this->configBlock['actions']['home'];
    foreach ($actions as $name => $action) {
      $actions[$name]['show'] = (bool) $action['show'];
    }
    return $actions;
  }
}
