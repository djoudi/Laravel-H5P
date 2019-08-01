[![StyleCI](https://styleci.io/repos/122313672/shield?branch=master)](https://styleci.io/repos/122313672)
[![Build Status](https://travis-ci.org/djoudi/Laravel-H5P.svg?branch=master)](https://travis-ci.org/djoudi/Laravel-H5P)
[![Scrutinizer Coverage](https://scrutinizer-ci.com/g/djoudi/Laravel-H5P/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/djoudi/Laravel-H5P/?branch=master)



# H5P Plugin in Laravel Framework 

## Description



## Installation

Require it in the Composer.

```bash
composer require djoudi/laravel-h5p
```

Publish the Views, Config and so things.

```bash
php artisan vendor:publish
```

Migrate the Database

```bash
php artisan migrate
```

Add to Composer-Classmap:
```php
'classmap': [
    "vendor/h5p/h5p-core/h5p-default-storage.class.php",
    "vendor/h5p/h5p-core/h5p-development.class.php",
    "vendor/h5p/h5p-core/h5p-event-base.class.php",
    "vendor/h5p/h5p-core/h5p-file-storage.interface.php",
    "vendor/h5p/h5p-core/h5p.classes.php",
    "vendor/h5p/h5p-editor/h5peditor-ajax.class.php",
    "vendor/h5p/h5p-editor/h5peditor-ajax.interface.php",
    "vendor/h5p/h5p-editor/h5peditor-file.class.php",
    "vendor/h5p/h5p-editor/h5peditor-storage.interface.php",
    "vendor/h5p/h5p-editor/h5peditor.class.php"
],
```

```php
'providers' => [
    Djoudi\LaravelH5p\LaravelH5pServiceProvider::class,
];
```

```bash
cd public/assets/vendor/h5p
ln -s ../../../../storage/h5p/libraries
ln -s ../../../../storage/h5p/editor
```

## Demo
you can see a demo  at : https://github.com/djoudi/laravel-h5p-demo
