<?php

/**
 * EXAMPLE: How to Add Pagination to Any Controller
 * 
 * This file demonstrates how to quickly add pagination support to any controller
 * using the PaginatesQueries trait (already included in base Controller).
 */

namespace App\Http\Controllers\API\Examples;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExampleController extends Controller
{
    /**
     * EXAMPLE 1: Basic pagination with resource
     * 
     * Before:
     * $items = YourModel::query()->orderBy('created_at', 'desc')->get();
     * $resources = YourResource::collection($items);
     * return Response::json($resources);
     * 
     * After (just one line change):
     */
    public function basicExample(Request $request)
    {
        $query = YourModel::query()->orderBy('created_at', 'desc');
        
        return Response::json(
            $this->indexResponse($query, YourResource::class, $request)
        );
    }
    
    /**
     * EXAMPLE 2: With filters and relationships
     */
    public function withFiltersExample(Request $request)
    {
        $query = YourModel::query();
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }
        
        // Add relationships
        $query->with(['customer', 'products', 'category']);
        
        // Apply ordering
        $query->orderBy('created_at', 'desc');
        
        // Return paginated response
        return Response::json(
            $this->indexResponse($query, YourResource::class, $request)
        );
    }
    
    /**
     * EXAMPLE 3: Custom page size
     */
    public function customPageSizeExample(Request $request)
    {
        $query = YourModel::query()->orderBy('name', 'asc');
        
        // Apply pagination with custom default (20 items)
        $paginator = $this->applyPagination($query, $request, 20);
        
        return Response::json(
            $this->paginatedResponse($paginator, YourResource::class)
        );
    }
    
    /**
     * EXAMPLE 4: Without resource transformation
     */
    public function withoutResourceExample(Request $request)
    {
        $query = YourModel::query()->orderBy('id', 'desc');
        
        // Pass null as resource class to return raw data
        return Response::json(
            $this->indexResponse($query, null, $request)
        );
    }
    
    /**
     * EXAMPLE 5: Complex query with scopes
     */
    public function complexQueryExample(Request $request)
    {
        $query = YourModel::query()
            ->active()  // Custom scope
            ->whereHas('customer', function($q) use ($request) {
                if ($request->has('customer_type')) {
                    $q->where('type', $request->get('customer_type'));
                }
            })
            ->with(['customer', 'products.category'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc');
        
        return Response::json(
            $this->indexResponse($query, YourResource::class, $request)
        );
    }
    
    /**
     * EXAMPLE 6: Paginating a collection (after query)
     */
    public function collectionExample(Request $request)
    {
        // Get collection with complex processing
        $items = YourModel::query()
            ->with(['relations'])
            ->get()
            ->map(function($item) {
                // Some custom processing
                $item->calculated_field = $item->price * 1.1;
                return $item;
            })
            ->filter(function($item) {
                // Some filtering logic
                return $item->calculated_field > 100;
            });
        
        // Apply pagination to the collection
        $paginator = $this->applyPagination($items, $request);
        
        return Response::json(
            $this->paginatedResponse($paginator, YourResource::class)
        );
    }
}

/**
 * QUICK MIGRATION CHECKLIST:
 * 
 * 1. Find your index() method
 * 2. Replace ->get() with just the query builder
 * 3. Replace manual Response::json() with:
 *    return Response::json(
 *        $this->indexResponse($query, YourResource::class, $request)
 *    );
 * 4. Done! ✅
 * 
 * 
 * API USAGE:
 * 
 * GET /api/your-endpoint?page=1
 * GET /api/your-endpoint?page=2&per_page=20
 * GET /api/your-endpoint?status=active&page=1
 * GET /api/your-endpoint?paginate=false  (disable pagination)
 * 
 * 
 * RESPONSE FORMAT:
 * {
 *   "data": [...],
 *   "pagination": {
 *     "current_page": 1,
 *     "last_page": 5,
 *     "per_page": 15,
 *     "total": 75,
 *     "from": 1,
 *     "to": 15,
 *     "has_more": true
 *   }
 * }
 */
