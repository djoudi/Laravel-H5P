/* 
 *
 * @Project        
 * @Copyright      Djoudi
 * @Created        2018-02-20
 * @Filename       laravel-h5p.js
 * @Description    
 *
 */

(function ($) {
    ns.init = function () {
        ns.$ = H5P.jQuery;

        if (H5PIntegration !== undefined && H5PIntegration.editor !== undefined) {
            ns.basePath = H5PIntegration.editor.libraryUrl;
            ns.fileIcon = H5PIntegration.editor.fileIcon;
            ns.ajaxPath = H5PIntegration.editor.ajaxPath;
            ns.filesPath = H5PIntegration.editor.filesPath+ '/editor';  // test to add /editor
            ns.apiVersion = H5PIntegration.editor.apiVersion;
            // Semantics describing what copyright information can be stored for media.
            ns.copyrightSemantics = H5PIntegration.editor.copyrightSemantics;
            // Required styles and scripts for the editor
            ns.assets = H5PIntegration.editor.assets;
            // Required for assets
            ns.baseUrl = H5PIntegration.baseUrl;
            ns.metadataSemantics = H5PIntegration.editor.metadataSemantics
            if (H5PIntegration.editor.nodeVersionId !== undefined) {
                ns.contentId = H5PIntegration.editor.nodeVersionId;
            }

            var h5peditor;
            var $upload = $('.laravel-h5p-upload').parents('.laravel-h5p-upload-container');
            var $editor = $('#laravel-h5p-editor');
            var $create = $('#laravel-h5p-create').hide();
            var $type = $('.laravel-h5p-type');
            var $params = $('#laravel-h5p-parameters');
            var $library = $('#laravel-h5p-library');
            var library = $library.val();

            $type.change(function () {
                if ($type.filter(':checked').val() === 'upload') {
                    $create.hide();
                    $upload.show();
                } else {
                    $upload.hide();
                    if (h5peditor === undefined) {
                        h5peditor = new ns.Editor(library, $params.val(), $editor[0]);
                    }
                    $create.show();
                }
            });

            if ($type.filter(':checked').val() === 'upload') {
                $type.change();
            } else {
                $type.filter('input[value="create"]').attr('checked', true).change();
            }

            $('#laravel-h5p-form').submit(function () {
                if (h5peditor !== undefined) {
                    var params = h5peditor.getParams();

                    if (params !== undefined) {
                        $library.val(h5peditor.getLibrary());
                        $params.val(JSON.stringify(params));
                    } else {
                        return false;
                    }
                }

                $(this).find('.btn').button('loading');
            });

            // Title label
            var $title = $('#laravel-h5p-title');
            var $label = $title.prev();
            $title.focus(function () {
                $label.addClass('screen-reader-text');
            }).blur(function () {

                if ($title.val() === '') {
                    ns.getAjaxUrl('libraries')
                    $label.removeClass('screen-reader-text');
                }
            }).focus();

            // Delete confirm
            $('#laravel-h5p-destory').click(function () {
                return confirm(H5PIntegration.editor.deleteMessage);
            });
        }


    }


    ns.getAjaxUrl = function (action, parameters) {
        var url = H5PIntegration.editor.ajaxPath + action + '/?';
        var request_params = [];

        if (parameters !== undefined) {
            for (var property in parameters) {
                if (parameters.hasOwnProperty(property)) {
                    request_params.push(encodeURIComponent(property) + "=" + encodeURIComponent(parameters[property]));
                }
            }
        }
        return url + request_params.join('&');
    };



    $(document).ready(ns.init);


})(H5P.jQuery);
