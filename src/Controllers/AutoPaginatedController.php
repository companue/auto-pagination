<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Companue\AutoPaginate\Traits\PaginatesQueries;

/**
 * Auto-Paginated Controller
 * 
 * All API controllers should extend this class to get pagination methods:
 * - indexResponse() - Smart auto-pagination
 * - applyPagination() - Manual pagination control
 * - paginatedResponse() - Standardized response builder
 * - shouldPaginate() - Pagination detection
 * 
 * @package Companue\AutoPaginate
 */
class AutoPaginatedController extends Controller
{
    use PaginatesQueries;
}
