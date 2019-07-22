<?php

namespace Djoudi\LaravelH5p\Http\Controllers;

use App\Http\Controllers\Controller;
use Djoudi\LaravelH5p\Events\H5pEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DownloadController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $h5p = App::make('LaravelH5p');
        $core = $h5p::$core;
        $interface = $h5p::$interface;

        $content = $core->loadContent($id);
        $content['filtered'] = '';
        $params = $core->filterParameters($content);

        return response()
            ->download($interface->_download_file, '', [
                'Content-Type'  => 'application/zip',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            ]);

        event(new H5pEvent('download', null, $content['id'], $content['title'], $content['library']['name'], $content['library']['majorVersion'], $content['library']['minorVersion']));
    }
}
