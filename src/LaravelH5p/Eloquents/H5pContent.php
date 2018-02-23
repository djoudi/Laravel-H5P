<?php

namespace Djoudi\LaravelH5p\Eloquents;

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
        'license',
        'keywords',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'user_id');
    }

    public function get_user()
    {
        return (object) DB::table('users')->where('id', $this->user_id)->first();
    }
}
