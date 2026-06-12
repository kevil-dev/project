<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\UnitModel;

// ProductController handles HTTP requests related to products.
// Its ONLY jobs are:
//   1. Ask the right Model for data
//   2. Format that data into a Response
//
// It never writes SQL. All SQL lives in the Models.
class ProductController
{
    private ProductModel  $productModel;
    private CategoryModel $categoryModel;
    private UnitModel     $unitModel;

    // The Database connection arrives from the Router (wired up in index.php).
    // We immediately hand it to the Models — the controller itself never queries the DB.
    public function __construct(Database $db)
    {
        $this->productModel  = new ProductModel($db);
        $this->categoryModel = new CategoryModel($db);
        $this->unitModel     = new UnitModel($db);
    }

    // Handles: GET /api/products?search=...&category_id=...&unit_id=...
    // All three query params are optional — omitting one means "no filter on that field".
    public function index(Request $request, array $params): Response
    {
        // Read the three filter values from the URL query string.
        // Request::get() uses filter_input — never raw $_GET — so it's already sanitised.
        $name       = trim($request->get('search'));      // e.g. "neem"
        $categoryId = (int) $request->get('category_id'); // 0 when omitted or empty
        $unitId     = (int) $request->get('unit_id');     // 0 when omitted or empty

        // search() builds a dynamic WHERE clause — only non-empty/non-zero values
        // add a condition, so passing all-empty values returns the full (≤50) list.
        $products = $this->productModel->search($name, $categoryId, $unitId);

        // Convert each Product entity into a plain array for JSON output.
        // We do this explicitly so we control exactly which fields the API exposes.
        $data = [];
        foreach ($products as $p) {
            $data[] = [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => $p->price,
                'category_id' => $p->category_id,
                'unit_id'     => $p->unit_id,
                'image_path'  => $p->image_path,
            ];
        }

        $response = new Response();
        $response->json(['data' => $data]);
        return $response;
    }

    // Handles: GET /api/meta
    // Returns categories and units so the frontend can fill its filter dropdowns.
    public function meta(Request $request, array $params): Response
    {
        $categories = $this->categoryModel->getAll();
        $units      = $this->unitModel->getAll();

        $categoryData = [];
        foreach ($categories as $c) {
            $categoryData[] = ['id' => $c->id, 'name' => $c->name];
        }

        $unitData = [];
        foreach ($units as $u) {
            $unitData[] = ['id' => $u->id, 'name' => $u->name];
        }

        $response = new Response();
        $response->json([
            'categories' => $categoryData,
            'units'      => $unitData,
        ]);
        return $response;
    }
}
