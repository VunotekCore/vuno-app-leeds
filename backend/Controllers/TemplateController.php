<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Template;

class TemplateController
{
  public function index(): void
  {
    $templates = Template::findAll();
    Response::success($templates);
  }

  public function pageData(): void
  {
    $templates = Template::findAll();
    $products = \Models\Product::findAll();

    Response::success([
      'templates' => $templates,
      'products'  => $products,
    ]);
  }

  public function show(array $params): void
  {
    $template = Template::findById($params['id']);
    if (!$template) {
      Response::error('Template not found', 404);
    }
    Response::success($template);
  }

  public function store(): void
  {
    $data = Request::body();

    if (empty($data['template_name']) || empty($data['message_body'])) {
      Response::error('template_name and message_body are required', 422);
    }

    $data['created_by'] = $_REQUEST['auth_user']['sub'];
    $uuid = Template::create($data);
    $template = Template::findById($uuid);

    Response::success($template, 'Template created successfully', 201);
  }

  public function update(array $params): void
  {
    $template = Template::findById($params['id']);
    if (!$template) {
      Response::error('Template not found', 404);
    }

    $data = Request::body();
    Template::update($params['id'], $data);
    $updated = Template::findById($params['id']);

    Response::success($updated, 'Template updated successfully');
  }

  public function destroy(array $params): void
  {
    $template = Template::findById($params['id']);
    if (!$template) {
      Response::error('Template not found', 404);
    }

    Template::delete($params['id']);
    Response::success(null, 'Template deleted successfully');
  }
}
