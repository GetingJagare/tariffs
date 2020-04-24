<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Categories
 * @package App
 *
 * @property int $id
 * @property string $name
 */
class Categories extends Model
{
    protected $fillable = ['name'];
}
