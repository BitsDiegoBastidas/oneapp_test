<?php

namespace Drupal\oneapp_convergent_upgrade_plan_gt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UpgradePlanLogGtController.
 */
class UpgradePlanLogGtController extends ControllerBase {

  /**
   * @var \Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradePlanLogGtModelService
   */
  protected $logModel;

  /**
   * UpgradePlanLogGtController constructor.
   * @param \Drupal\oneapp_convergent_upgrade_plan_gt\Services\UpgradePlanLogGtModelService $upgrade_plan_log_model
   */
  public function __construct($upgrade_plan_log_model) {
    $this->logModel = $upgrade_plan_log_model;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oneapp.convergent.UpgradePlanLogGtModelService')
    );
  }

  /**
   * @param Request $request
   * @return array
   */
  public function renderUpgradePlanLogGtTable(Request $request) {

    $query_params = $request->query->all();

    $build['form'] = \Drupal::formBuilder()->getForm(
      \Drupal\oneapp_convergent_upgrade_plan_gt\Form\UpgradePlanLogGtFilterForm::class
    );

    if (!empty($query_params['business_unit'])) {
      $build['form']['business_unit']['#value'] = $query_params['business_unit'];
    }

    if (!empty($query_params['search'])) {
      $build['form']['search']['#value'] = $query_params['search'];
    }

    if (!empty($query_params['start_date'])) {
      $build['form']['start_date']['#value'] = $query_params['start_date'];
    }

    if (!empty($query_params['end_date'])) {
      $build['form']['end_date']['#value'] = $query_params['end_date'];
    }

    $build['table'] = [
      '#type' => 'table',
      '#header' => $this->logModel::getTableReportHeaders(),
      '#rows' => $this->logModel::getTableReport($query_params),
      '#empty' => t('No content has been found.'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

  /**
   * @param Request $request
   */
  public function exportUpgradePlanLogGtTable(Request $request) {
    $query_params = $request->query->all();
    // Get and build the headers
    $headers = $this->logModel::getTableReportHeaders();
    array_walk($headers, function (&$val, $key) { $val = $val['data']; });
    // Ger results
    $rows = $this->logModel::getTableReport($query_params, 0);
    $delimiter = ';';
    $enclosure = '"';
    $file_name = 'upgrade_plan_log_' . date('Ymd_His') . '.csv';
    /** force download */
    header("Content-Type: text/plain; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"{$file_name}\"");
    // In case there aren't rows
    if (empty($rows)) {
      echo "No se encontraron registros con los parámetros ingresados.";
      exit();
    }

    $output = fopen("php://output", "w");
    // Set headers
    fputcsv($output, $headers, $delimiter, $enclosure);
    // Set rows
    foreach ($rows as $row) {
      fputcsv($output, $row, $delimiter, $enclosure);
    }
    fclose($output);
    exit();
  }
}
