<?php

namespace Djoudi\LaravelH5p\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class H5pLibrary extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'title',
        'major_version',
        'minor_version',
        'patch_version',
        'runnable',
        'restricted',
        'fullscreen',
        'embed_types',
        'preloaded_js',
        'preloaded_css',
        'drop_library_css',
        'semantics',
        'tutorial_url',
        'has_icon',
        'created_at',
        'updated_at',
    ];

    public function numContent()
    {
        $h5p = App::make('LaravelH5p');
        $interface = $h5p::$interface;

        return intval($interface->getNumContent($this->id));
    }

    public function getCountContentDependencies()
    {
        $h5p = App::make('LaravelH5p');
        $interface = $h5p::$interface;
        $usage = $interface->getLibraryUsage($this->id, $interface->getNumNotFiltered() ? true : false);

        return intval($usage['content']);
    }

    public function getCountLibraryDependencies()
    {
        $h5p = App::make('LaravelH5p');
        $interface = $h5p::$interface;
        $usage = $interface->getLibraryUsage($this->id, $interface->getNumNotFiltered() ? true : false);

        return intval($usage['libraries']);
    }
}
