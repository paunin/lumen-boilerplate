<?php

namespace Test\Behaviour\Traits\Entity;

use Test\Behaviour\Traits\Entity\Stuff\TestFillableEntity;

/**
 * @coversDefaultClass \App\Traits\Entity\FillableEntityTrait
 */
class FillableEntityTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see FillableEntityTrait::fillFromArray
     */
    public function testFillFromArray()
    {
        $entity = new TestFillableEntity();
        $return = $entity->fillFromArray(['value' => 'test value']);

        static::assertAttributeEquals('test value', 'value', $entity);
        static::assertSame($entity, $return);
    }
}
