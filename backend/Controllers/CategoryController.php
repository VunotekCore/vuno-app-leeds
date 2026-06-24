<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Category;

class CategoryController
{
  public function index(): void
  {
    $categories = Category::findAll();
    Response::success($categories);
  }

  public function show(array $params): void
  {
    $category = Category::findById($params['id']);
    if (!$category) {
      Response::error('Category not found', 404);
    }
    Response::success($category);
  }

  public function store(): void
  {
    $data = Request::body();

    if (empty($data['name'])) {
      Response::error('name is required', 422);
    }

    $data['created_by'] = $_REQUEST['auth_user']['sub'];
    $uuid = Category::create($data);
    $category = Category::findById($uuid);

    Response::success($category, 'Category created successfully', 201);
  }

  public function update(array $params): void
  {
    $category = Category::findById($params['id']);
    if (!$category) {
      Response::error('Category not found', 404);
    }

    $data = Request::body();
    Category::update($params['id'], $data);
    $updated = Category::findById($params['id']);

    Response::success($updated, 'Category updated successfully');
  }

  public function destroy(array $params): void
  {
    $category = Category::findById($params['id']);
    if (!$category) {
      Response::error('Category not found', 404);
    }

    Category::delete($params['id']);
    Response::success(null, 'Category deleted successfully');
  }
}
