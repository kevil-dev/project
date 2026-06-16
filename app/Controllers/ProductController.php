<?php

declare(strict_types=1);
// API response shapes:
// - Success with a resource:  { "data": {...} }
// - Success with just action: { "message": "..." }
// - Any error:                { "errors": { "field": "message", ... } }
// - Non-field errors use the key "general".

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\UnitModel;
use App\Models\ProductModel;

class ProductController
{
    private CategoryModel $categoryModel;
    private UnitModel     $unitModel;
    private ProductModel  $productModel;

    public function __construct(Database $db)
    {
        $this->categoryModel = new CategoryModel($db);
        $this->unitModel     = new UnitModel($db);
        $this->productModel  = new ProductModel($db);
    }
    public function meta(Request $request, array $params): Response
    {
        $response = new Response();

        $response->json([
            'data' => [
                'categories' => $this->categoryModel->getAll(),
                'units'      => $this->unitModel->getAll(),
            ],
        ]);

        return $response;
    }
    public function index(Request $request, array $params): Response
    {
        $page       = max(1, (int) $request->get('page', '1'));
        $search     = trim($request->get('search'));
        $categoryId = (int) $request->get('category_id');
        $unitId     = (int) $request->get('unit_id');

        $total    = $this->productModel->countAll($search, $categoryId, $unitId);
        $products = $this->productModel->getAll($page, $search, $categoryId, $unitId);

        $response = new Response();
        $response->json([
            'data' => $products,
            'meta' => [
                'current_page' => $page,
                'per_page'     => 20,
                'total'        => $total,
            ],
        ]);

        return $response;
    }
    public function show(Request $request, array $params): Response
    {
        $id       = (int) $params['id'];
        $product  = $this->productModel->getById($id);
        $response = new Response();

        if ($product === null) {
            $response->setStatus(404);
            $response->json(['errors' => ['general' => 'Product not found']]);
            return $response;
        }

        $response->json(['data' => $product]);
        return $response;
    }
    public function destroy(Request $request, array $params): Response
    {
        $id       = (int) $params['id'];
        $deleted  = $this->productModel->softDelete($id);
        $response = new Response();

        if (!$deleted) {
            $response->setStatus(404);
            $response->json(['errors' => ['general' => 'Product not found']]);
            return $response;
        }

        $response->json(['message' => 'Product deactivated successfully']);
        return $response;
    }
    public function store(Request $request, array $params): Response
    {
        $name       = trim($request->post('name'));
        $price      = trim($request->post('price'));
        $categoryId = trim($request->post('category_id'));
        $unitId     = trim($request->post('unit_id'));
        $stock      = trim($request->post('stock'));
        $image      = $request->file('image');

        // Validate
        $validator = new \App\Core\Validator();
        $validator->required('name', $name);
        $validator->maxLength('name', $name, 150);
        $validator->required('price', $price);
        $validator->positiveNumber('price', $price);
        $validator->required('category_id', $categoryId);
        $validator->positiveInteger('category_id', $categoryId);
        $validator->required('unit_id', $unitId);
        $validator->positiveInteger('unit_id', $unitId);
        $validator->required('stock', $stock);
        $validator->positiveInteger('stock', $stock);
        $validator->image('image', $image);

        if ($validator->hasErrors()) {
            $response = new Response();
            $response->setStatus(422);
            $response->json(['errors' => $validator->getErrors()]);
            return $response;
        }

        // Handle image upload
        $extension = match (mime_content_type($image['tmp_name'])) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => throw new \RuntimeException('Unexpected MIME type after validation'),
        };

        $filename  = uniqid('product_', true) . '.' . $extension;
        $uploadDir = ROOT_PATH . '/public/uploads/products/';
        $imagePath = '/uploads/products/' . $filename;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        move_uploaded_file($image['tmp_name'], $uploadDir . $filename);

        // Save to database
        $productId = $this->productModel->create(
            $name,
            (float) $price,
            (int) $categoryId,
            (int) $unitId,
            $imagePath,
            (int) $stock
        );

        $response = new Response();
        $response->setStatus(201);
        $response->json([
            'message' => 'Product created successfully',
            'data'    => ['id' => $productId],
        ]);

        return $response;
    }
    public function update(Request $request, array $params): Response
    {
        $id         = (int) $params['id'];
        $name       = trim($request->post('name'));
        $price      = trim($request->post('price'));
        $categoryId = trim($request->post('category_id'));
        $unitId     = trim($request->post('unit_id'));
        $image      = $request->file('image');


        $validator = new \App\Core\Validator();
        $validator->required('name', $name);
        $validator->maxLength('name', $name, 150);
        $validator->required('price', $price);
        $validator->positiveNumber('price', $price);
        $validator->required('category_id', $categoryId);
        $validator->positiveInteger('category_id', $categoryId);
        $validator->required('unit_id', $unitId);
        $validator->positiveInteger('unit_id', $unitId);

        if ($image['error'] !== UPLOAD_ERR_NO_FILE) {
            $validator->image('image', $image);
        }

        if ($validator->hasErrors()) {
            $response = new Response();
            $response->setStatus(422);
            $response->json(['errors' => $validator->getErrors()]);
            return $response;
        }

        $currentImagePath = $this->productModel->getImagePath($id);

        if ($currentImagePath === null) {
            $response = new Response();
            $response->setStatus(404);
            $response->json(['errors' => ['general' => 'Product not found']]);
            return $response;
        }


        $newImagePath = null;

        if ($image['error'] === UPLOAD_ERR_OK) {
            $extension = match (mime_content_type($image['tmp_name'])) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => throw new \RuntimeException('Unexpected MIME type after validation'),
            };

            $filename  = uniqid('product_', true) . '.' . $extension;
            $uploadDir = ROOT_PATH . '/public/uploads/products/';
            $newImagePath = '/uploads/products/' . $filename;

            move_uploaded_file($image['tmp_name'], $uploadDir . $filename);

            $oldFilePath = ROOT_PATH . '/public' . $currentImagePath;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        $updated = $this->productModel->update(
            $id,
            $name,
            (float) $price,
            (int) $categoryId,
            (int) $unitId,
            $newImagePath
        );

        $response = new Response();
        $response->json(['message' => 'Product updated successfully']);
        return $response;
    }
    public function stock(Request $request, array $params): Response
{
    $id   = (int) $params['id'];
    $body = $request->json();

    $quantity = $body['quantity'] ?? null;
    $type     = isset($body['type']) ? trim($body['type']) : '';
    $reason   = isset($body['reason']) ? trim($body['reason']) : '';

    // Validate
    $validator = new \App\Core\Validator();
    $validator->nonZeroInteger('quantity', $quantity);
    $validator->required('type', $type);
    $validator->stockType('type', $type);

    if ($validator->hasErrors()) {
        $response = new Response();
        $response->setStatus(422);
        $response->json(['errors' => $validator->getErrors()]);
        return $response;
    }

    // Check product exists
    $currentImagePath = $this->productModel->getImagePath($id);

    if ($currentImagePath === null) {
        $response = new Response();
        $response->setStatus(404);
        $response->json(['errors' => ['general' => 'Product not found']]);
        return $response;
    }

    // Check stock won't go negative

    // note: not safe if multiple admins edit the same product at once, but good enough for this simple app
    if ($quantity < 0) {
        $currentStock = $this->productModel->getStock($id);

        if ($currentStock + $quantity < 0) {
            $response = new Response();
            $response->setStatus(422);
            $response->json([
                'errors' => [
                    'quantity' => 'Insufficient stock. Current stock is ' . $currentStock
                ]
            ]);
            return $response;
        }
    }

    $this->productModel->addTransaction($id, $quantity, $type, $reason);

    $response = new Response();
    $response->json(['message' => 'Stock updated successfully']);
    return $response;
}
}
