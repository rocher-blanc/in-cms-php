$(function() {
    init('body');
});

init = function( base ) {
    checkImageLib( base );
    deleteImageLib( base );
};

checkImageLib = function(base) {
    if ( $(base + ' input[data-upload-image]').length ) {
        $(base + ' input[data-upload-image]').each(function(){
            var $this = $(this);
            var myForm = $this.closest('div.field');
            var tvalue = $("meta[name=token]").attr("content") ;

            $this.fileinput({
                language: 'fr',
                uploadUrl: $this.data('uploadurl'),
                mainClass: "input-group-md upload-image",

                allowedFileExtensions: ["jpeg", "jpg", "png", "gif"],
                uploadExtraData:{
                    model:1,
                    field:$this.data('field'),
                    csrf_token:tvalue
                },

                showCaption: false,
                showRemove: false,

                showUpload: false,
                showPreview: false,
                showCancel: false,
                showProgress: false,

                maxFileCount: 1,
                autoReplace: true,
                browseLabel: "Parcourir",
                browseClass: "button button-rounded",
                browseIcon: "<i class=\"icon-line-upload\"></i> ",
            }).on("filebatchselected", function(event, files) {
                myForm.find('.form-process').fadeIn();
                $this.fileinput("upload");
            }).on("fileuploaded", function(event, files) {
                myForm.find('.form-process').fadeOut();
                myForm.find('.kv-upload-progress').hide();
                location.reload();
            }).on('fileclear', function(event, id, index) {
                $($this.attr('data-bdd')).val('');
                myForm.find('.form-process').fadeOut();
            }).on('fileerror', function(event, id, index) {
                myForm.find('.form-process').fadeOut();
            }).on('filebatchuploaderror', function(event, id, index) {
                myForm.find('.form-process').fadeOut();
            }).on('filebeforedelete', function() {
                console.log('test filebeforedelete');
            }).on('filedeleted', function() {
                console.log('test filedeleted');
            });
        });
    }
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