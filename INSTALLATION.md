# Installation Guide for Companue Auto-Paginate Package

## For Local Development (Using Symlinks)

### Step 1: Add Repository to composer.json

Add the local package path to your Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../../packages-repository/companue/auto-paginate",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

### Step 2: Require the Package

```bash
composer require companue/auto-paginate:@dev
```

Or add to `composer.json`:

```json
{
    "require": {
        "companue/auto-paginate": "^1.0"
    }
}
```

Then run:

```bash
composer update companue/auto-paginate
```

### Step 3: Update Your Base Controller

Replace local trait import with package trait:

**Before:**
```php
use App\Traits\PaginatesQueries;
```

**After:**
```php
use Companue\AutoPaginate\Traits\PaginatesQueries;
```

Full example:

```php
<?php

namespace App\Http\Controllers;

use Companue\AutoPaginate\Traits\PaginatesQueries;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, PaginatesQueries;
}
```

### Step 4: Remove Old Local Trait (Optional)

If you had a local trait, you can now remove it:

```bash
rm app/Traits/PaginatesQueries.php
```

### Step 5: Clear Cache

```bash
php artisan clear-compiled
php artisan cache:clear
composer dump-autoload
```

## For Production Deployment

### Option 1: Via Packagist (Recommended)

Once published to Packagist:

```bash
composer require companue/auto-paginate
```

### Option 2: Via Private Repository

Add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/companue/auto-paginate.git"
        }
    ],
    "require": {
        "companue/auto-paginate": "^1.0"
    }
}
```

Then:

```bash
composer install
```

## Verification

After installation, verify the package is working:

```bash
php artisan about
```

Look for `Companue\AutoPaginate\Providers\AutoPaginateServiceProvider` in the providers list.

## Usage

Use in any controller:

```php
public function index(Request $request)
{
    $query = YourModel::query()->orderBy('created_at', 'desc');
    
    return response()->json(
        $this->indexResponse($query, YourResource::class, $request)
    );
}
```

## Troubleshooting

### Trait Not Found

If you get "Trait not found" error:

```bash
composer dump-autoload
php artisan clear-compiled
```

### Symlink Issues on Windows

If symlink doesn't work on Windows:

1. Run CMD or PowerShell as Administrator
2. Enable Developer Mode in Windows Settings
3. Or use `"symlink": false` in composer.json

### Package Not Loaded

Check if service provider is registered:

```bash
php artisan package:discover
```

## Migration from Local Implementation

1. Install package as described above
2. Update Controller.php to use package trait
3. Remove local `app/Traits/PaginatesQueries.php`
4. Test existing endpoints
5. Commit changes

## Updates

To update the package:

```bash
composer update companue/auto-paginate
```

For development with symlinks:

```bash
cd ../../packages-repository/companue/auto-paginate
git pull
cd -
composer dump-autoload
```

## Complete Example

**composer.json:**
```json
{
    "require": {
        "companue/auto-paginate": "^1.0"
    },
    "repositories": [
        {
            "type": "path",
            "url": "../../packages-repository/companue/auto-paginate",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

**Controller.php:**
```php
<?php

namespace App\Http\Controllers;

use Companue\AutoPaginate\Traits\PaginatesQueries;

class Controller extends BaseController
{
    use PaginatesQueries;
}
```

**PreorderController.php:**
```php
public function index(Request $request)
{
    $query = Preorder::query()
        ->with(['customer', 'products'])
        ->orderBy('created_at', 'desc');
    
    return Response::json(
        $this->indexResponse($query, PreorderSimpleItem::class, $request)
    );
}
```

That's it! You're ready to use auto-pagination across your application! 🚀
