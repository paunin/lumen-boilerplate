<?php

namespace App\Traits\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Trait to generate UUID for model.
 */
trait UuidModelTrait
{
    /**
     * This function overwrites the default boot static method of Eloquent models. It will hook
     * the creation event with a simple closure to insert the UUID
     */
    public static function bootUuidModelTrait()
    {
        static::creating(
            function (Model $model) {
                $model->incrementing = false;
                if ($model->getKey() === null) {
                    $model->setAttribute(
                        $model->getKeyName(),
                        Uuid::uuid4()
                            ->toString()
                    );
                }
            },
            0
        );
    }

    /**
     * This function is used internally by Eloquent models to test if the model has auto increment value
     *
     * @return bool Always false
     */
    public function getIncrementing()
    {
        return false;
    }
}
