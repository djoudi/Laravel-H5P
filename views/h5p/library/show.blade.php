@extends( config('laravel-h5p.layout') )

@section( 'h5p' )
<div class="container-fluid">
    <div class="row">

        <div class="col-md-12">

            <h3>{{ $library->title }}</h3>

            <ul>
                <li class=''>{{ trans('laravel-h5p.library.version') }} : {{ $settings['libraryInfo']['info']['version'] }}</li>
                <li class=''>{{ trans('laravel-h5p.h5p.fullscreen') }} : {{ $settings['libraryInfo']['info']['fullscreen'] }}</li>
                <li class=''> {{ trans('laravel-h5p.library.contents') }}: {{ $settings['libraryInfo']['info']['content_library'] }}</li>
                <li class=''>{{ trans('laravel-h5p.library.contents_using_it') }}  : {{ $settings['libraryInfo']['info']['used'] }}</li>
            </ul>


            <a href="{{ route('h5p.library.index') }}" class="btn btn-default"><i class="fa fa-reply"></i> Go Back</a>


        </div>

    </div>

</div>

@endsection

@push( 'h5p-header-script' )
    @foreach($required_files['styles'] as $style)
    {{ Html::style($style) }}
    @endforeach
@endpush

@push( 'h5p-footer-script' )
    <script type="text/javascript">
        H5PAdminIntegration = {!! json_encode($settings) !!};
    </script>

    {{--    core script       --}}
    @foreach($required_files['scripts'] as $script)
    {{ Html::script($script) }}
    @endforeach

@endpush
