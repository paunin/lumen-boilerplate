<?php

namespace Test\Behaviour\Traits\Model;

use Illuminate\Database\Schema\Blueprint;
use Ramsey\Uuid\Uuid;
use Test\Behaviour\Traits\Model\Stuff\TestUuid;

/**
 * @coversDefaultClass \App\Traits\Model\UuidModelTrait
 */
class UuidModelTraitTest extends \Test\TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        \Schema::create(
            'test_uuids',
            function (Blueprint $table) {
                $table->uuid('id')
                      ->primary();
                $table->string('name');
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        \Schema::drop('test_uuids');
        parent::tearDown();
    }

    /**
     * @covers ::bootUuidModelTrait
     * @covers ::getIncrementing
     */
    public function testUuidGeneration()
    {
        $model = TestUuid::create(['name' => 'test uuid generation']);
        static::assertFalse($model->incrementing);
        static::assertFalse($model->getIncrementing());
        static::assertTrue(Uuid::isValid($model->getKey()));
    }

    /**
     * @covers ::bootUuidModelTrait
     * @covers ::getIncrementing
     */
    public function testOnAlreadyExistsUuid()
    {
        $uuid  = Uuid::uuid4()
                     ->toString();
        $model = TestUuid::create(['id' => $uuid, 'name' => 'test uuid generation']);
        static::assertFalse($model->incrementing);
        static::assertEquals($uuid, $model->getKey());
    }
}