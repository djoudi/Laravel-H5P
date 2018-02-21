<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <script>
            window.Laravel = <?php echo json_encode([ 'csrfToken' => csrf_token()]); ?>
        </script>

        {{--    core styles       --}}
        @foreach($settings['core']['styles'] as $style)
        {{ Html::style($style) }}
        @endforeach

        @foreach($settings['loadedCss'] as $style)
        {{ Html::style($style) }}
        @endforeach
    </head>

    <body>

        <div  id="app">
            {!! $embed_code  !!}
        </div>

        <script type="text/javascript" src="{{ url('/assets/js/app.js') }}"></script>
        <script type="text/javascript">
            H5PIntegration = {!! json_encode($settings) !!};
        </script>

        {{--    core script       --}}
        @foreach($settings['core']['scripts'] as $script)
        {{ Html::script($script) }}
        @endforeach

        @foreach($settings['loadedJs'] as $script)
        {{ Html::script($script) }}
        @endforeach

    </body>
</html>
