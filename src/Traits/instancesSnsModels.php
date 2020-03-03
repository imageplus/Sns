<?php


namespace Imageplus\Sns\src\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait instancesSnsModels
 * @package Imageplus\Sns\Traits
 */
trait instancesSnsModels
{
    /**
     * Holds the uninstantiated model
     * @var Model
     */
    public $model;

    /**
     * instancesSnsModels constructor.
     */
    public function __construct()
    {
        //when the parent loads add its model
        $this->getModel();
    }

    /**
     * Sets the model based off a variable on the parent or using a portion of this name
     */
    public function getModel(){
        //the model is either set as a value or its in the name in the default cases so use this
        $model = Str::lower(
            isset($this->model_name)
                    ? $this->model_name
                    : str_replace(
                        'Handler',
                        '',
                        str_replace('Sns', '', class_basename($this))
                    )
                );

        //TODO: Change str_replace to regex replace

        //set the model to the configured model
        $this->model = config("sns.models.{$model}");
    }
}
