<?php

namespace Controllers;

use Core\Response;
use Models\Lead;

class DashboardController
{
  public function index(): void
  {
    $metrics = Lead::getDashboardMetrics();
    $followUpAlerts = Lead::getFollowUpAlerts();

    Response::success([
      'metrics'         => $metrics,
      'follow_up_alerts' => $followUpAlerts,
    ]);
  }
}
