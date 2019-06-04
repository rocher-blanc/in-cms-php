$(function() {
    init('body');
});

init = function( base ) {
    deleteImageLib( base );
    uploadImageLib();
};

uploadImageLib = function() {
    $("#form-upload-gallery").dropzone({
        complete: function() {
            location.reload();
        },
        addedfile: function() {
            $('.dz-message span').html( $('.dz-message').data('uploadmsg') );
            $('.dz-message i').removeClass('icon-cloud-download').addClass('icon-refresh2 spin');
        }
    });
};

deleteImageLib = function(base) {
    if ( $(base + ' .deletelibimage').length ) {
        $(base + ' .deletelibimage').bind('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $div = $(this).parent();

            $.ajax({
                url: $(this).attr('href'),
                type: "get",
                dataType: 'json',
                success: function( data ) {
                    Notify(data.msg, data.result);

                    if ( data.result == true ) {
                        $div.fadeOut(400, function(){
                            $div.remove();
                        });
                    }
                }
            });
        });
    }
};