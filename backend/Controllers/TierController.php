<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Tier;

class TierController
{
  public function index(): void
  {
    $tiers = Tier::findAll();
    Response::success($tiers);
  }

  public function show(array $params): void
  {
    $tier = Tier::findById($params['id']);
    if (!$tier) {
      Response::error('Tier not found', 404);
    }
    Response::success($tier);
  }

  public function store(): void
  {
    $data = Request::body();

    if (empty($data['name']) || !isset($data['price'])) {
      Response::error('name and price are required', 422);
    }

    $data['created_by'] = $_REQUEST['auth_user']['sub'];
    $uuid = Tier::create($data);
    $tier = Tier::findById($uuid);

    Response::success($tier, 'Tier created successfully', 201);
  }

  public function update(array $params): void
  {
    $tier = Tier::findById($params['id']);
    if (!$tier) {
      Response::error('Tier not found', 404);
    }

    $data = Request::body();
    Tier::update($params['id'], $data);
    $updated = Tier::findById($params['id']);

    Response::success($updated, 'Tier updated successfully');
  }

  public function destroy(array $params): void
  {
    $tier = Tier::findById($params['id']);
    if (!$tier) {
      Response::error('Tier not found', 404);
    }

    Tier::delete($params['id']);
    Response::success(null, 'Tier deleted successfully');
  }
}
