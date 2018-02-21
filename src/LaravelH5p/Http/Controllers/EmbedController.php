<?php

namespace Chali5124\LaravelH5p\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Chali5124\LaravelH5p\Events\H5pEvent;

class EmbedController extends Controller {

    public function __invoke(Request $request, $id) {
        $h5p            = App::make('LaravelH5p');
        $core           = $h5p::$core;
        $settings       = $h5p::get_editor();
        $content        = $h5p->get_content($id);
        $embed          = $h5p->get_embed($content, $settings);
        $embed_code     = $embed['embed'];
        $settings       = $embed['settings'];

        event(new H5pEvent('content', NULL, $content['id'], $content['title'], $content['library']['name'], $content['library']['majorVersion'], $content['library']['minorVersion']));

        return view('h5p.content.embed', compact("settings", 'user', 'embed_code'));
    }

}
