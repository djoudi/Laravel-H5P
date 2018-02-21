/* 
 *
 * @Project        
 * @Copyright      leechanrin
 * @Created        2017-04-05 오전 11:42:35 
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
                    alert('변경되었습니다');
                }
            });

        });

        $(document).on("click", ".laravel-h5p-destory", function (e) {

            var $this = $(this);
            if (confirm("해당 라이브러리를 삭제하시겠습니까?")) {
                $.ajax({
                    url: "{{ route('laravel-h5p.library.destory') }}",
                    data: {id: $this.data('id')},
                    success: function (response) {
                        //                        alert('삭제되었습니다');
                        if (response.msg) {
                            alert(response.msg);
                        }
                    }
                });

            }

        });

    });
    
})(H5P.jQuery);