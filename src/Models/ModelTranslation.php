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
     * Create the field and set the translation if it doesn't exist yet, or update if it already exist.
     *
     * @param string      $type
     * @param string      $value
     * @param string|null $locale
     *
     * @return self
     */
    public function set($type, $value, $locale = null)
    {
        $locale = $this->getLocale($locale);
        $exist = $this->getExistingTranslation($type, $locale);

        if ($exist) {
            $exist->value = $value;
            $exist->save();

            return $exist;
        }

        return self::create([
            'type'     => $type,
            'value'    => $value,
            'locale'   => $locale,
            'model'    => get_class($this->caller),
            'model_id' => $this->caller->id,
        ]);
    }

    /**
     * Return the translation.
     *
     * @param string      $type
     * @param string|null $locale
     *
     * @return string
     */
    public function get($type, $locale = null)
    {
        $locale = $this->getLocale($locale);
        $translation = $this->getExistingTranslation($type, $locale);

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
        $locale = $this->getLocale($locale);
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
        return self::where('type', $type)
            ->where('locale', $locale)
            ->where('model', get_class($this->caller))
            ->where('model_id', $this->caller->id)
            ->first();
    }
}
