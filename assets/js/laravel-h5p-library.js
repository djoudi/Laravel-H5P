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
   
    $(document).ready(function () {

   
        $(document).on("click", ".laravel-h5p-restricted", function (e) {

            var $this = $(this);

            $.ajax({
                url: "{{ route('laravel-h5p.library.restrict') }}",
                data: {id: $this.data('id'), selected: $this.is(':checked')},
                success: function (response) {
                    alert('Changed');
                }
            });

        });

        $(document).on("click", ".laravel-h5p-destory", function (e) {

            var $this = $(this);
            if (confirm("Are you sure you want to delete this library?")) {
                $.ajax({
                    url: "{{ route('laravel-h5p.library.destory') }}",
                    data: {id: $this.data('id')},
                    success: function (response) {
                        //                        alert('Deleted');
                        if (response.msg) {
                            alert(response.msg);
                        }
                    }
                });

            }

        });

    });
    
})(H5P.jQuery);