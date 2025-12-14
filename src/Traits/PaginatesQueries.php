<?php

namespace Companue\AutoPaginate\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait PaginatesQueries
{
    /**
     * Apply pagination to a query builder or collection
     * 
     * @param Builder|Collection $query The query builder or collection to paginate
     * @param Request|null $request The request object (optional, will use request() if not provided)
     * @param int $defaultPerPage Default items per page (default: 15)
     * @return LengthAwarePaginator
     */
    protected function applyPagination($query, Request $request = null, int $defaultPerPage = 15)
    {
        $request = $request ?? request();
        $perPage = $request->get('per_page', $defaultPerPage);
        
        // Ensure per_page is within reasonable limits
        $perPage = min(max((int)$perPage, 1), 100);
        
        if ($query instanceof Builder) {
            return $query->paginate($perPage);
        }
        
        if ($query instanceof Collection) {
            $page = $request->get('page', 1);
            return new LengthAwarePaginator(
                $query->forPage($page, $perPage),
                $query->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }
        
        throw new \InvalidArgumentException('Query must be an instance of Builder or Collection');
    }
    
    /**
     * Build a standardized paginated response
     * 
     * @param LengthAwarePaginator $paginator The paginator instance
     * @param string|null $resourceClass Optional resource class to transform items
     * @return array
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $resourceClass = null)
    {
        $items = $resourceClass 
            ? $resourceClass::collection($paginator->items())
            : $paginator->items();
            
        return [
            'data' => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more' => $paginator->hasMorePages()
            ]
        ];
    }
    
    /**
     * Check if the request wants paginated results
     * 
     * @param Request|null $request
     * @return bool
     */
    protected function shouldPaginate(Request $request = null): bool
    {
        $request = $request ?? request();
        
        // Check if pagination is explicitly disabled
        if ($request->has('paginate') && $request->get('paginate') === 'false') {
            return false;
        }
        
        // Only paginate if page or per_page parameters are present
        return $request->has('page') || $request->has('per_page');
    }
    
    /**
     * Get all items or paginate based on request
     * 
     * @param Builder|Collection $query
     * @param string|null $resourceClass
     * @param Request|null $request
     * @return array|Collection
     */
    protected function indexResponse($query, string $resourceClass = null, Request $request = null)
    {
        $request = $request ?? request();
        
        if ($this->shouldPaginate($request)) {
            $paginator = $this->applyPagination($query, $request);
            return $this->paginatedResponse($paginator, $resourceClass);
        }
        
        // Non-paginated response (backward compatibility)
        $items = $query instanceof Builder ? $query->get() : $query;
        return $resourceClass ? $resourceClass::collection($items) : $items;
    }
}
