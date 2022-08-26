<?php


namespace Drupal\oneapp_convergent_upgrade_plan_gt\Services;

use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Query\SelectExtender;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Database\Statement;

class UpgradePlanLogGtModelService {

  protected static $tableName = 'oneapp_convergent_upgrade_plan_gt_log';
  protected static $tableAlias = 'log';

  /**
   * @param array $filter
   * @param int $limit
   * @return array
   */
  public static function getTableReport($params = [], $limit = 20, $sort_by_headers = true) {

    $headers = self::getTableReportHeaders();

    /** @var Select $query */
    $query = \Drupal::database()
      ->select(self::$tableName, self::$tableAlias)
      ->fields(self::$tableAlias, array_keys($headers));

    // Builds data from request params to build conditions
    if (!empty($conditions = self::getTableReportConditions($params))) {
      // Prepare orConditionGroup
      $or_group = $query->orConditionGroup();
      // Prepare andConditionGroup
      $and_group = $query->andConditionGroup();
      foreach ($conditions as $condition) {
        if ($condition['type'] == 'or_group') {
          $or_group->condition(self::$tableAlias . '.' . $condition['field'], $condition['value'], $condition['operator']);
        }
        if ($condition['type'] == 'and_group') {
          $and_group->condition(self::$tableAlias . '.' . $condition['field'], $condition['value'], $condition['operator']);
        }
      }
      if ($or_group->count()) {
        $query->condition($or_group);
      }
      if ($and_group->count()) {
        $query->condition($and_group);
      };
    }

    if (!empty($limit)) {
      /** @var SelectExtender $query */
      $query = $query->extend(PagerSelectExtender::class)->limit($limit);
    }

    if (!empty($sort_by_headers)) {
      /** @var SelectExtender $query */
      $query = $query->extend(TableSortExtender::class)->orderByHeader($headers);
    }

    /** @var Statement $stm */
    $stm = $query->execute();

    if ($stm) {
      return $stm->fetchAll(\PDO::FETCH_NUM);
    }
    return [];
  }

  /**
   * @return array
   */
  public static function getTableReportHeaders() {
    return [
      'id' => ['data' => t('Id'), 'field' => 'id', 'sort' => 'DESC'],
      'transaction_id' => ['data' => t('Transaction Id'), 'field' => 'transaction_id'],
      'client_name' => ['data' => t('Client'), 'field' => 'client_name'],
      'service_number' => ['data' => t('Service Number'), 'field' => 'service_number'],
      'bundle_plan' => ['data' => t('Bundle Plan'), 'field' => 'bundle_plan'],
      'name_plan' => ['data' => t('Name Plan'), 'field' => 'name_plan'],
      'data' => ['data' => t('Data'), 'field' => 'data'],
      'plan' => ['data' => t('Plan'), 'field' => 'plan'],
      'lead_id' => ['data' => t('Lead Id'), 'field' => 'lead_id'],
      'contract_id' => ['data' => t('Contract Id'), 'field' => 'contract_id'],
      'date' => ['data' => t('Date'), 'field' => 'date'],
      'business_unit' => ['data' => t('B. Unit'), 'field' => 'business_unit'],
    ];
  }

  /**
   * Builds data from request params to build conditions
   * @param $params
   * @return array
   */
  public static function getTableReportConditions($params) {
    if (!empty($params['business_unit'])) {
      $conditions[] = ['type' => 'and_group', 'field' => 'business_unit', 'value' => $params['business_unit'], 'operator' => '='];
    }
    if (!empty($params['search'])) {
      $conditions[] = ['type' => 'or_group', 'field' => 'client_name', 'value' => "%{$params['search']}%", 'operator' => 'LIKE'];
      $conditions[] = ['type' => 'or_group', 'field' => 'service_number', 'value' => "%{$params['search']}%", 'operator' => 'LIKE'];
      $conditions[] = ['type' => 'or_group', 'field' => 'bundle_plan', 'value' => "%{$params['search']}%", 'operator' => 'LIKE'];
      $conditions[] = ['type' => 'or_group', 'field' => 'name_plan', 'value' => "%{$params['search']}%", 'operator' => 'LIKE'];
      $conditions[] = ['type' => 'or_group', 'field' => 'lead_id', 'value' => "%{$params['search']}%", 'operator' => 'LIKE'];
      $conditions[] = ['type' => 'or_group', 'field' => 'contract_id', 'value' => "%{$params['search']}%", 'operator' => 'LIKE'];
    }
    if (!empty($params['start_date'])) {
      $start_date = "{$params['start_date']} 00:00:00";
      $conditions[] = ['type' => 'and_group', 'field' => 'date', 'value' => $start_date, 'operator' => '>='];
    }
    if (!empty($params['end_date'])) {
      $end_date = "{$params['end_date']} 23:59:59";
      $conditions[] = ['type' => 'and_group', 'field' => 'date', 'value' => $end_date, 'operator' => '<='];
    }
    return $conditions ?? [];
  }
}
