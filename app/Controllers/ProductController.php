<?php

declare(strict_types=1);

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
            'categories' => $this->categoryModel->getAll(),
            'units'      => $this->unitModel->getAll(),
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
            $response->json(['error' => 'Product not found']);
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
            $response->json(['error' => 'Product not found']);
            return $response;
        }

        $response->json(['message' => 'Product deactivated successfully']);
        return $response;
    }
}
