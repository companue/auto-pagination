# Companue Auto-Paginate

[![Latest Version on Packagist](https://img.shields.io/packagist/v/companue/auto-paginate.svg?style=flat-square)](https://packagist.org/packages/companue/auto-paginate)
[![Total Downloads](https://img.shields.io/packagist/dt/companue/auto-paginate.svg?style=flat-square)](https://packagist.org/packages/companue/auto-paginate)

A Laravel package that provides automatic API pagination with infinite scroll support. Add pagination to any controller endpoint with just one line of code!

## Features

- ✅ **One-Line Integration** - Add pagination with a single method call
- ✅ **Automatic Scroll Detection** - Perfect for infinite scroll UIs
- ✅ **Consistent Response Format** - Standardized across all endpoints
- ✅ **Backward Compatible** - Supports both paginated and non-paginated responses
- ✅ **Flexible** - Works with Query Builders and Collections
- ✅ **Type Safe** - Full PHP type hints and return types
- ✅ **Zero Configuration** - Works out of the box

## Installation

Install via Composer:

```bash
composer require companue/auto-paginate
```

The package will automatically register its service provider.

## Usage

### Basic Usage

Add the trait to your base controller or any specific controller:

```php
use Companue\AutoPaginate\Traits\PaginatesQueries;

class Controller extends BaseController
{
    use PaginatesQueries;
}
```

Then in your controller methods:

```php
public function index(Request $request)
{
    $query = YourModel::query()->orderBy('created_at', 'desc');
    
    return response()->json(
        $this->indexResponse($query, YourResource::class, $request)
    );
}
```

That's it! Your endpoint now supports pagination.

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
    return response()->json(
        $this->indexResponse($query, YourResource::class, $request)
    );
}
```

### Custom Page Size

```php
public function index(Request $request)
{
    $query = YourModel::query();
    
    // Apply pagination with custom default (20 items per page)
    $paginator = $this->applyPagination($query, $request, 20);
    
    return response()->json(
        $this->paginatedResponse($paginator, YourResource::class)
    );
}
```

### Without Resource Transformation

```php
public function index(Request $request)
{
    $query = YourModel::query();
    
    // Pass null to return raw data without resource transformation
    return response()->json(
        $this->indexResponse($query, null, $request)
    );
}
```

### Paginating Collections

```php
public function index(Request $request)
{
    // Get and process collection
    $items = YourModel::query()
        ->get()
        ->map(function($item) {
            $item->calculated_field = $item->price * 1.1;
            return $item;
        });
    
    // Apply pagination to the collection
    $paginator = $this->applyPagination($items, $request);
    
    return response()->json(
        $this->paginatedResponse($paginator, YourResource::class)
    );
}
```

## API Requests

### Paginated Requests
```
GET /api/items?page=1
GET /api/items?page=2&per_page=20
GET /api/items?filter=active&page=1&per_page=10
```

### Non-Paginated Requests
```
GET /api/items?paginate=false
```

## Response Format

### Paginated Response
```json
{
  "data": [
    { "id": 1, "name": "Item 1" },
    { "id": 2, "name": "Item 2" }
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
  { "id": 1, "name": "Item 1" },
  { "id": 2, "name": "Item 2" }
]
```

## Available Methods

### `indexResponse($query, $resourceClass = null, $request = null)`
Smart method that automatically handles pagination based on request parameters.

**Parameters:**
- `$query` - Query Builder or Collection
- `$resourceClass` - Optional resource class for transformation
- `$request` - Optional request object (defaults to `request()`)

**Returns:** Array with data and pagination info, or raw collection

### `applyPagination($query, $request = null, $defaultPerPage = 15)`
Manually apply pagination to a query or collection.

**Parameters:**
- `$query` - Query Builder or Collection
- `$request` - Optional request object
- `$defaultPerPage` - Default items per page (default: 15, max: 100)

**Returns:** `LengthAwarePaginator`

### `paginatedResponse($paginator, $resourceClass = null)`
Build standardized paginated response from a paginator.

**Parameters:**
- `$paginator` - LengthAwarePaginator instance
- `$resourceClass` - Optional resource class

**Returns:** Array with data and pagination metadata

### `shouldPaginate($request = null)`
Check if pagination should be applied based on request.

**Parameters:**
- `$request` - Optional request object

**Returns:** Boolean

## Configuration

The package works with sensible defaults:
- **Default page size:** 15 items
- **Maximum page size:** 100 items
- **Pagination parameter:** `page`
- **Page size parameter:** `per_page`

## Frontend Integration

Perfect for infinite scroll implementations:

```javascript
// React/Vue example
const loadMore = () => {
  if (pagination.has_more && !loading) {
    fetchData({ page: pagination.current_page + 1 });
  }
};
```

## Migration from Manual Pagination

**Before:**
```php
$perPage = $request->get('per_page', 15);
$records = YourModel::query()->paginate($perPage);
$items = YourResource::collection($records->items());

return response()->json([
    'data' => $items,
    'pagination' => [
        'current_page' => $records->currentPage(),
        'last_page' => $records->lastPage(),
        // ... more fields
    ]
]);
```

**After:**
```php
$query = YourModel::query();

return response()->json(
    $this->indexResponse($query, YourResource::class, $request)
);
```

## Testing

```bash
composer test
```

## Requirements

- PHP 8.1 or higher
- Laravel 10.0, 11.0, or 12.0

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [mimalefdal](https://github.com/mimalefdal)
- [All Contributors](../../contributors)

## Support

For support, please open an issue on GitHub or contact mimalefdal@gmail.com
