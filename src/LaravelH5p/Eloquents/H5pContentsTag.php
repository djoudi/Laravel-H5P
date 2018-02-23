<?php

namespace Djoudi\LaravelH5p\Eloquents;

use Illuminate\Database\Eloquent\Model;

class H5pContentsTag extends Model
{
    protected $primaryKey = ['content_id', 'tag_id'];
    protected $fillable = [
        'content_id',
        'tag_id',
    ];
}
