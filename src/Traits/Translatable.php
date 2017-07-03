<?php

namespace Askaoru\Translatable\Traits;

use Askaoru\Translatable\Models\ModelTranslation;

trait Translatable
{
    /**
     * Establish the link to the translation class.
     *
     * @return \Askaoru\Translatable\Models\ModelTranslation
     */
    public function translation()
    {
        $translation_model = new ModelTranslation();
        $translation_model->setCaller($this);

        return $translation_model;
    }
}
