<?php

namespace Chali5124\LaravelH5p\Eloquents;

use DB;
use Illuminate\Database\Eloquent\Model;

class H5pLibrariesCachedasset extends Model {

    protected $primaryKey = ['library_id', 'hash'];
    protected $fillable = [
        'library_id',
        'hash'
    ];

}
