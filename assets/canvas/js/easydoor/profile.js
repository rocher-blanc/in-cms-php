$(function() {
    if ( $('.upload-photo').length ) {
        $('.upload-photo').each(function(){
            var $this = $(this);
            var myForm = $this.closest('form');
            var tvalue = $("meta[name=token]").attr("content") ;

            $this.fileinput({
                language: 'fr',
                uploadUrl: $this.data('uploadurl'),
                mainClass: "input-group-md upload-image",

                allowedFileExtensions: ["jpeg", "jpg", "png", "gif"],
                uploadExtraData:{
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
                browseClass: "button button-mini button-rounded",
                browseIcon: "<i class=\"icon-picture\"></i> ",
                removeClass: "button button-mini button-rounded delete-img-" + $this.data('fieldname') + " button-red",
                removeLabel: "Supprimer",
                removeIcon: "<i class=\"icon-trash\"></i> "
            }).on("filebatchselected", function(event, files) {
                myForm.find('.form-process').fadeIn();
                $this.fileinput("upload");
            }).on("fileuploaded", function(event, files) {
                $('#photo').val(files.response.name);
                $('#avatarview').attr('src',files.response.url);
                myForm.find('.form-process').fadeOut();
                myForm.find('.kv-upload-progress').hide();
            }).on('fileclear', function(event, id, index) {
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
});