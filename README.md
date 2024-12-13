# A package to manage a community or organization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jfsullivan/community-manager.svg?style=flat-square)](https://packagist.org/packages/jfsullivan/community-manager)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jfsullivan/community-manager/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jfsullivan/community-manager/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jfsullivan/community-manager/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jfsullivan/community-manager/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jfsullivan/community-manager.svg?style=flat-square)](https://packagist.org/packages/jfsullivan/community-manager)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require jfsullivan/community-manager
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="community-manager-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="community-manager-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="community-manager-views"
```

## Usage

### Owning a Community
Your Eloquent model, typically the `User` model, that owns a community should use the `jfsullivan\CommunityManager\Traits\OwnsCommunities` trait.

Here's an example of how to implement the trait:

```php
namespace App\Models;

use jfsullivan\CommunityManager\Traits\OwnsCommunities;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use OwnsCommunities;
}
```

### Community Memberships
Your Eloquent model, typically the `User` model, that has community memberships should use the `jfsullivan\CommunityManager\Traits\HasCommunityMemberships` trait.

Here's an example of how to implement the trait:

```php
namespace App\Models;

use jfsullivan\CommunityManager\Traits\HasCommunityMemberships;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasCommunityMemberships;
}
```

Your models' migrations should have a field to save the generated slug to.

With its migration:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYourEloquentModelTable extends Migration
{
    public function up()
    {
        Schema::create('your_eloquent_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug'); // Field name same as your `saveSlugsTo`
            $table->string('name');
            $table->timestamps();
        });
    }
}

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Jack Sullivan](https://github.com/jfsullivan)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
