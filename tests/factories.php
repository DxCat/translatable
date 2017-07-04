<?php

$faktory->define(['post', 'Askaoru\Translatable\Tests\Models\Post'], function ($f) {
    $f->id = 1;
    $f->title = 'Post Title';
    $f->body = 'Some Post Body Content';
});

$faktory->define(['model_translations', 'Askaoru\Translatable\Models\ModelTranslation'], function ($f) {
    $f->type = 'title';
    $f->value = 'Default post title auto created';
    $f->locale = 'en';
    $f->model = 'Askaoru\Translatable\Tests\Models\Post';
    $f->model_id = 1;
});
