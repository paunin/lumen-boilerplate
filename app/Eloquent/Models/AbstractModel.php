<?php

namespace App\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract model
 *
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static |null first($columns = ['*'])
 */
abstract class AbstractModel extends Model
{
}
