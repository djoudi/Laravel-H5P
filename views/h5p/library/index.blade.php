@extends( config('laravel-h5p.layout') )

@section( 'h5p' )
<div class="container-fluid">

    <div class="row">

        <div class="col-md-9">

            <div class="panel panel-primary">

                {!! Form::open(['route' => ['h5p.library.store'], 'id'=>'h5p-library-form', 'class'=>'form-horizontal', 'enctype'=>"multipart/form-data"]) !!}
                <div class="panel-body">

                    <div class="form-group {{ $errors->has('h5p_file') ? 'has-error' : '' }}" style="margin-bottom: 0px;">
                        <label for="inputTitle" class="control-label col-md-3">{{ trans('laravel-h5p.library.upload_libraries') }}</label>
                        <div class="col-md-9">
                            <input type="file" name="h5p_file" id="h5p-file" class="form-control">

                            <ul class="list-unstyled" style="margin:10px 0px 0px;">
                                <li>
                                    <label for="h5p-upgrade-only" class="">
                                        <input type="checkbox" name="h5p_upgrade_only" id="h5p-upgrade-only">

                                        {{ trans('laravel-h5p.library.only_update_existing_libraries') }}</label>
                                </li>
                                <li> <label for="h5p-disable-file-check" class="">
                                        <input type="checkbox" name="h5p_disable_file_check" id="h5p-disable-file-check">

                                        {{ trans('laravel-h5p.library.upload_disable_extension_check') }}</label>
                                </li>
                            </ul>
                            @if ($errors->has('h5p_file'))
                            <span class="help-block">
                                {{ $errors->first('h5p_file') }}
                            </span>
                            @endif
                        </div>
                    </div>

                </div>

                <div class="panel-footer">
                    <input type="submit" name="submit" value="{{ trans('laravel-h5p.library.upload') }}" class="btn btn-primary">
                </div>
                {!! Form::close() !!}

            </div>

        </div>
        <div class="col-md-3">
            <div class="panel panel-primary">

                {!! Form::open(['route' => ['h5p.library.clear'], 'id'=>'laravel-h5p-update-content-type-cache', 'class'=>'form-horizontal', 'enctype'=>"multipart/form-data"]) !!}


                <div class="panel-body">
 <!--<p>Making sure the content type cache is up to date will ensure that you can view, download and use the latest libraries. This is different from updating the libraries themselves.</p>-->

                    <h4>{{ trans('laravel-h5p.library.content_type_cache') }}</h4>
<!--                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row">Last update</th>
                                <td>{{ $last_update }}</td>
                            </tr>
                        </tbody>
                    </table>-->



                </div>

                <div class="panel-footer">
                    <input type="hidden" id="sync_hub" name="sync_hub" value="">
                    <input type="submit" name="updatecache" id="updatecache" class="btn btn-danger btn-large" value="{{ trans('laravel-h5p.library.clear') }}">
                </div>
                {!! Form::close() !!}

            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-md-12">


            <p class="form-control-static">
                {{ trans('laravel-h5p.library.search-result', ['count' => count($entrys)]) }}
            </p>

            <table class="table text-middle text-center h5p-lists">
                <colgroup>
                    <col width="*">
                    <col width="8%">
                    <col width="8%">
                    <col width="8%">
                    <col width="10%">
                    <col width="10%">
                    <col width="15%">
                </colgroup>

                <thead>
                    <tr class="active">
                        <th class="text-left">{{ trans('laravel-h5p.library.name') }}</th>
                        <th class="text-center">{{ trans('laravel-h5p.library.version') }}</th>
                        <th class="text-center">{{ trans('laravel-h5p.library.restricted') }}</th>
                        <th class="text-center">{{ trans('laravel-h5p.library.contents') }}</th>
                        <th class="text-center">{{ trans('laravel-h5p.library.contents_using_it') }}</th>
                        <th class="text-center">{{ trans('laravel-h5p.library.libraries_using_it') }}</th>
                        <th class="text-center">{{ trans('laravel-h5p.library.actions') }}</th>
                    </tr>
                </thead>

                <tbody>

                    @unless(count($entrys) >0)
                    <tr><td colspan="6" class="h5p-noresult">{{ trans('laravel-h5p.common.no-result') }}</td></tr>
                    @endunless

                    @foreach($entrys as $entry)
                    <tr>
                        <td class="text-left">
                            <p class="form-control-static">{{ $entry->title }}</p>
                        </td>
                        <td class="text-center">
                            <p class="form-control-static">{{ $entry->major_version.'.'.$entry->minor_version.'.'.$entry->patch_version }}</p>
                        </td>

                        <td class="text-center">

                            <input type="checkbox" value="{{ $entry->restricted }}"
                                   @if($entry->restricted == '1')
                                   checked=""
                                   @endif
                                   class="laravel-h5p-restricted" data-id="{{ $entry->id }}">

                        </td>

                        <td class="text-center">
                            <p class="form-control-static">{{ number_format($entry->numContent()) }}</p>
                        </td>
                        <td class="text-center">
                            <p class="form-control-static">{{ number_format($entry->getCountContentDependencies()) }}</p>
                        </td>
                        <td class="text-center">
                            <p class="form-control-static">{{ number_format($entry->getCountLibraryDependencies()) }}</p>
                        </td>

                        <td class="text-center">
                            <button class="btn btn-danger laravel-h5p-destory" data-id="{{ $entry->id }}">{{ trans('laravel-h5p.library.remove') }}</button>
                        </td>
                    </tr>
                    @endforeach

                 {!! $entrys->links() !!}
                </tbody>
            </table>

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
    H5PAdminIntegration = {!! json_encode($settings) !!};
</script>

{{--    core script       --}}
@foreach($required_files['scripts'] as $script)
{{ Html::script($script) }}
@endforeach



<script type="text/javascript">

    (function ($) {

        $(document).ready(function () {

            $(document).on("click", ".laravel-h5p-restricted", function (e) {
                var $this = $(this);
                $.ajax({
                    url: "{{ route('h5p.library.restrict') }}",
                    data: {id: $this.data('id'), selected: $this.is(':checked')},
                    success: function (response) {
                        alert("{{ trans('laravel-h5p.library.updated') }}");
                    }
                });
            });

            $(document).on("submit", "#laravel-h5p-update-content-type-cache", function (e) {
                if(confirm("{{ trans('laravel-h5p.library.confirm_clear_type_cache') }}")) {
                        return true;
                }else{
                        return false;
                }
            });

            $(document).on("click", ".laravel-h5p-destory", function (e) {

                    var $obj = $(this);
                    var msg = "{{ trans('laravel-h5p.library.confirm_destroy') }}";
                    if (confirm(msg)) {

                        $.ajax({
                            url: "{{ route('h5p.library.destroy') }}",
                            data: {id: $obj.data('id')},
                            method: "DELETE",
                            success: function (response) {
                                    if (response.msg) {
                                        alert(response.msg);
                                    }
                                    location.reload();
                            },
                            error: function () {
                                alert("{{ trans('laravel-h5p.library.can_not_destroy') }}");
                                location.reload();
                            }
                        })
                    }

            });

        });

    })(H5P.jQuery);

</script>

@endpush
