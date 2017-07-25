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
        if ($this->getExistingTranslation($type, $locale)) {
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
        if ($translation = $this->getExistingTranslation($type, $locale)) {
            return $translation->value;
        }
    }

    /**
     * Return an array of specified locales for a given type, return all when no locales are given.
     *
     * @param string $type
     * @param array  $locale
     *
     * @return array
     */
    public function getAll($type, $locale = [])
    {
        return $this->whereTypeAndLocale($type, $locale)->get()
                    ->map(function ($row) {
                        return [$row->locale => $row->value];
                    })
                    ->collapse()
                    ->all();
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
        if ($exist = $this->getExistingTranslation($type, $locale)) {
            $exist->delete();

            return true;
        }

        return false;
    }

    /**
     * Clear the translations of specified locales for a given type, clear all when no locales are given.
     *
     * @param string $type
     * @param array  $locale
     *
     * @return int
     */
    public function clearAll($type, $locale = [])
    {
        return $this->whereTypeAndLocale($type, $locale)->delete();
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

        return $this->whereType($type)
                    ->where('locale', $locale)
                    ->first();
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
     * Return query with all translation if no locale provided.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $type
     * @param array                                 $locale
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereTypeAndLocale($query, $type, $locale = [])
    {
        if (empty($locale)) {
            return $query->whereType($type);
        }

        return $query->whereType($type)->whereIn('locale', $locale);
    }

    /**
     * Return query for type of caller model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereType($query, $type)
    {
        return $query->where('type', $type)
                     ->where('model', get_class($this->caller))
                     ->where('model_id', $this->caller->id);
    }
}
