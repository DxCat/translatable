# Laravel 5 Translatable (Work in Progress)

A simple package that makes it really easy for you to create models with translations. 
Laravel already provides a really nice way to support multilingual strings in the blade files, however it is lacking the mean to allow for translations on database entries which is what this package is trying to achieve, elegantly.

# Highlights

- Super simple to use. eally, just use the trait on your models, and you're good to go!
- Only a single table will be needed to store the translations of all of your models. And you can define this table name in the config too!
- Okay maybe there's just 2 points to highlight. I mean it's not really the best package out there. But I try to make it the best that I could!

# Installation
1. Include this package via composer.
````bash
$ composer require askaoru/translatable
````
2. Add the service provider to your `config/app.php` under the providers array.
````php
'providers' => [
    // Other laravel packages ...
    
    Askaoru\Translatable\TranslatableServiceProvider::class,
],
````
3. Publish the config and migration files.
````bash
$ php artisan vendor:publish --provider="Askaoru\Translatable\TranslatableServiceProvider"
````
- Doing so will create 2 files which you can edit if you want to change the database name
````
config\translatable.php
database\migrations\2017_07_01_031473_create_model_translations_table.php
````
4. Finally run the migration.
````bash
$ php artisan migrate
````

# Usage


