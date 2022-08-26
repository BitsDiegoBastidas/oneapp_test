<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\RechargeLendingAmountsRestLogic;
use Drupal\Component\Utility\Xss;

/**
 * Class RechargeLendingAmountsGtRestLogic.
 */
class RechargeLendingAmountsGtRestLogic extends RechargeLendingAmountsRestLogic {

  const CHARS_EXCLUDE = '*';

  /**
   * Property to store configurations.
   *
   * @var configBlock
   */
  protected $configBlock;

  /**
   * {@inheritdoc}
   */
  public function setConfig($configBlock) {
    $this->configBlock = $configBlock;
  }

  /**
   * {@inheritdoc}
   */
  public function get($msisdn) {
    $msisdn = $this->msisdnValid($msisdn);
    $config = $this->configBlock;
    $actions = $config['actions'];
    $data = [];
    $loans = NULL;
    try {
      $billingType = $this->getBalance($msisdn)->typeClient;
    }
    catch (HttpException $exception) {
      $billingType = NULL;
    }

    if ($billingType == 'PREPAGO') {
      $loans = ($this->getLoan($msisdn)) ?? [];
      $loan = NULL;
      foreach ($loans as $value) {
        $loan['offerId'] = [
          'label' => $config['fields']['offerId']['formattedValue'],
          'show' => (bool) $config['fields']['offerId']['show'],
          'value' => $value->packageId,
          'formattedValue' => $value->packageId,
        ];

        $loan['offerName'] = [
          'label' => $config['fields']['offerName']['formattedValue'],
          'show' => (bool) $config['fields']['offerName']['show'],
          'value' => $value->name,
          'formattedValue' => $value->name,
        ];

        $loan['description'] = [
          'label' => '',
          'show' => (bool) $config['fields']['description']['show'],
          'value' => $value->description,
          'formattedValue' => $value->description,
        ];

        $tagsValue = [];
        $tagsImageName = [];
        if (isset($value->includedResources) && $value->includedResources != []) {
          foreach ($value->includedResources as $tag) {
            $tagsValue[] = $tag->resourceDescription;
            $tagsImageName[] = $this->findImage($tag->resourceDescription);
          }
        }

        $loan['tags'] = [
          'label' => $config['fields']['tags']['formattedValue'],
          'show' => (bool) $config['fields']['tags']['show'],
          'value' => $tagsValue,
          'imageName' => $tagsImageName,
        ];

        $validityFormattedValue = "";
        $validityNumber = $value->validityNumber;
        $validityType = $value->validityType;

        if ($validityNumber > 0) {
          $formattedValue = [
            $validityNumber,
            $validityType,
          ];
          $validityFormattedValue = trim(implode(" ", $formattedValue));
        }

        $loan['validity'] = [
          'label' => $config['fields']['validity']['formattedValue'],
          'show' => (bool) $config['fields']['validity']['show'],
          'value' => [
            'validity' => $validityNumber,
            'validityUnit' => $validityType,
          ],
          'formattedValue' => $validityFormattedValue,
        ];

        $formattedPrice = $this->utils->formatCurrency($value->price, TRUE, FALSE);
        $loan['price'] = [
          'label' => $config['fields']['price']['formattedValue'],
          'show' => (bool) $config['fields']['price']['show'],
          'value' => [
            'amount' => (float) $value->price,
            'currencyId' => $this->utils->getCurrencyCode(TRUE),
          ],
          'formattedValue' => $formattedPrice,
        ];

        $formattedFee = $this->utils->formatCurrency($value->fee, TRUE, FALSE);
        $loan['fee'] = [
          'label' => $config['fields']['fee']['formattedValue'],
          'show' => (bool) $config['fields']['fee']['show'],
          'value' => [
            'amount' => (float) $value->fee,
            'currencyId' => $this->utils->getCurrencyCode(TRUE),
          ],
          'formattedValue' => $formattedFee,
        ];

        $data['products'][] = $loan;
      }

      // Remove prefix.
      $msisdn = $this->msisdnValid($msisdn, FALSE);
      $data['confirmation'] = $this->getConfirmationSchema($config['confirmation'], $msisdn);
      $config = $this->configResult($actions);

      if (!empty($data['products'])) {

        usort($data['products'], [$this, 'orderByPrice']);

        return [
          'data' => $data,
          'config' => $config,
        ];
      }

    }

    return [
      'data' => $this->emptyState(),
      'config' => [],
    ];
  }

  /**
   * Get empty state.
   */
  public function emptyState() {
    return [
      'noData' => ['value' => 'empty'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLoan($msisdn) {
    $products = NULL;
    $loans = $this->rechargeLendingAmountServices->getLoan($msisdn);

    if (isset($loans->products) && $loans->products != []) {

      $packageId = Xss::filter(\Drupal::request()->get('packageId'));
      $packageLoan = NULL;

      if (!empty($packageId)) {
        foreach ($loans->products as $loan) {
          $id = $loan->packageId;
          if ($packageId == $id) {
            $packageLoan = $loan;
          }
        }
      }

      foreach ($loans->products as $loan) {
        if (isset($loan->type) && $loan->type == 'LOAN') {
          $id = $loan->packageId;
          $products[$id] = $loan;
        }
      }

      if (!empty($packageLoan)) {
        $loanCategory = $packageLoan->category;
        if (is_array($products) || is_object($products)) {
          foreach ($products as $k => $product) {
            $category = $product->category;
            if ($loanCategory != $category) {
              unset($products[$k]);
            }
          }
        }
      }
    }

    return $products;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmationSchema($confirmation, $msisdn) {
    return [
      'confirmationTitle' => [
        'label' => $confirmation['confirmationTitle']['label'],
        'show' => (bool) $confirmation['confirmationTitle']['show'],
      ],
      'message' => [
        'label' => $confirmation['message']['label'],
        'show' => (bool) $confirmation['message']['show'],
      ],
      'orderDetailsTitle' => [
        'label' => $confirmation['orderDetailsTitle']['label'],
        'show' => (bool) $confirmation['orderDetailsTitle']['show'],
      ],
      'targetAccountNumber' => [
        'label' => $confirmation['targetAccountNumber']['label'],
        'value' => $msisdn,
        'formattedValue' => $msisdn,
        'show' => (bool) $confirmation['targetAccountNumber']['show'],
      ],
      'loanAmount' => [
        'label' => $confirmation['loanAmount']['label'],
        'show' => (bool) $confirmation['loanAmount']['show'],
      ],
      'purchaseDetail' => [
        'label' => $confirmation['purchaseDetail']['label'],
        'show' => (bool) $confirmation['purchaseDetail']['show'],
        'formattedValue' => $confirmation['purchaseDetail']['formattedValue'],
      ],
      'paymentMethodsTitle' => [
        'label' => $confirmation['paymentMethodsTitle']['label'],
        'show' => (bool) $confirmation['paymentMethodsTitle']['show'],
      ],
      'paymentMethod' => [
        'label' => $confirmation['paymentMethod']['label'],
        'show' => (bool) $confirmation['paymentMethod']['show'],
        'formattedValue' => $confirmation['paymentMethod']['formattedValue'],
      ],
      'loanBalance' => [
        'label' => $confirmation['loanBalance']['label'],
        'show' => (bool) $confirmation['loanBalance']['show'],
      ],
      'feeAmount' => [
        'label' => $confirmation['feeAmount']['label'],
        'show' => (bool) $confirmation['feeAmount']['show'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance($msisdn) {
    try {
      return $this->rechargeLendingAmountServices->getBalance($msisdn);
    }
    catch (HttpException $exception) {
      throw $exception;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function findImage($resourceDescription) {
    $value = $this->sanitizedResourceDescritionLabel($resourceDescription) . '.svg';
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function sanitizedResourceDescritionLabel($label) {
    $label = str_replace(str_split(self::CHARS_EXCLUDE), '', $label);
    $label = str_replace(' ', '_', strtolower($label));
    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function msisdnValid($msisdn, $flag = TRUE) {
    $mobileUtilsService = \Drupal::service('oneapp.mobile.utils');
    return $mobileUtilsService->modifyMsisdnCountryCode($msisdn, $flag);
  }

  /**
   * {@inheritdoc}
   */
  private static function orderByPrice($a, $b) {
    return ($a['price']['value']['amount'] < $b['price']['value']['amount']) ? -1 : 1;
  }

}
