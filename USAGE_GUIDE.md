# Pagination Implementation Guide

## Overview
A structured pagination solution has been implemented using the `PaginatesQueries` trait. This allows all controllers to support pagination without code duplication.

## Architecture

### 1. PaginatesQueries Trait
**Location:** `app/Traits/PaginatesQueries.php`

This trait provides reusable pagination methods:
- `applyPagination()` - Applies pagination to queries or collections
- `paginatedResponse()` - Builds standardized paginated response
- `shouldPaginate()` - Determines if pagination should be applied
- `indexResponse()` - Smart method that handles both paginated and non-paginated responses

### 2. Base Controller
**Location:** `app/Http/Controllers/Controller.php`

The base controller now includes the `PaginatesQueries` trait, making pagination available to all controllers automatically.

## Usage in Controllers

### Simple Usage (Recommended)
```php
public function index(Request $request)
{
    $query = YourModel::query()->orderBy('created_at', 'desc');
    
    return Response::json(
        $this->indexResponse($query, YourResourceClass::class, $request)
    );
}
```

### With Filters
```php
public function index(Request $request)
{
    $query = YourModel::query();
    
    // Apply filters
    if ($request->has('status')) {
        $query->where('status', $request->get('status'));
    }
    
    // Apply ordering
    $query->orderBy('created_at', 'desc');
    
    // Return with automatic pagination
    return Response::json(
        $this->indexResponse($query, YourResourceClass::class, $request)
    );
}
```

### Manual Pagination Control
```php
public function index(Request $request)
{
    $query = YourModel::query()->with(['relations']);
    
    // Apply pagination manually
    $paginator = $this->applyPagination($query, $request, 20); // 20 items per page
    
    return Response::json(
        $this->paginatedResponse($paginator, YourResourceClass::class)
    );
}
```

### Without Resource Class
```php
public function index(Request $request)
{
    $query = YourModel::query();
    
    // Returns raw items without resource transformation
    return Response::json(
        $this->indexResponse($query, null, $request)
    );
}
```

## API Request Examples

### Paginated Requests
```
GET /api/preorders?page=1
GET /api/preorders?page=2&per_page=20
GET /api/preorders?filter=current&page=1&per_page=10
```

### Non-Paginated Requests (Backward Compatibility)
```
GET /api/preorders?paginate=false
```

## Response Format

### Paginated Response
```json
{
  "data": [
    { "id": 1, "title": "Item 1" },
    { "id": 2, "title": "Item 2" }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "from": 1,
    "to": 15,
    "has_more": true
  }
}
```

### Non-Paginated Response
```json
[
  { "id": 1, "title": "Item 1" },
  { "id": 2, "title": "Item 2" },
  ...
]
```

## Migration Guide for Existing Controllers

### Before (Manual Pagination)
```php
public function index(Request $request)
{
    $perPage = $request->get('per_page', 15);
    $records = YourModel::query()
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);
    
    $items = YourResource::collection($records->items());
    
    return Response::json([
        'data' => $items,
        'pagination' => [
            'current_page' => $records->currentPage(),
            'last_page' => $records->lastPage(),
            'per_page' => $records->perPage(),
            'total' => $records->total(),
            'has_more' => $records->hasMorePages()
        ]
    ]);
}
```

### After (Using Trait)
```php
public function index(Request $request)
{
    $query = YourModel::query()->orderBy('created_at', 'desc');
    
    return Response::json(
        $this->indexResponse($query, YourResource::class, $request)
    );
}
```

## Features

### ✅ Automatic Pagination
- Defaults to 15 items per page
- Customizable via `per_page` parameter
- Maximum 100 items per page (safety limit)

### ✅ Backward Compatibility
- Non-paginated responses still work with `paginate=false`
- Existing frontend code continues to function

### ✅ Flexible
- Works with Query Builders
- Works with Collections
- Optional resource transformation
- Customizable defaults

### ✅ Standardized Response
- Consistent response format across all endpoints
- All pagination metadata included
- Frontend-friendly `has_more` flag

## Examples in Different Controllers

### Example 1: PreorderController
```php
public function index(Request $request)
{
    switch ($request->get('filter')) {
        case 'deleted':
            $query = Preorder::onlyTrashed();
            break;
        case 'new':
            $query = Preorder::withStatus('new');
            break;
        default:
            $query = Preorder::query();
            break;
    }
    
    $query = $query->with(['customer', 'products'])->orderBy('created_at', 'desc');
    
    return Response::json(
        $this->indexResponse($query, PreorderSimpleItem::class, $request)
    );
}
```

### Example 2: ProductController (Hypothetical)
```php
public function index(Request $request)
{
    $query = Product::query()
        ->with(['category', 'variants'])
        ->orderBy('name', 'asc');
    
    return Response::json(
        $this->indexResponse($query, ProductResource::class, $request)
    );
}
```

### Example 3: OrderController (Hypothetical)
```php
public function index(Request $request)
{
    $query = Order::query();
    
    // Apply search
    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->get('search') . '%');
    }
    
    // Apply date filter
    if ($request->filled('from_date')) {
        $query->where('created_at', '>=', $request->get('from_date'));
    }
    
    $query->orderBy('created_at', 'desc');
    
    return Response::json(
        $this->indexResponse($query, OrderResource::class, $request)
    );
}
```

## Benefits

1. **DRY Principle** - No code duplication across controllers
2. **Consistency** - All endpoints return the same pagination format
3. **Maintainability** - Changes to pagination logic only need to be made in one place
4. **Flexibility** - Easy to customize per endpoint if needed
5. **Type Safety** - Proper type hints and return types
6. **Performance** - Built on Laravel's optimized pagination
7. **Frontend Ready** - Response format matches frontend expectations

## Notes

- The trait is already included in the base `Controller` class
- All controllers extending `Controller` automatically have access to pagination methods
- The `indexResponse()` method is the recommended approach for most use cases
- Pagination can be disabled per request using `?paginate=false`
- Default page size is 15, maximum is 100 (configurable in trait)
