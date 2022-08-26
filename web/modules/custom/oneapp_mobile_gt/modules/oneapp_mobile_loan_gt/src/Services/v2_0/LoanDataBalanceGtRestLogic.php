<?php

namespace Drupal\oneapp_mobile_loan_gt\Services\v2_0;

use Drupal\oneapp\Exception\HttpException;
use Drupal\oneapp_mobile_loan\Services\v2_0\LoanDataBalanceRestLogic;

/**
 * Class LoanDataBalanceGtRestLogic.
 */
class LoanDataBalanceGtRestLogic extends LoanDataBalanceRestLogic {


  /**
   * Get loan balance.
   *
   * @param string $msisdn
   *   Msisdn of the user.
   *
   * @return array
   *   Return associative array.
   */
  public function get($msisdn) {

    $config = $this->configBlock;
    $scoring = $this->getLendingScoring($msisdn);
    $currencyId = $this->utils->getCurrencyId();

    $creditAvailable = $scoring->response[0]->CreditAvailable == NULL ? 0 : $scoring->response[0]->CreditAvailable;
    $formattedCreditAvailable = $this->utils->formatCurrency($creditAvailable, TRUE, FALSE);

    $totalDebt = $scoring->response[0]->TotalDebt == NULL ? 0 : $scoring->response[0]->TotalDebt;
    $formattedTotalDebt = $this->utils->formatCurrency($totalDebt, TRUE, FALSE);

    $data = [
      'creditAvailable' => [
        'value' => [
          [
            'amount' => (double) $creditAvailable,
            'currencyId' => $currencyId,
          ],
        ],
        'formattedValue' => $formattedCreditAvailable,
        'label' => $config['fields']['creditAvailable']['label'],
        'show' => (bool) $config['fields']['creditAvailable']['show'],
      ],
      'totalDebt' => [
        'value' => [
          [
            'amount' => (double) $totalDebt,
            'currencyId' => $currencyId,
          ],
        ],
        'formattedValue' => $formattedTotalDebt,
        'label' => $config['fields']['totalDebt']['label'],
        'show' => (bool) $config['fields']['totalDebt']['show'],
      ],
      'scoring' => [
        'value' => TRUE,
        'formattedValue' => TRUE,
        'label' => $config['fields']['scoring']['label'],
        'unit' => '',
        'show' => (bool) $config['fields']['scoring']['show'],
      ],
      'overdraft' => [
        'value' => TRUE,
        'formattedValue' => TRUE,
        'label' => $config['fields']['overdraft']['label'],
        'unit' => '',
        'show' => (bool) $config['fields']['overdraft']['show'],
      ],
    ];

    $config = [
      'actions' => [
        'purchase' => [
          'label' => $config['actions']['purchase']['label'],
          'url' => $config['actions']['purchase']['url'],
          'type' => $config['actions']['purchase']['type'],
          'show' => (double) $scoring->response[0]->TotalDebt > 0 ? (bool)$config['actions']['purchase']['show']: false,
        ],
        'info' => [
          'label' => $config['actions']['info']['label'],
          'url' => $config['actions']['info']['url'],
          'type' => $config['actions']['info']['type'],
          'show' => (bool) $config['actions']['info']['show'],
        ],
      ],
    ];


    return [
      'data' => $data,
      'config' => $config,
    ];

  }

}
