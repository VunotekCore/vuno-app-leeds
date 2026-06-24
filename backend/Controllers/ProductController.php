<?php

namespace Controllers;

use Core\Request;
use Core\Response;
use Models\Product;

class ProductController
{
  public function index(): void
  {
    $products = Product::findAll();
    Response::success($products);
  }

  public function show(array $params): void
  {
    $product = Product::findById($params['id']);
    if (!$product) {
      Response::error('Product not found', 404);
    }
    Response::success($product);
  }

  public function store(): void
  {
    $data = Request::body();

    if (empty($data['name'])) {
      Response::error('name is required', 422);
    }

    $data['created_by'] = $_REQUEST['auth_user']['sub'];
    $uuid = Product::create($data);
    $product = Product::findById($uuid);

    Response::success($product, 'Product created successfully', 201);
  }

  public function update(array $params): void
  {
    $product = Product::findById($params['id']);
    if (!$product) {
      Response::error('Product not found', 404);
    }

    $data = Request::body();
    Product::update($params['id'], $data);
    $updated = Product::findById($params['id']);

    Response::success($updated, 'Product updated successfully');
  }

  public function destroy(array $params): void
  {
    $product = Product::findById($params['id']);
    if (!$product) {
      Response::error('Product not found', 404);
    }

    Product::delete($params['id']);
    Response::success(null, 'Product deleted successfully');
  }
}