/* 
 *
 * @Project        
 * @Copyright      Djoudi
 * @Created        2018-02-20
 * @Filename       laravel-h5p-editor.js
 * @Description    
 *
 */
(function ($) {
    
    // setting for inside editor
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': window.parent.Laravel.csrfToken
        },
        dataType: 'json',
    });

})(H5P.jQuery);
