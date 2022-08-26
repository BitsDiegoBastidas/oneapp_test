<?php

namespace Drupal\oneapp_mobile_premium_gt\Services\v2_0;

use Drupal\oneapp_mobile_premium\Services\v2_0\PremiumDetailRestLogic;
use Drupal\file\Entity\File;

/**
 * Class PremiumDetailRestLogicGt.
 */
class PremiumDetailRestLogicGt extends PremiumDetailRestLogic {

  /**
   * {@inheritdoc}
   */
  public function get($id, $product_id) {

    $available_offers = $this->service->getAvailableOffers($id);
    $active_offers = $this->service->getActiveOffers($id);
    $available_offers = $this->service->filterAvailableOffers($active_offers, $available_offers);
    
    $product = $this->service->getOneProduct($product_id);
    $drupal_service = $this->getDrupalService($product);
    $product_data = $drupal_service->getData($id, $product, $available_offers, $active_offers);

    $data = [];

    if (!is_null($product_data)) {
      $base_url = $this->utils->getUrlImages();
      if ($product->get('logo1')->target_id) {
        $logo1 = rawurlencode(File::load($product->get('logo1')->target_id)->get('filename')->value);
        $logo1_html = '<div class="logo" style="background: url(' . $base_url . $logo1 . ')"></div>';
        $product_data['priceHtml'] = str_replace('@logo1', $logo1_html, $product_data['priceHtml']);
      }
      if ($product->get('logo2')->target_id) {
        $logo2 = rawurlencode(File::load($product->get('logo2')->target_id)->get('filename')->value);
        $logo2_html = '<div class="logo" style="background: url(' . $base_url . $logo2 . ')"></div>';
        $product_data['priceHtml'] = str_replace('@logo2', $logo2_html, $product_data['priceHtml']);
      }

      $data['productId'] = $this->formatField('productId', $product->get('id_service')->value);
      $data['productName'] = $this->formatField('productName', $product->get('name_service')->value);
      $data['price'] = $this->formatField('price', [
        'value' => $product_data['price'],
        'formattedValue' => $product_data['priceHtml'],
      ]);

      $data['status'] = $this->formatField('status', $product_data['isActive']);

      if ($product_data['isActive']) {
        $banner_url = rawurlencode(File::load($product->get('banner_card_detail_active')->target_id)->get('filename')->value);

        if (isset($product_data['pendingUnsubscription']) && $product_data['pendingUnsubscription']) {
          $description = str_replace(
            ['@date_pending', '@date_requested'],
            [$product_data['unsubscriptionDate'], $product_data['unsubscriptionDateRequested']],
            $product->get('text_card_detail_inactive')->value
          );
          $data['pendingUnsubscription'] = $this->formatField('pendingUnsubscription', TRUE);
        }
        else {
          $description = $product_data['textCardMoreDetail'];
          $data['pendingUnsubscription'] = $this->formatField('pendingUnsubscription', FALSE);

          // Configura el modal de desactivación.
          $this->setModalDeactivate($product, $base_url);
        }
        if ($product_id == "Office365") {
          $data['confirmationId'] = $this->formatField('confirmationId', $product_data["subscriptionCode"]);
        }

        // Desactivación en proceso.
        $this->hasTicket($this->utils::PS_TYPE_ACTION_UNSUBSCRIBE, $product, $id, $data);
      }
      else {
        $banner_url = rawurlencode(File::load($product->get('banner_card_detail')->target_id)->get('filename')->value);
        $description = $product_data['textCardDetail'];

        $actions = $this->configBlock['actions']['terms_modal']['buttons'];
        foreach ($actions as &$action) {
          $action['show'] = (isset($action['show']) && $action["show"]) ? TRUE : FALSE;
        }

        if ($product->get('type_service')->value == $this->utils::PS_TYPE_SERVICE_AMZ) {
          $confirmation_id = $drupal_service->getDocomoSubscribe($id, $product, $available_offers, $active_offers);
          $data['confirmationId'] = $this->formatField('confirmationId', $confirmation_id);
        }

        if ($product->get('type_service')->value == $this->utils::PS_TYPE_SERVICE_SYMPHONICA_EXTERNAL) {
          $data['confirmationId'] = $this->formatField('confirmationId', $id);
        }

        $this->modals['terms'] = [
          'title' => t('Términos y condiciones'),
          'body' => $product->get('terms_tigo')->value,
          'show' => TRUE,
          'actions' => $actions,
        ];

        $this->modals['termsProduct'] = [
          'title' => t('Términos y condiciones'),
          'body' => $product->get('terms_service')->value,
          'show' => TRUE,
          'actions' => $actions,
        ];

        // Activación en proceso.
        $this->hasTicket($this->utils::PS_TYPE_ACTION_SUBSCRIBE, $product, $id, $data);
      }

      $data['activate_label'] = $product->get('activate_label')->value;

      $data['banner'] = $this->formatField('banner', $banner_url);
      $data['description'] = $this->getDescription($description, $product_data);
      $data['isAssociationJourney'] = ['value' => $drupal_service->isAssociationJourney()];
    }
    else {
      $data = ['noData' => ['value' => 'empty']];
    }

    return $data;
  }

  /**
   * Subscribe to amazon.
   */
  public function subscribe($account_id, $product_id, $payload) {

    $available_offers = $this->service->getAvailableOffers($account_id);
    $active_offers = $this->service->getActiveOffers($account_id);

    $product = $this->service->getOneProduct($product_id);
    $drupal_service = $this->getDrupalService($product);

    $product_data = $drupal_service->subscribe($account_id, $product, $payload, $available_offers, $active_offers);

    if (isset($product_data['redirect'])) {
      $data['premiumDetails']['redirect'] = [
        'label' => t('Redirección'),
        'value' => $product_data['redirect'],
        'formattedValue' => $product_data['redirect'],
        'show' => FALSE,
      ];
    }
    else {
      $date = new \DateTime('now');
      $body = str_replace(['@date'], [$date->format('d/m/Y')], $product_data['textCardConfirmActivation']);

      $data['premiumDetails']['details'] = [
        'label' => t('Detalles'),
        'value' => '',
        'formattedValue' => $body,
        'show' => TRUE,
      ];
    }

    // Ajustes validacion con office.
    $is_send_license = strtolower($product_id) == 'office365' && !empty($payload["confirmationId"]);

    if (!empty($product_data)) {
      $data['result'] = $this->getResultSuccess($this->utils::PS_TYPE_ACTION_SUBSCRIBE, $is_send_license);
      // Activacion en progreso.
      $this->hasTicket($this->utils::PS_TYPE_ACTION_SUBSCRIBE, $product, $account_id, $data);
      $data['config']['partner'] = $product->get('name_service')->value;
      $data['config']['product'] = $product->get('id_service')->value;
    }
    else {
      if ($is_send_license) {
        $label = $this->configBlock['messages']['license_failed']['title'];
        $body = $this->configBlock['messages']['license_failed']['body'];
      }
      else {
        $label = $this->configBlock['messages']['fail']['title'];
        $body = $this->configBlock['messages']['fail']['body'];
      }
      // Resultado fallido.
      $data['result'] = [
        'label' => $label,
        'formattedValue' => $body,
        'value' => FALSE,
        'show' => TRUE,
      ];
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  protected function getResultSuccess($action, $is_send_license = FALSE) {
    if ($this->ticketId) {
      $label = $this->configBlock['messages']['pending_' . $action]['title'];
      $formatted_value = $this->configBlock['messages']['pending_' . $action]['body'];
    }
    elseif ($action == $this->utils::PS_TYPE_ACTION_SUBSCRIBE && $is_send_license) {
      $label = $this->configBlock['messages']['license_success']['title'];
      $formatted_value = $this->configBlock['messages']['license_success']['body'];
    }
    elseif ($action == $this->utils::PS_TYPE_ACTION_SUBSCRIBE && !$is_send_license) {
      $label = $this->configBlock['messages']['success']['title'];
      $formatted_value = $this->configBlock['messages']['success']['body'];
    }
    elseif ($action == $this->utils::PS_TYPE_ACTION_UNSUBSCRIBE) {
      $label = $this->configBlock['messages']['success_active']['title'];
      $formatted_value = $this->configBlock['messages']['success_active']['body'];
    }
    return [
      'label' => $label ?? '',
      'formattedValue' => $formatted_value ?? '',
      'value' => TRUE,
      'show' => TRUE,
    ];
  }

}
