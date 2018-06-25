$(function() {
    if ( $('.upload').length ) {
        $('.upload').each(function(){
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
                showRemove: true,

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
                console.log('test');
                console.log( files );
                console.log( files.response );
                if ( files.response.error == true ) {
                    $('#errorImport').html(files.response.message);
                }
                else {
                    redirect( $this.data('redirect') );
                }
            });
        });
    }
});