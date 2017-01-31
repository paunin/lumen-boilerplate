<?php

namespace Test\Behaviour\Traits\Model\Stuff;

use App\Traits\Model\UuidModelTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Model to test uuid generation.
 */
class TestUuid extends Model
{
    use UuidModelTrait;

    public $timestamps = false;
    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
    ];
}
