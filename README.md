[![StyleCI](https://styleci.io/repos/122313672/shield?branch=master)](https://styleci.io/repos/122313672)

# H5P Plugin in Laravel Framework 


## Installation

```bash
composer require djoudi/laravel5.5-h5p
```

```bash
php artisan vendor:publish
```

```bash
php artisan migrate
```

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
cd public/assets;
ln -s ../../storage/h5p;  
```
