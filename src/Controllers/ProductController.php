<?php
declare(strict_types=1);

namespace Api\Controllers;

use Api\Models\Product;
use Api\Utils\Response;
use Api\Utils\Validator;

class ProductController
{
    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($_GET['per_page'] ?? 10)));

        $filters = [
            'search'   => $_GET['search'] ?? null,
            'category' => $_GET['category'] ?? null
        ];

        $result = Product::all($filters, $page, $perPage);

        Response::success(
            $result['items'],
            'Products retrieved successfully.',
            200,
            $result['meta']
        );
    }

    public function show(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $product = Product::findById($id);

        if (!$product) {
            Response::error('Product not found.', 404);
        }

        Response::success($product, 'Product found.');
    }

    public function store(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $validator = new Validator($input);
        $validator->required('name')->minLength('name', 3)
                  ->required('sku')->minLength('sku', 3)
                  ->required('price')->numeric('price', 0.01)
                  ->required('category');

        if (!$validator->isValid()) {
            Response::error('Validation failed for product payload.', 422, $validator->getErrors());
        }

        if (Product::findBySku($input['sku'])) {
            Response::error("A product with SKU '{$input['sku']}' already exists.", 409);
        }

        $id = Product::create($input);
        $newProduct = Product::findById($id);

        Response::success($newProduct, 'Product created successfully.', 201);
    }

    public function update(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $product = Product::findById($id);

        if (!$product) {
            Response::error('Product not found for update.', 404);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($input)) {
            Response::error('No payload provided for update.', 400);
        }

        if (isset($input['sku'])) {
            $existing = Product::findBySku($input['sku'], $id);
            if ($existing) {
                Response::error("SKU '{$input['sku']}' is already assigned to another product.", 409);
            }
        }

        if (isset($input['price']) && (!is_numeric($input['price']) || (float)$input['price'] <= 0)) {
            Response::error('Price must be a positive numeric value.', 422);
        }

        Product::update($id, $input);
        $updated = Product::findById($id);

        Response::success($updated, 'Product updated successfully.');
    }

    public function destroy(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $product = Product::findById($id);

        if (!$product) {
            Response::error('Product not found for deletion.', 404);
        }

        Product::delete($id);

        Response::success(null, 'Product removed successfully.', 200);
    }
}
