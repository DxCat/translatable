# Laravel 5 Translatable

A simple package that makes it really easy for you to create models with translations. 
Laravel already provides a really nice way to support multilingual strings in the blade files, however it is lacking the mean to allow for translations on database entries which is what this package is trying to achieve, elegantly.

# Highlights

- Super simple to use. Really, just use the trait on your models, and you're good to go!
- Only a single table will be needed to store the translations of all of your models. And you can define this table name in the config too!

# Installation
1. Include this package via composer.
```bash
$ composer require askaoru/translatable
```
2. Add the service provider to your `config/app.php` under the providers array.
```php
'providers' => [
    // Other laravel packages ...
    
    Askaoru\Translatable\TranslatableServiceProvider::class,
],
```
3. Publish the config and migration files.
```bash
$ php artisan vendor:publish --provider="Askaoru\Translatable\TranslatableServiceProvider"
```
- Doing so will create 2 files which you can edit if you want to change the database name
```
config\translatable.php
database\migrations\2017_07_01_031473_create_model_translations_table.php
```
4. Finally run the migration.
```bash
$ php artisan migrate
```

# Setting up
To use this package on your project, all you need to do is inherit the translatable trait in your model.
Like for an example, a Post model which you'd like to have multilanguage support for.

```php
<?php

namespace App;

use Askaoru\Translatable\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Translatable;
    
    //
}

```

# Usage
Basically, there's only 3 methods that you would ever need for this package, `set()`, `get()` and `clear()`. 

1. The `set()` method.
This method is the how you would set your translations. It's very versatile as you can use it to set a single or multiple languages at the same time. It is also a 'save or update' kind of things which mean that if you're trying to set a translation to something that has been defined before, it would update that. But if it was never defined, it will create the record instead.

The third parameter would be which language you're setting the translation for, but if you does not define it, it will use laravel's default language that are set at the moment.

```php
    # Example
    // Fetch the post with an ID of 1.
    $post = Post::find(1);
    
    // Set the post title to 'Hello World' in the default language (assuming the default / current language is English).
    $post->translation()->set('title', 'Hello World');
    
    // Set the post title to 'こんにちは世界' in the japanese locale.
    $post->translation()->set('title', 'こんにちは世界', 'jp');
    
    // Set the post title to multiple locales at the same time.
    $post->translation()->set('title', [
        'en' => 'Hello World',
        'jp' => 'こんにちは世界'
    ]);
```

2. The `get()` method.
This simple method is how you fetch the translation that you have set.

```php
    # Example
    // Again, fetch the model that this translation trait is attached to, in this case, Post.
    $post = Post::find(1);
    
    // Get the title in current / default language
    $post->translation()->get('title'); // Would return 'Hello World' again assuming default is English.
    
    // Get the title in other language, in this example, Japanese.
    $post->translation()->get('title', 'jp'); // Would return 'こんにちは世界'.
    
    //Get all titles with there locale
    $post->translation()->getAll('title'); //Would return ['en' => 'Title' , 'ar' => 'عنوان']
    
    //Get all titles for sepcific locales
    $post->translation()->getAll('title',['en','my']); //Would return ['en' => 'Title' , 'my' => 'Alamat']
```

3. The `clear()` method.
Well this is just as you would imagine. It would clear the translation that was set. This method will return true if deletion is successful and will return false if the translation doesn't exist. 

```php

    # Example .. Seriously making documentations is tiring but a good documentation is important to have...
    // Fetch the post.
    $post = Post::find(1);
    
    // Clear the translation for the post title of current / default language, English.
    $post->translation()->clear('title');
    
    // Clear the translation for the post title of the Japanese language.
    $post->translation()->clear('title', 'jp');
    
    // Clear all translations for post title 
    $post->translation()->clearAll('title'); //Would return number of deleted rows
    
    // Clear the translation for specific locales
    $post->translation()->clearAll('title',['en','ar']); //Would remove translations of ar and en
```

# Contributions
Every pull request and contributions to improve this package would be very, very much appreciated. And if you have any question, found a bug, or need any help related to this package, please do open an issue. I will do my best to attend to them.

[List of all contributors](https://github.com/askaoru/translatable/graphs/contributors)

That's it, I hope this little package would be helpful to you. Thanks for giving it a try!
