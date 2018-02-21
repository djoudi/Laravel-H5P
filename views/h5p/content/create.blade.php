@extends( config('laravel-h5p.layout') )

@section( 'h5p' )
<div class="container-fluid">

    <div class="row">

        <div class="col-md-12">

            {!! Form::open(['route' => ['h5p.store'], 'class'=>'form-horizontal', 'enctype'=>"multipart/form-data", 'id'=>'laravel-h5p-form']) !!}
            <input type="hidden" name="library" id="laravel-h5p-library" value="{{ $library }}">
            <input type="hidden" name="parameters" id="laravel-h5p-parameters" value="{{ $parameters }}">
            
            <fieldset>

                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                    <label for="inputTitle" class="control-label col-md-3">{{ trans('laravel-h5p.content.title') }}</label>
                    <div class="col-md-9">
                        {{ Form::text('title', old('title'), [
                                'class' => 'form-control',
                                'placeholder' => trans('laravel-h5p.content.title'),
                                'id' => 'laravel-h5p-title',
                            ]) }}

                        @if ($errors->has('title'))
                        <span class="help-block">
                            {{ $errors->first('title') }}
                        </span>
                        @endif
                    </div>
                </div>


                <div id="laravel-h5p-create" class="form-group {{ $errors->has('parameters') ? 'has-error' : '' }}">
                    <label for="inputParameters" class="control-label col-md-3">{{ trans('laravel-h5p.content.parameters') }}</label>
                    <div class="col-md-9">
                        <div>
                            <div id="laravel-h5p-editor">{{ trans('laravel-h5p.content.loading_content') }}</div>
                        </div>

                        @if ($errors->has('parameters'))                 
                        <span class="help-block">
                            {{ $errors->first('parameters') }}
                        </span>
                        @endif

                    </div>
                </div>



                <div class="form-group laravel-h5p-upload-container">
                    <label for="inputUpload" class="control-label col-md-3">{{ trans('laravel-h5p.content.upload') }}</label>
                    <div class="col-md-9">
                        <input type="file" name="h5p_file" id="h5p-file" class="laravel-h5p-upload form-control"/>
                        <small class="h5p-disable-file-check helper-block">
                            <label class="">
                                <input type="checkbox" name="h5p_disable_file_check" id="h5p-disable-file-check"/> {{ trans('laravel-h5p.content.upload_disable_extension_check') }}
                            </label>
                        </small>

                        @if ($errors->has('library'))
                        <span class="help-block">
                            {{ $errors->first('upload') }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group {{ $errors->has('action') ? 'has-error' : '' }}">
                    <label for="inputAction" class="control-label col-md-3">{{ trans('laravel-h5p.content.action') }}</label>
                    <div class="col-md-6">

                        <label class="radio-inline">
                            <input type="radio" name="action" value="upload" class="laravel-h5p-type" >{{ trans('laravel-h5p.content.action_upload') }}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="action" value="create" class="laravel-h5p-type" checked="checked"/>{{ trans('laravel-h5p.content.action_create') }}
                        </label>


                        @if ($errors->has('action'))
                        <span class="help-block">
                            {{ $errors->first('action') }}
                        </span>
                        @endif
                    </div>
                </div>



                @if(config('laravel-h5p.h5p_show_display_option'))

                <div class="form-group h5p-sidebar">
                    <label class="control-label col-md-3">{{ trans('laravel-h5p.content.display') }}</label>
                    <div class="col-md-9">

                        <div class="form-control-static">

                            <ul class="list-unstyled">

                                <li>
                                    <label>
                                        {{ Form::checkbox('frame', true, $display_options[H5PCore::DISPLAY_OPTION_FRAME], [
                                        'class' => 'h5p-visibility-toggler',
                                        'data-h5p-visibility-subject-selector' => ".h5p-action-bar-buttons-settings",
                                        'id' => 'laravel-h5p-title',
                                        'value' => old('title')
                                    ]) }}
                                        {{ trans("laravel-h5p.content.display_toolbar") }}
                                    </label>
                                </li>

                                @if(isset($display_options[H5PCore::DISPLAY_OPTION_DOWNLOAD]))
                                <li>
                                    <label>
                                        {{ Form::checkbox('download', true, $display_options[H5PCore::DISPLAY_OPTION_DOWNLOAD], [
                                        'class' => 'h5p-visibility-toggler',
                                        'data-h5p-visibility-subject-selector' => ".h5p-action-bar-buttons-settings",
                                        'id' => 'laravel-h5p-title',
                                        'value' => old('title')
                                    ]) }}
                                        {{ trans("laravel-h5p.content.display_download_button") }}
                                    </label>
                                </li>
                                @endif

                                @if (isset($display_options[H5PCore::DISPLAY_OPTION_EMBED]))
                                <li>
                                    <label>
                                        {{ Form::checkbox('embed', true, $display_options[H5PCore::DISPLAY_OPTION_EMBED], [
                                        'class' => 'h5p-visibility-toggler',
                                        'data-h5p-visibility-subject-selector' => ".h5p-action-bar-buttons-settings",
                                        'id' => 'laravel-h5p-title',
                                        'value' => old('title')
                                    ]) }}
                                        {{ trans("laravel-h5p.content.display_embed_button") }}
                                    </label>
                                </li>
                                @endif

                                @if  (isset($display_options[H5PCore::DISPLAY_OPTION_COPYRIGHT]))
                                <li>
                                    <label>
                                        {{ Form::checkbox('copyright', true, $display_options[H5PCore::DISPLAY_OPTION_COPYRIGHT], [
                                        'class' => 'h5p-visibility-toggler',
                                        'data-h5p-visibility-subject-selector' => ".h5p-action-bar-buttons-settings",
                                        'id' => 'laravel-h5p-title',
                                        'value' => old('title')
                                    ]) }}
                                        {{ trans("laravel-h5p.content.display_copyright_button") }}
                                    </label>
                                </li>
                                @endif

                            </ul>
                        </div>

                    </div>

                </div>
                @endif
                
                
                <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                    <a href="{{ route('h5p.index') }}" class="btn btn-default"><i class="fa fa-reply"></i> {{ trans('laravel-h5p.content.cancel') }}</a>

                    {{ Form::submit(trans('laravel-h5p.content.save'), [
                "class"=>"btn btn-primary",
                "data-loading-text" => trans('laravel-h5p.content.saving')
                        ]) }}

                </div>

            </div>


            </fieldset>


            

            {!! Form::close() !!}

        </div>

    </div>

</div>

@endsection

@push( 'h5p-header-script' )
{{--    core styles       --}}
@foreach($settings['core']['styles'] as $style)
{{ Html::style($style) }}
@endforeach
@endpush

@push( 'h5p-footer-script' )
<script type="text/javascript">
    H5PIntegration = {!! json_encode($settings) !!};
</script>

{{--    core script       --}}
@foreach($settings['core']['scripts'] as $script)
{{ Html::script($script) }}
@endforeach

@endpush