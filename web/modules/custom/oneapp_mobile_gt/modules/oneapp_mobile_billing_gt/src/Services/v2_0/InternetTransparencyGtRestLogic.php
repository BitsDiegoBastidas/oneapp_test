<?php

namespace Drupal\oneapp_mobile_billing_gt\Services\v2_0;



use Drupal\oneapp_mobile_billing\Services\v2_0\InternetTransparencyRestLogic;

/**
 * Class InternetTransparencyRestLogic.
 */
class InternetTransparencyGtRestLogic extends InternetTransparencyRestLogic {

  const KB = 1024;

  const MB = 1024 * self::KB;

  const GB = 1024 * self::MB;

  protected $appIconDefault = 'default.svg';

  private $internetConsumptionEntities = NULL;

  /**
   * Responds to GET requests.
   *
   * @param string $msisdn
   *   Msisdn.
   *
   * @return array
   *   The response to summary configurations.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   * @throws \Exception
   *   Throws exception expected.
   */
  public function get($msisdn) {
    $this->loadInternetConsumptionEntities();
    $form = $this->initFormArray();
    $appList = $this->getSanitizeAppList($msisdn, $form);
    if (isset($appList) && count($appList) > 0) {
      return [
        'packageHistory' => $appList,
        'form' => $form,
      ];
    }
    else {
      return [
        'noData' => [
          'value' => 'empty',
        ],
      ];
    }
  }
  public function initFormArray() {
    return [
      'filters' => [
        'label' => 'Paquete',
        'show' => TRUE,
        'options' => [],
      ]
    ];
  }

  public function getSanitizeAppList($msisdn, &$form) {
    $result = [];
    $response = $this->getAppsList($msisdn);
    $packageHistory = $response->packageHistory;
    foreach ($packageHistory as $item) {
      $package = [
        'packageType' => [
          'label' => '',
          'show' => TRUE,
          'value' => count($result),
          'formattedValue' => $item->packageName,
        ],
        'summaryConsumption' => [
          'usedData' => $this->getUsedConsumption($item->quota, $item->unusedQuota),
          'quota' => $this->getQuota($item->quota),
          'endDate' => $this->getEndDate($item->startDate, $item->endDate),
        ],
        'appConsumption' => $this->getSanitizedApps($item->appConsumption, $this->getValue($item->quota) - $this->getValue($item->unusedQuota))
      ];
      if (count($package['appConsumption']) == 0) {
        $package['message'] = $this->configBlock['config']['messageConsumptionEmpty'];
      }
      else {
        $package['message'] = $this->configBlock['config']['messageConsumption'];
      }
      $option = [
        'show' => TRUE,
        'value' => $package['packageType']['value'],
        'formattedValue' => $item->packageName,
      ];
      $result[] = $package;
      $form['filters']['options'][] = $option;
    }
    return $result;
  }

  public function loadInternetConsumptionEntities() {
    $storage = \Drupal::entityTypeManager()->getStorage('mobile_internet_consum_entity');
    $ids = \Drupal::entityQuery('mobile_internet_consum_entity')->execute();
    $this->internetConsumptionEntities = $storage->loadMultiple($ids);
  }

  public function getValue($dataValue) {
    $value = 0;
    if (strpos($dataValue, ' MB') !== FALSE) {
      $value = str_replace(' MB', '', $dataValue);
      $value = floatval($value);
      $value = $value * 1024 * 1024;
    }
    return $value;
  }

  public function getQuota($quota) {
    return [
      'label' => $this->configBlock['consumption']['fields']['quota']['label'],
      'show' => (bool) $this->configBlock['consumption']['fields']['quota']['show'],
      'value' => round($this->getValue($quota)),
      'formattedValue' => $this->formatData($this->getValue($quota)),
    ];
  }
  public function getUsedConsumption($quota, $unusedQuota) {
    $value = $this->getValue($quota) - $this->getValue($unusedQuota);
    return [
      'label' => $this->configBlock['consumption']['fields']['remainingConsumption']['label'],
      'show' => (bool) $this->configBlock['consumption']['fields']['remainingConsumption']['show'],
      'value' => round($value),
      'formattedValue' => $this->formatData($value),
    ];
  }
  public function getEndDate($starDate, $endDate) {
    return [
      'label' => $this->configBlock['consumption']['fields']['endDate']['label'],
      'show' => (bool) $this->configBlock['consumption']['fields']['endDate']['show'],
      'value' => [
        'startDate' => $starDate,
        'endDate' => $endDate,
      ],
      'formattedValue' => date('d/M h:ia', strtotime($endDate)),
    ];
  }

  public function getSanitizedApps($arrApps, $usedData) {
    $result = [];
    usort($arrApps, function ($x, $y) {
      return  (floatval($x->mb) < floatval($y->mb));
    });
    foreach ($arrApps as $app) {
      $entityConfig = $this->findInternetConsumptionEntity($app->appName);
      $friendlyName = isset($entityConfig['friendlyName']) ? $entityConfig['friendlyName'] : NULL;
      $appIcon = isset($entityConfig['appIcon']) ? $entityConfig['appIcon'] : NULL;
      $app = [
        'appName' => $this->getAppName($app->appName, $friendlyName),
        'usedData' => $this->getUsedData($app->mb),
        'usedDataPercentage' => $this->getUsedDataPercentage($app->mb, $usedData),
        'appIcon' => $this->getAppIcon($appIcon),
      ];
      $result[] = $app;
    }
    return $result;
  }

  public function getAppName($appName, $friendlyName = NULL) {
    return [
      'label' => '',
      'show' => (bool) $this->configBlock['consumption']['fields']['appName']['show'],
      'value' => $appName,
      'formattedValue' => isset($friendlyName) ? $friendlyName : $appName,
    ];
  }

  public function getUsedDataPercentage($consumption, $used) {
    $percent = ($consumption * 1024 * 1024 * 100) / $used;
    $temp = floatval(intval($percent));
    $diff = $percent - $temp;
    if ($diff > 0.5 ) {
      $temp = intval($temp) + 1;
    }
    return [
      'label' => '',
      'show' => (bool) $this->configBlock['consumption']['fields']['usedDataPercentage']['show'],
      'value' => $temp,
      'formattedValue' => $temp . '%',
    ];
  }

  public function getUsedData($usedData) {
    $value = floatval($usedData) * 1024 * 1024;
    $value = intval($value);
    return [
      'label' => '',
      'show' => (bool) $this->configBlock['consumption']['fields']['usedData']['show'],
      'value' => $usedData,
      'formattedValue' => $this->formatData($value),
    ];
  }

  public function getAppIcon($appIcon = NULL) {
    return [
      'label' => '',
      'show' => (bool) $this->configBlock['consumption']['fields']['appIcon']['show'],
      'imageName' => isset($appIcon) ? $appIcon : $this->appIconDefault,
    ];
  }

  public function formatData($value) {
    if ($value >= self::GB) {
      $quotient = floatval($value / self::GB);
      //$temp = round($quotient, 1);
      $temp = number_format((float)$quotient, 1, '.', '');
      return $temp . ' GB';
    }
    elseif ($value >= self::MB) {
      $quotient = floatval($value / self::MB);
      $temp = floatval(intval($quotient));
      $diff = $quotient - $temp;
      if ($diff > 0.5 ) {
        $temp = intval($temp) + 1;
      }
      return $temp . ' MB';
    }
    elseif ($value >= self::KB) {
      $quotient = floatval($value / self::MB);
      $temp = floatval(intval($quotient));
      $diff = $quotient - $temp;
      if ($diff > 0.5 ) {
        $temp = intval($temp) + 1;
      }
      return $temp . ' MB';
    }
    else {
      return $value . ' ' . ($value ? 'B' : 'MB');
    }
  }

  public function findInternetConsumptionEntity($appName) {
    if (!isset($this->internetConsumptionEntities)) {
      return NULL;
    }
    $result = [];
    foreach ($this->internetConsumptionEntities as $consumptionEntity) {
      if (strtolower($consumptionEntity->getAppName()) == strtolower($appName)) {
        $result['friendlyName'] = $consumptionEntity->label();
        $result['appIcon'] = $consumptionEntity->getAppIcon();
      }
    }
    return $result;
  }
}

