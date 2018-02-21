<?php

namespace Chali5124\LaravelH5p\Eloquents;

use DB;
use Illuminate\Database\Eloquent\Model;

class H5pLibrariesLanguage extends Model {

    protected $primaryKey = ['library_id', 'language_code'];
    protected $fillable = [
        'library_id',
        'language_code',
        'translation'
    ];

}
