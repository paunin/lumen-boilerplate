<?php

namespace Test\Behaviour\Traits\Entity\Stuff;

use App\Traits\Entity\FillableEntityTrait;

/**
 * Stub to test FillableEntityTrait.
 */
class TestFillableEntity
{
    use FillableEntityTrait;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
