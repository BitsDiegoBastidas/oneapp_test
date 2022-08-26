<?php

namespace Drupal\oneapp_convergent_payment_gateway_autopayments_gt\Services\v2_0;

use Drupal\oneapp\ApiResponse\ErrorBase;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Drupal\file\Entity\File;
use Drupal\oneapp_convergent_payment_gateway_autopayments\Services\v2_0\DetailsInvoiceEnrollmentRestLogic;
/**
 * Class DetailsInvoiceRestLogic.
 */
class DetailsInvoiceEnrollmentGtRestLogic extends DetailsInvoiceEnrollmentRestLogic{

  /**
   * mock up the api getEnrollmoent response.
   *
   * @param $businessUnit
   * @param $idType
   * @param $id
   *
   * @return array
   */

  public function validateMigrateMailFormat($mail, $config) {
    $configEmail = $config['mail'];
    $newConfigMail = explode("@", $configEmail);
    $prefixMailAnexo = isset($config['annex_by_prefix_mail']) ? $config['annex_by_prefix_mail'] : "";
    $newEmail = [];
    if ($prefixMailAnexo) {
      $newEmail = explode($prefixMailAnexo, $mail);
    }
    if (isset($newEmail[1])) {
      $numberPart = explode("@", $newEmail[1]);
      if (($newConfigMail[0] == $newEmail[0]) && is_numeric($numberPart[0])) {
        return [
          'access' => FALSE,
          'number' => $numberPart[0],
        ];
      }
      else {
        return ['access' => TRUE];
      }
    }
    else {
      if ($mail == $configEmail) {

        return ['access' => FALSE];
      }
      else {

        return ['access' => TRUE];
      }
    }
  }

  public function validateMailEnrollment($mail) {
    $configEnrollments = \Drupal::config('oneapp_convergent_payment_gateway.config')->getRawData();
    $configEmail = isset($configEnrollments['user_default_payments_enrollment'])?$configEnrollments['user_default_payments_enrollment']: [];
    $emailConfig = isset($configEmail['email_default']['mail']) ? $configEmail['email_default']['mail']: "";
    $isAccessAccount = trim($this->authTokenService->getMail()) == trim($emailConfig) ? FALSE: TRUE;
    if ($isAccessAccount) {
      $access = $this->validateMigrateMailFormat(trim($mail), $configEmail['recurring_payments_access']);
      $isAccessAccount = $access['access'];
    }
    return $isAccessAccount;
  }

  /**
   * mock up the api getEnrollmoent response.
   *
   * @param $businessUnit
   * @param $idType
   * @param $id
   *
   * @return array
   */
  public function getFormattedEnrollment($businessUnit, $idType, $id, $accountNumber = NULL) {
    $paymentGateway = \Drupal::service('oneapp_convergent_payment_gateway.recurring_payments.v2_0.enrollments');
    $enrollments = [];
    if (!$this->config['he_otp']['enable'] && $paymentGateway->isHe()) {
      return [
        'enrollments' => [
          'noData' => [
            'value' => 'empty',
          ],
        ],
        'message_he' => $this->config['he_otp']['message'],
        'actions' => [
          'login' => [
            'label' => $this->config['he_otp']['button'],
            'show' => TRUE,
            'type' => "button",
          ],
        ],
      ];
    }
    try {
      $response = $paymentGateway->getEnrollmentsByParams($businessUnit, $idType, $id, $accountNumber);
    }
    catch (\Exception $e) {
      $billingService = \Drupal::service('oneapp_mobile_billing.billing_service');
      if ($billingService->isConvergent($id)) {
        return [
          'enrollments' => [
            'noData' => [
              'value' => 'hide',
            ],
          ],
          'actions' => [],
        ];
      }
      if ($e->getCode() == '404' || $e->getCode() == '500') {
        $this->config["actionsMyEnrollment"]["addEnrollment"]["show"] = (isset($this->config["actionsMyEnrollment"]["addEnrollment"]["show"]) &&
          $this->config["actionsMyEnrollment"]["addEnrollment"]["show"]) ?
          TRUE : FALSE;
        if (!$paymentGateway->isHe()) {
          unset($this->config["actionsMyEnrollment"]["addEnrollment"]["he"]);
          return [
            'enrollments' => [
              'noData' => [
                'value' => 'empty',
              ],
            ],
            'messages_without_enrollment' => $this->config['getEnrollment']['without_data'],
            'actions' => [
              'addEnrollment' => $this->config["actionsMyEnrollment"]["addEnrollment"],
            ],
          ];
        }
        else {
          return [
            'enrollments' => [
              'noData' => [
                'value' => 'empty',
              ],
            ],
            'message_he' => $this->config['he_otp']['message'],
            'actions' => [
              'login' => [
                'label' => $this->config['he_otp']['button'],
                'show' => TRUE,
                'type' => "button",
              ],
            ],
          ];
        }
      }
      else {
        throw $e;
      }
    }
    $isOtherUserEnrollment = $this->getIsOtherUserEnrollment($response, $paymentGateway);
    $emailEnrollment = isset($response->body->trace->email)? $response->body->trace->email: "";
    if ($isOtherUserEnrollment) {
      $isOtherUserEnrollment = $this->validateMailEnrollment($emailEnrollment);
    }
    $this->config["actionsMyEnrollment"]["addEnrollment"]["show"] = (isset($this->config["actionsMyEnrollment"]["addEnrollment"]["show"]) &&
      $this->config["actionsMyEnrollment"]["addEnrollment"]["show"]) ? TRUE : FALSE;
    if (property_exists($response, 'responseData')) {
      $responseData = [
        'enrollments' => [
          'noData' => [
            'value' => 'empty',
          ],
        ],
        'messages_without_enrollment' => $this->config['getEnrollment']['without_data'],
        'actions' => [
          'addEnrollment' => $this->config["actionsMyEnrollment"]["addEnrollment"],
        ],
      ];
      unset($responseData["actions"]["addEnrollment"]["he"]);
      return $responseData;
    }
    if (isset($response->body->enrollmentDetails->useRegionalPaymentGateway)) {
      if (($response->body->enrollmentDetails->useRegionalPaymentGateway == FALSE)) {
        if ($this->config["localEnrollments"]["show"]) {
          return [
            'enrollments' => [
              'noData' => [
                'value' => 'empty',
              ],
            ],
            'messages_without_enrollment' => str_replace("@entity", $response->body->enrollmentDetails->processorName, $this->config["localEnrollments"]["message"]),
            'actions' => [],
          ];
        }
        else {
          return [
            'enrollments' => [
              'noData' => [
                'value' => 'hide',
              ],
            ],
            'actions' => [],
          ];
        }

      }
    }

    if (isset($response->body->paymentToken)) {
      $paymentToken = $response->body->paymentToken;
    }
    if (isset($response->body->trace->customerName)) {
      $traceInfo = $response->body->trace;
    }
    if (isset($response) && isset($response->httpStatusCode) && $response->httpStatusCode == 200) {
      if (isset($response->body->paymentToken)) {
        $paymentToken = $response->body->paymentToken;
      }
      if (isset($response->body->trace->customerName)) {
        $traceInfo = $response->body->trace;
      }
      uasort($this->config['enrollments']['fields'], ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
      foreach ($this->config['enrollments']['fields'] as $key => $field) {
        $enrollment = [];
        $enrollment[$key] = [
          'label' => $field['label'],
          'show' => ($field['show']) ? TRUE : FALSE,
        ];
        switch ($key) {
          case 'enrollmentId':
            $enrollment[$key]['value'] = isset($response->body->id) ? $response->body->id : '';
            $enrollment[$key]['formattedValue'] = isset($response->body->id) ? (string) $response->body->id : '';
            break;

          case 'cardType':
            $enrollment[$key]['value'] = isset($paymentToken->cardType) ? $paymentToken->cardType : '';
            $enrollment[$key]['formattedValue'] = isset($paymentToken->cardType) ? (string) $paymentToken->cardType : '';
            break;

          case 'expirationDate':
            if ($paymentToken->expirationYear && $paymentToken->expirationMonth) {
              $month = isset($paymentToken->expirationMonth) ? str_pad($paymentToken->expirationMonth, 2, '0', STR_PAD_LEFT) : '';
              $year = isset($paymentToken->expirationYear) ? $paymentToken->expirationYear : '';
              $enrollment[$key]['value'] = $month . '/' . $year;
              $formattedDate = $this->getFormattedDate($year, $month);
              $enrollment[$key]['formattedValue'] = $formattedDate['value'];
              $enrollment[$key]['expiredCard'] = $formattedDate['expiredCard'];
            }
            break;

          case 'email':
            $email = $this->getFormattedEmail($traceInfo->email);
            $enrollment[$key]['value'] = $traceInfo->email ?? '';
            $enrollment[$key]['show'] = empty($email) ? FALSE : $enrollment[$key]['show'];
            $enrollment[$key]['formattedValue'] = $email ?? '';
            break;

          case 'customerName':
            $enrollment[$key]['value'] = isset($traceInfo->customerName) ? $traceInfo->customerName : '';
            $enrollment[$key]['formattedValue'] = isset($traceInfo->customerName) ? (string) $traceInfo->customerName : '';
            break;

          case 'maskedCreditCardNumber':
            $enrollment[$key]['value'] = isset($paymentToken->maskedCreditCardNumber) ? $paymentToken->maskedCreditCardNumber : '';
            $enrollment[$key]['formattedValue'] = isset($paymentToken->maskedCreditCardNumber) ?
              (string) $this->getFormattedCreditCard($paymentToken->maskedCreditCardNumber) : '';
            break;

          case 'accountNumber':
            $enrollment[$key]['value'] = isset($response->body->accountNumber) ? $response->body->accountNumber : $id;
            $enrollment[$key]['formattedValue'] = $id;
            break;
        }
        $enrollments[$key] = $enrollment[$key];
      }
    }
    $enrollmentsResponse['enrollments'] = $enrollments;
    if ($paymentGateway->isHe()) {
      $enrollmentsResponse['actions'] = $this->getActions();
      $enrollmentsResponse['message_he'] = $this->config['he_otp']['message'];
      $enrollmentsResponse['actions']['login'] = [
        'label' => $this->config['he_otp']['button'],
        'show' => TRUE,
        'type' => "button",
      ];
    }

    if (!empty($this->config["otherUserEnrollment"]["message"]) && !$paymentGateway->isHe()) {
      $enrollments_response['message'] = str_replace('@email', $enrollmentsResponse['enrollments']['email']['formattedValue'],
        $this->config["otherUserEnrollment"]["message"]);
    }
    if ((strpos($traceInfo->email, 'none') !== FALSE) || (strpos($traceInfo->email, 'null') !== FALSE)) {
      $enrollmentsResponse['enrollments']['email']['show'] = FALSE;
      $enrollmentsResponse['enrollments']['email']['label'] = '';
      $enrollmentsResponse['enrollments']['email']['value'] = '';
      $enrollmentsResponse['enrollments']['email']['formattedValue'] = '';
    }
    return $enrollmentsResponse;
  }
}
