<?php

namespace Djoudi\LaravelH5p\Eloquents;

use App\User;
use DB;
use Illuminate\Database\Eloquent\Model;

//use App\Models\User;

class H5pContent extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'title',
        'library_id',
        'parameters',
        'filtered',
        'slug',
        'embed_type',
        'disable',
        'content_type',
        'author',
        'source',
        'year_from',
        'year_to',
        'license',
        'license_version',
        'license_extras',
        'author_comments',
        'changes',
        'default_languge',
        'keywords',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function get_user()
    {
        return (object) DB::table('users')->where('id', $this->user_id)->first();
    }
}
