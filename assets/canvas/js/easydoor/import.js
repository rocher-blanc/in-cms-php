$(function() {
    if ( $('.upload-import').length ) {
        $('.upload-import').each(function(){
            var $this = $(this);
            var tvalue = $("meta[name=token]").attr("content") ;

            $this.fileinput({
                language: 'fr',
                uploadUrl: $this.data('uploadurl'),
                mainClass: "input-group-md upload-image",

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
                browseLabel: "Importer votre fichier",
                browseClass: "button button-rounded",
                browseIcon: "<i class=\"icon-download\"></i> ",
            }).on("filebatchselected", function(event, files) {
                $this.fileinput("upload");
            }).on("fileuploaded", function(event, files) {
                if ( files.response.result == false ) {
                    $('#errorImport').html(files.response.message);
                }
                else {
                    redirect( $this.data('redirect') );
                }
            });
        });
    }
});