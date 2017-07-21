<?php

namespace Askaoru\Translatable\Tests;

class TranslatableTraitTest extends TestCase
{
    /**
     * @var Askaoru\Translatable\Tests\Models\Post
     */
    protected $post;

    /**
     * Set up the environtment for this test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->post = $this->faktory->create('post');
    }

    /**
     * Target : Make sure that the translation() returns an instance of Askaoru\Translatable\Models\ModelTranslation.
     * Target 2 : The returned instance must have a caller property which is an instance of the original class.
     */
    public function testTraitConnection()
    {
        $this->assertInstanceOf('Askaoru\Translatable\Models\ModelTranslation', $this->post->translation());
        $this->assertInstanceOf('Askaoru\Translatable\Tests\Models\Post', $this->post->translation()->getCaller());
    }

    /**
     * Target : Make sure that the createTranslation() are able to create the translation field.
     */
    public function testCreateTranslation()
    {
        $translation = $this->post->translation()->createTranslation('title', 'Post Title Translation');

        $this->assertEquals('title', $translation->type);
        $this->assertEquals('Post Title Translation', $translation->value);
    }

    /**
     * Target : Make that createTranslation() are able to create entry with specified locale.
     */
    public function testCreateTranslationWithSpecifiedLocale()
    {
        $translation = $this->post->translation()->createTranslation('title', 'Terjemahan Tajuk', 'my');

        $this->assertEquals('title', $translation->type);
        $this->assertEquals('Terjemahan Tajuk', $translation->value);
        $this->assertEquals('my', $translation->locale);
    }

    /**
     * Target : Make sure updateTranslation() does update existing record properly.
     */
    public function testUpdateTranslation()
    {
        $this->post->translation()->createTranslation('title', 'Original created title');

        $translation = $this->post->translation()->updateTranslation('title', 'Post Title Translation');

        $this->assertEquals('Post Title Translation', $translation->value);
        $this->assertNotEquals('Original created title', $translation->value);
    }

    /**
     * Target : Make sure set() function can create a translation field.
     */
    public function testSetSingleCreateTranslation()
    {
        $translation = $this->post->translation()->set('title', 'Post Title Translation');

        $this->assertEquals('Post Title Translation', $translation->value);
    }

    /**
     * Target : Make sure set() function can update a translation field.
     * Condition : Translation of this type and locale already exist.
     */
    public function testSetUpdateSingleTranslation()
    {
        $this->post->translation()->createTranslation('title', 'Original created title');

        $translation = $this->post->translation()->set('title', 'Post Title Translation');

        $this->assertEquals('Post Title Translation', $translation->value);
        $this->assertNotEquals('Original created title', $translation->value);
    }

    /**
     * Target : Make sure createOrUpdateSingleTranslation() are able to determine whether to create or update a translation.
     * Condition : Translation doesn't originally exist when creating, and translation exist when updating.
     */
    public function testCreateOrUpdateSingleTranslation()
    {
        $this->assertNull($this->post->translation()->get('title'));

        $translation = $this->post->translation()->createOrUpdateSingleTranslation('title', 'Original Post Title');
        $this->assertEquals('Original Post Title', $translation->value);

        $translation_update = $this->post->translation()->createOrUpdateSingleTranslation('title', 'Updated Post Title');
        $this->assertEquals('Updated Post Title', $translation_update->value);
    }

    /**
     * Target : Make sure set() can update and create multiple translations at the same time.
     */
    public function testSetMultipleTranslations()
    {
        $this->post->translation()->createTranslation('title', 'Original created title');

        $translations = $this->post->translation()->set('title', ['en' => 'Post Title Translation', 'my' => 'Terjemahan Tajuk']);

        $this->assertEquals('en', $translations[0]->locale);
        $this->assertEquals('Post Title Translation', $translations[0]->value);
        $this->assertNotEquals('Original created title', $translations[0]->value);

        $this->assertEquals('my', $translations[1]->locale);
        $this->assertEquals('Terjemahan Tajuk', $translations[1]->value);
    }

    /**
     * Target : Make sure get() are able to return the translation value.
     * Condition : Translation field exist.
     */
    public function testGetTranslationValue()
    {
        $this->post->translation()->createTranslation('title', 'Original created title');

        $this->assertEquals('Original created title', $this->post->translation()->get('title'));
    }

    /**
     * Target : Make sure clear() can delete a translation entry.
     * Condition : Translation field exist.
     */
    public function testClearTranslation()
    {
        $this->post->translation()->createTranslation('title', 'Original created title');

        $this->assertTrue($this->post->translation()->clear('title'));
        $this->assertNull($this->post->translation()->get('title'));
    }

    /**
     * Target : Make sure createOrUpdateSingleTranslation() updates the translation table on second time .
     */
    public function testCreateOrUpdateSingleTranslationUpdatesWithoutLocaleOnSecondTime()
    {
        $this->post->translation()->createOrUpdateSingleTranslation('title', 'First Title Without Locale');

        $this->post->translation()->createOrUpdateSingleTranslation('title', 'Second Title Without Locale');

        $this->assertNull($this->post->translation()->where('value', 'First Title Without Locale')->first());
        $this->assertEquals('Second Title Without Locale', $this->post->translation()->get('title'));
    }

    /**
     * Target : Make sure getAll without locale returns all translations .
     */
    public function testGetAllTranslationsWithoutLocale()
    {
        $this->post->translation()->set('title', ['en' => 'Post Title', 'ar' => 'اول عنوان المنشور']);

        $this->assertCount(2, $this->post->translation()->getAll('title'));
    }

    /**
     * Target : Make sure getAll with locale returns exact translations with locale .
     */
    public function testGetAllTranslationsWithLocale()
    {
        $this->post->translation()->set('title', ['en' => 'Post Title', 'ar' => 'اول عنوان المنشور']);

        $this->assertEquals('Post Title', $this->post->translation()->getAll('title', ['en'])['en']);
        $this->assertCount(1, $this->post->translation()->getAll('title', ['ar']));
        $this->assertCount(2, $this->post->translation()->getAll('title', ['ar', 'en']));
        $this->assertCount(2, $this->post->translation()->getAll('title', []));
    }

    /**
     * Target : Make sure clearAll without locale clears all translations.
     */
    public function testClearAllTranslationsWithoutLocale()
    {
        $this->post->translation()->set('title', ['en' => 'Post Title', 'ar' => 'اول عنوان المنشور']);

        $this->assertEquals(2, $this->post->translation()->clearAll('title'));
        $this->assertCount(0, $this->post->translation()->getAll('title'));
    }

    /**
     * Target : Make sure clearAll with locale clears exact translations.
     */
    public function testClearAllTranslationsWithLocale()
    {
        $this->post->translation()->set('title', ['en' => 'Post Title', 'ar' => 'اول عنوان المنشور', 'my' => 'Terjemahan Tajuk']);

        $this->assertEquals(1, $this->post->translation()->clearAll('title', ['ar']));
        $this->assertCount(2, $this->post->translation()->getAll('title'));
        $this->assertEquals('Post Title', $this->post->translation()->getAll('title', ['en'])['en']);
        $this->assertEquals(2, $this->post->translation()->clearAll('title', ['en', 'my']));
        $this->assertCount(0, $this->post->translation()->getAll('title'));
    }
}
