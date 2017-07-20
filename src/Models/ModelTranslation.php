<?php

namespace Askaoru\Translatable\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\App;

class ModelTranslation extends Eloquent
{
    /**
     * Property to save the caller class on.
     *
     * @var object
     */
    protected $caller;

    /**
     * The attributes that can be set with Mass Assignment.
     *
     * @var array
     */
    protected $fillable = ['type', 'value', 'locale', 'model', 'model_id'];

    /**
     * Override the getTable() method to set the database from config.
     *
     * @return string
     */
    public function getTable()
    {
        return config('translatable.database', 'model_translations');
    }

    /**
     * Save the calling class details.
     *
     * @param object $caller
     *
     * @return void
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
    }

    /**
     * Return the saved caller.
     *
     * @return object
     */
    public function getCaller()
    {
        return $this->caller;
    }

    /**
     * Sets the translations for the caller model.
     *
     * @param string      $type
     * @param string      $value
     * @param string|null $locale
     *
     * @return self
     */
    public function set($type, $value, $locale = null)
    {
        if (is_array($value)) {
            $translation_collection = [];

            foreach ($value as $locale => $translation) {
                $translation_collection[] = $this->createOrUpdateSingleTranslation($type, $translation, $locale);
            }

            return collect($translation_collection);
        }

        return $this->createOrUpdateSingleTranslation($type, $value, $locale);
    }

    /**
     * Determine whether the record should be created or updated.
     *
     * @param string      $type
     * @param string      $value
     * @param string|null $locale
     *
     * @return self
     */
    public function createOrUpdateSingleTranslation($type, $value, $locale = null)
    {
        $exist = $this->getExistingTranslation($type, $locale);

        if ($exist) {
            return $this->updateTranslation($type, $value, $locale);
        }

        return $this->createTranslation($type, $value, $locale);
    }

    /**
     * Create new translation field.
     *
     * @param string      $type
     * @param string      $value
     * @param string|null $locale
     *
     * @return self
     */
    public function createTranslation($type, $value, $locale = null)
    {
        $locale = $this->getLocale($locale);

        return self::create([
            'type'     => $type,
            'value'    => $value,
            'locale'   => $locale,
            'model'    => get_class($this->caller),
            'model_id' => $this->caller->id,
        ]);
    }

    /**
     * Update existing translation value.
     *
     * @param string      $type
     * @param string      $value
     * @param string|null $locale
     *
     * @return self
     */
    public function updateTranslation($type, $value, $locale = null)
    {
        $translation = $this->getExistingTranslation($type, $locale);

        $translation->value = $value;
        $translation->save();

        return $translation;
    }

    /**
     * Return the translation.
     *
     * @param string      $type
     * @param string|null $locale
     *
     * @return mixed
     */
    public function get($type, $locale = null)
    {
        $translation = $this->getExistingTranslation($type, $locale);

        if (!$translation) {
            return;
        }

        return $translation->value;
    }

    /**
     * Clear the translation for the given type.
     *
     * @param string $type
     * @param string $locale
     *
     * @return bool
     */
    public function clear($type, $locale = null)
    {
        $exist = $this->getExistingTranslation($type, $locale);

        if (!$exist) {
            return false;
        }

        $exist->delete();

        return true;
    }

    /**
     * Return the locale if it's set, return default application locale if not set.
     *
     * @param string $locale
     *
     * @return string
     */
    protected function getLocale($locale)
    {
        if (is_null($locale)) {
            $locale = App::getLocale();
        }

        return $locale;
    }

    /**
     * Return the existing record for specified type and locale of the caller model.
     *
     * @param string $type
     * @param string $locale
     *
     * @return self
     */
    protected function getExistingTranslation($type, $locale)
    {
        $locale = $this->getLocale($locale);

        return self::where('type', $type)
            ->where('locale', $locale)
            ->where('model', get_class($this->caller))
            ->where('model_id', $this->caller->id)
            ->first();
    }
}
