<?php

namespace Drupal\oneapp_mobile_upselling_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_upselling\Services\v2_0\RechargeOrderDetailsRestLogic;

/**
 * Class RechargeOrderDetailsRestLogic.
 */
class RechargeOrderDetailsGtRestLogic extends RechargeOrderDetailsRestLogic {

  /**
   * Property to store configurations.
   *
   * @var mixed
   */
  protected $configBlock;

  /**
   * Default configuration.
   *
   * @var mixed
   */
  protected $myNumber;

  /**
   * Responds to GET requests.
   *
   * @param string $msisdn
   *   Msisdn.
   * @param int $amount
   *   Amount.
   *
   * @param bool $isSame
   * @return mixed
   *   mixed
   *
   * @throws \ReflectionException
   */
  public function get($msisdn, $amount, $isSame = FALSE, $id = FALSE) {
    $config = $this->configBlock;
    $billingtype = NULL;
    $data = [];
    $this->myNumber = (str_replace(' ', '', $msisdn) === str_replace(' ', '', $id));
    $amount_config = \Drupal::config('oneapp_mobile.config')->get('recharge_amounts_dimensions');
    $max = intval($amount_config['maxCredit']);
    $min = intval($amount_config['min']);
    $data['msisdn'] = [
      'label' => $config['fields']['msisdn']['title'],
      'value' => $id,
      'formattedValue' => $id,
      'show' => (bool) $config['fields']['msisdn']['show'],
    ];
    $data['amount'] = [
      'label' => $config['fields']['amount']['title'],
      'value' => $amount,
      'formattedValue' => $this->utils->formatCurrency($amount, TRUE, FALSE),
      'show' => (bool) $config['fields']['amount']['show'],
    ];
    $data['detail'] = [
      'label' => $this->configBlock['fields']['type']['title'],
      'formattedValue' => $this->configBlock['fields']['type']['label'],
      'show' => (bool) $this->configBlock['fields']['type']['show'],
    ];
    $result = $data;
    try {
      if ($id == $msisdn) {
        $plan = $this->getBalance($id);
        if ($plan->responseMessage === 'Transaccion exitosa') {
          return $this->verifyData($plan->typeClient, $amount, $result, $id, $msisdn);
        }
        else {
          $tigo_invalido = TRUE;
          $errors = $this->error($amount, $min, $max, FALSE, $tigo_invalido);
          $config = $this->configResult($amount, $errors, $min, $max);
          return [
            'data' => $result,
            'config' => $config,
          ];
        }
      }
      else {
        $plan = $this->getBalance($id);
        if ($plan->responseMessage === 'Transaccion exitosa') {
            $data['msisdn']['value'] = $msisdn;
            $data['msisdn']['formattedValue'] = $msisdn;
            $plan = $this->getBalance($msisdn);
            if ($plan->typeClient === "KIT" || $plan->typeClient === "PREPAGO" || $plan->typeClient === "FACTURA FIJA") {
              $errors = $this->error($amount, $min, $max, FALSE, FALSE);
            }
            else {
              $postpaid = TRUE;
              $errors = $this->error($amount, $min, $max, $postpaid, FALSE);
            }
          $result = $data;
          $config = $this->configResult($amount, $errors, $min, $max);
          return [
            'data' => $result,
            'config' => $config,
          ];
        }
        else {
          $tigo_invalido = TRUE;
          $errors = $this->error($amount, $min, $max, FALSE, $tigo_invalido);
          $config = $this->configResult($amount, $errors, $min, $max);
          return [
            'data' => $result,
            'config' => $config,
          ];
        }
      }
    }
    catch (HttpException $exception) {
      $tigo_invalido = TRUE;
      $errors = $this->error($amount, $min, $max, FALSE, $tigo_invalido);
      $config = $this->configResult($amount, $errors, $min, $max);
      return [
        'data' => $result,
        'config' => $config,
      ];
    }
  }

  /**
   * Verify data to response.
   *
   * @param string $billingType
   *   Billing Type of the msisdn.
   * @param int $amount
   *   Amount to recharge.
   * @param array $result
   *   Amount to recharge.
   *
   * @return array
   *   Array formatted success or error.
   */
  public function verifyData($billingType, $amount, $result) {
    $amount_config = \Drupal::config('oneapp_mobile.config')->get('recharge_amounts_dimensions');
    $max = intval($amount_config['maxCredit']);
    $min = intval($amount_config['min']);
    if ($billingType === "KIT" || $billingType === "PREPAGO" || $billingType === "FACTURA FIJA") {
      $errors = $this->error($amount, $min, $max, FALSE, FALSE);
    }
    else {
      $postpaid = TRUE;
      $errors = $this->error($amount, $min, $max, $postpaid, FALSE);
    }
    $config = $this->configResult($amount, $errors, $min, $max);
    return [
      'data' => $result,
      'config' => $config,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function configResult($amount, $errors, $min, $max) {
    $actions = [];
    $rows = [];
    $index = 0;
    $config = $this->configBlock['buttons'];

    foreach ($config as $id => $field) {
      if ($id == 'changeMsisdn') {
        $actions[$id] = [
          'label' => $field['label'],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => (bool) $field['show'],
        ];

        if ($errors['error'] === TRUE) {
          $rows = $errors['rows'];
          break;
        }
      }

      if ($id == 'creditCard') {
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'description' => [
            'label' => $field['description'],
            'show' => !empty($field['description']),
          ],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => FALSE,
        ];
        $promotionalTextCreditCard = $this->configBlock['fields']['promotionalTextCreditCard'];
        $row[$id]['description'] = [
          'label' => $promotionalTextCreditCard['label'],
          'show' => (bool) $field['show'],
        ];
        $amount_config = \Drupal::config('oneapp_mobile.config')->get('recharge_amounts_dimensions');
        $minCredit = intval($amount_config['max']);
        $maxCredit = intval($amount_config['maxCredit']);
        if ($amount >= $minCredit && $amount <= $maxCredit && $errors['error'] != TRUE) {
          $row[$id]['show'] = (bool) $field['show'];
        }
      }

      if ($id == 'tigoMoney') {
        $row[$id] = [
          'paymentMethodName' => $field['title'],
          'label' => $field['label'],
          'description' => [
            'label' => $field['description'],
            'show' => !empty($field['description']),
          ],
          'url' => $field['url'],
          'type' => $field['type'],
          'show' => (bool) $field['show'],
        ];
        $row[$id]['show'] = $this->myNumber ? $row[$id]['show'] : FALSE;
      }

      if (isset($row)) {
        $rows[$index] = $row;
      }
    }
    $index++;
    $actions['paymentMethods'] = $rows;
    $paymentMethodsTitle = $this->configBlock['fields']['titlePaymentMethods'];
    $dataconfig = [];
    $dataconfig['titleDetails'] = [
      'label' => $this->configBlock['fields']['title']['title'],
      'show' => (bool) $this->configBlock['fields']['msisdn']['show'],
    ];
    $dataconfig['paymentMethods'] = [
      'label' => $paymentMethodsTitle['title'],
      'show' => (bool) $paymentMethodsTitle['show'],
    ];
    return [
      'actions' => $actions,
      'dataconfig' => $dataconfig,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function error($amount, $min, $max, $postpaid = FALSE, $tigo_invalido = FALSE) {
    $error = FALSE;
    $rows = [];
    $messages = $this->configBlock['messages'];
    if ($tigo_invalido === TRUE) {
      $row = [
        'value' => $messages['number_error']['label'],
        'show' => (bool) $messages['monto_max_error']['show'],
      ];
      $error = TRUE;
      $rows = $row;
    }
    elseif ($postpaid === TRUE) {
      $row = [
        'value' => $messages['recharge_error']['label'],
        'show' => (bool) $messages['monto_max_error']['show'],
      ];
      $error = TRUE;
      $rows = $row;
    }
    elseif ($amount < $min) {
      $value = $this->utils->formatCurrency($min, TRUE, FALSE);
      $row = [
        'value' => str_replace('@amount', $value, $messages['monto_error']['label']),
        'show' => (bool) $messages['monto_error']['show'],
      ];
      $error = TRUE;
      $rows = $row;
    }
    elseif ($amount > $max) {
      $value = $this->utils->formatCurrency($max, TRUE, FALSE);
      $row = [
        'value' => str_replace('@amount', $value, $messages['monto_max_error']['label']),
        'show' => (bool) $messages['monto_max_error']['show'],
      ];
      $error = TRUE;
      $rows = $row;
    }
    return [
      'rows' => $rows,
      'error' => $error,
    ];

  }

  /**
   * Implements getCurrentPlan.
   *
   * @param string $msisdn
   *   Msisdn value.
   *
   * @return mixed
   *   Msisdn value.
   *
   * @throws \ReflectionException
   */
  private function getBalance($msisdn) {
    $config = $this->configBlock;
    $message_error = $config['messages']['number_error']['label'];
    try {
      return $this->manager
        ->load('oneapp_mobile_upselling_v2_0_change_msisdn_endpoint')
        ->setHeaders([])
        ->setQuery([])
        ->setParams(['msisdn' => $msisdn])
        ->sendRequest();
    }
    catch (HttpException $exception) {
      $message = $message_error;

      $reflectedObject = new \ReflectionClass(get_class($exception));
      $property = $reflectedObject->getProperty('message');
      $property->setAccessible(TRUE);
      $property->setValue($exception, $message);
      $property->setAccessible(FALSE);

      throw $exception;
    }
  }

}
