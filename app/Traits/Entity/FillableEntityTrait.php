<?php
declare(strict_types=1);

namespace App\Traits\Entity;

/**
 * Trait to allow fill object from array.
 */
trait FillableEntityTrait
{
    /**
     * @param array $array
     *
     * @return static
     */
    public function fillFromArray(array $array)
    {
        foreach ($array as $property => $value) {
            $setter = 'set' . ucfirst($property);
            call_user_func([$this, $setter], $value);
        }

        return $this;
    }
}
