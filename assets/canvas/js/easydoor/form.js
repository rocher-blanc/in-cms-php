$(function() {
    init('body');
});

init = function( base ) {
    checkEditor( base );
    checkForm( base );
    checkBox( base );
    initCounterString( base );
    initDatePicker( base );
    initSelect( base );
    initLink( base );
    deleteValueMedia( base );
    checkImage( base );
    checkDocument( base );
    modalDepedency('body');
    tableDepedency( base );
};

modalDepedency = function ( base ) {
    if( $(base).find('a[data-modal="true"]').length > 0 ) {
        $(base).find('a[data-modal="true"]').magnificPopup({
            type: 'ajax',
            closeBtnInside: false,
            callbacks: {
                ajaxContentAdded: function(mfpResponse) {
                    SEMICOLON.widget.loadFlexSlider();
                    SEMICOLON.initialize.resizeVideos();
                    SEMICOLON.widget.masonryThumbs();
                    init('#myModal1');
                    checkboxSwitch('#myModal1');
                    if ( $('#myModal1 .tab-nav a').length ) {
                        $('#myModal1 .tab-nav a:first').tab('show');
                        console.log($('#myModal1 .tab-nav a'));
                    }
                },
                open: function() {
                    $('body').addClass('ohidden');
                },
                close: function() {
                    $('body').removeClass('ohidden');
                }
            }
        });
    }
};

tableDepedency = function( base ) {
    loadTable( base + ' .depedencyContent' );
};

loadTable = function( className ) {
    $( className ).each(function() {
        var $div = $(this);
        $.ajax({
            url: $div.data('table') ,
            type: 'GET',
            success: function(html) {
                $div.html( html ) ;
                listenFormTable( $div.attr('id') );
                modalDepedency('#' + $div.attr('id'));
            }
        });
    });
};

checkBox = function( base ) {
    if ( $(base + ' select[data-plugin-selectPicker]').length ) {
        $(base + ' select[data-plugin-selectPicker]').selectpicker({
            iconBase: '',
            tickIcon: 'icon-line-check'
        });
    }
};

checkImage = function(base) {
    if ( $(base + ' input[data-upload-image]').length ) {
        $(base + ' input[data-upload-image]').each(function(){
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
                showRemove: true,

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
                $("#" + $this.data('fieldname')).val(files.response.id);
                $("#" + $this.data('imgname')).attr('src', files.response.mini);
                myForm.find('.form-process').fadeOut();
                myForm.find('.kv-upload-progress').hide();

                $('.delete-img-' + $this.data('fieldname') ).click(function() {
                    $("#" + $this.data('fieldname')).val('');
                    $("#" + $this.data('imgname')).attr('src', $("#" + $this.data('imgname')).data('empty') );
                });
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

checkDocument = function(base) {
    if ( $(base + ' input[data-upload-document]').length ) {
        $(base + ' input[data-upload-document]').each(function(){
            var $this = $(this);
            var myForm = $this.closest('form');
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

                maxFileCount: 20,
                browseLabel: "Parcourir",
                browseClass: "button button-mini button-rounded",
                browseIcon: "<i class=\"icon-file\"></i> ",
            }).on("filebatchselected", function(event, files) {
                myForm.find('.form-process').fadeIn();
                $this.fileinput("upload");
            }).on("fileuploaded", function(event, files) {
                myForm.find('.form-process').fadeOut();
                myForm.find('.kv-upload-progress').hide();
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
0
checkForm = function(base) {
    $(base + ' form').not('.submitReady').bind('submit', function(e) {
        var $form = $(this);
        var mod = $form.data('slug');
        if ( $(base + ' .'+mod+'-form-process').length ) {
            $(base + ' .'+mod+'-form-process').show();
        }

        $(base + ' .ed_field').removeClass('error');
        var serialize = $form.serialize();
        //            data: $('#form-module form.submitReady').serialize(),


        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: serialize,
            success: function(data) {
                if(data.result == true && data.url != '') {
                    if ( $form.data('depedency') == true ) {
                        $.magnificPopup.close();
                        Notify(data.msg, data.result);
                        loadTable( '#tabs-' + $form.data('slug') + ' .depedencyContent' );
                    }
                    else {
                        redirect( data.url );
                    }
                }
                else {
                    Notify(data.msg, data.result);
                    if ( data.tab ) $('#onglet-' + data.tab ).click();

                    if ( data.field ) {
                        if ( $('#field-' + data.field).find('input[type=text]').length ) {
                            $('#field-' + data.field).find('input[type=text]').addClass('error').focus();
                        }
                        else if ( $('#field-' + data.field).find('textarea').length ) {
                            $('#field-' + data.field).find('textarea').addClass('error').focus();
                        }
                    }

                    if ( data.fields ) {
                        $.each(data.fields, function( index, value ) {
                            $('#field-' + value.field).addClass('error');
                        });
                    }
                }

                if ( $(base + ' .'+mod+'-form-process').length ) {
                    $(base + ' .'+mod+'-form-process').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Notify(errorThrown, false);
                $(base + ' .'+mod+'-form-process').hide();
            }
        });

    }).addClass('submitReady');
};

initDatePicker = function(base) {
    /* On met par defaut la langue FR pour le date picker */
    if ( $(base + " input[data-plugin-datepicker]").length ) {
        $(base + " input[data-plugin-datepicker]").datepicker({
            language: 'fr',
            autoclose: true,
            todayHighlight: true
        });
    }

    if ( $(base + " .date-range").length ) {
        $(base + " .date-range").datepicker({
            language: 'fr',
            autoclose: true,
            todayHighlight: true,
            inputs: $(".date-range input")
        });
    }
};

initCounterString = function(base) {
    if ( $(base + " .input-group input[data-plugin-counter]").length ) {
        $(base + " .input-group input[data-plugin-counter]").each(function() {
            if ( $(this).val().length > $(this).data('plugin-counter') ) {
                var colorDefaut = "red";
            }
            else {
                var colorDefaut = "black";
            }
            $(this).after( '<span class="input-group-addon counterInput" style="color:' + colorDefaut + ';">' + $(this).val().length + ' / ' + $(this).data('plugin-counter') + '</span>' );
            $(this).keyup(function() {
                var span = $(this).parent().find('.counterInput');
                if ( $(this).val().length > $(this).data('plugin-counter') ) {
                    span.css('color','red');
                }
                else {
                    span.css('color','black');
                }
                span.html( $(this).val().length + ' / ' + $(this).data('plugin-counter') );
            });
        });
    }
};

checkEditor = function(base) {
    if ( $(base + ' .wysiwyg').length ) {
        $(base + ' .wysiwyg').summernote({
            lang: 'fr-FR',
            toolbar: [
                ['hx', ['style']],
                ['style', ['bold', 'italic', 'underline', 'strikethrough', 'color']],
                ['clear', ['clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview']],
                ['misc', ['print']],
            ],
            popover: {
                image: [
                    ['custom', ['imageAttributes']],
                    ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']]
                ],
            },
            imageAttributes:{
                imageDialogLayout:'default', // default|horizontal
                icon:'<i class="note-icon-pencil"/>',
                removeEmpty:false // true = remove attributes | false = leave empty if present
            },
            displayFields:{
                imageBasic:true,  // show/hide Title, Source, Alt fields
                imageExtra:false, // show/hide Alt, Class, Style, Role fields
                linkBasic:true,   // show/hide URL and Target fields for link
                linkExtra:false   // show/hide Class, Rel, Role fields for link
            },
            onCreateLink : function(linkUrl) {
                var has_protocol = /^(https?|s?ftp)\:\/\//.test(linkUrl);
                var is_mailto = /^mailto\:/.test(linkUrl);
                var is_relative = /^\//.test(linkUrl);

                if (!has_protocol && !is_mailto) {
                    return "{{ host }}" + linkUrl;
                }
                else {
                    return linkUrl;
                }
            },
            callbacks: {
                onImageUpload: function (files) {
                    let form_data = new FormData();
                    let file = files[0];
                    form_data.append('filewysiwyg', file);
                    let $this = $(this);
                    $.ajax({
                        url: "/ajax/wysiwyg.php?dir=" + $this.attr('data-dir'),
                        data: form_data,
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            $this.summernote("insertImage", data);
                        }
                    });
                }
            }
        });
    }
};

uploadImage = function(base) {
    var token = $("meta[name=token]").attr("content") ;

    if ( $(base + ' #fileupload').length ) {
        $(base + ' #fileupload').fileupload({
            url: $(this).data('url'),
            dataType: 'json',
            formData: {csrf_token: token, min_width: $(this).data('minwidth'), min_height: $(this).data('minheight')},
            done: function (e, data) {
                if ( data._response.result.files[0].error != undefined ) {
                    Notify(data._response.result.files[0].error, false);
                }
                else {
                    $.ajax({
                        url: $(this).data('postaction'),
                        type: "post",
                        dataType: 'json',
                        data: "csrf_token=" + token + "&field=" + $(this).data('field') + '&data=' + JSON.stringify(data._response.result.files),
                        success: function( json ) {
                            parseJsonMedia( json ) ;
                        }
                    });
                }
            },
            progressall: function (e, data) {
                $('#noimagemsg').hide();
                $('#portfolio').hide();
                $('.loadingmedia').show();
            }
        }).prop('disabled', !$.support.fileInput).parent().addClass($.support.fileInput ? undefined : 'disabled');

        $(d).on('click', base + ' .actionclickmedia', function (e) {
            $('.loadingmedia').show();
            $('#portfolio').hide();

            $.ajax({
                url: $(this).data('postaction'),
                type: "POST",
                dataType: 'json',
                data: "csrf_token=" + token + "&field=" + $('#fileupload').data('field') + '&dataid=' + $(this).data('id'),
                success: function( json ) {
                    parseJsonMedia( json ) ;
                }
            });
        });

        $(d).on('click', base + ' .deleteMedia', function (e) {
            e.preventDefault();
            var my = $(this);
            var article = $(this).parent().parent().parent();
            $.ajax({
                url: $(this).attr('href'),
                type: "get",
                dataType: 'json',
                success: function( data ) {
                    Notify(data.msg, data.result);

                    if ( data.result == true ) {
                        article.fadeOut(400, function(){
                            $(this).remove();
                            if ( $('#content-media .imagemedia').length == 0 ) {
                                $('#noimagemsg').show();
                            }
                        });

                        var id_field = $('#fileupload').data('fieldid');

                        if ( my.data('id') == $('#' + id_field ).val() ) {
                            $('#bloc_media_' + id_field + ' img').attr('src', site + '/assets/vendor/cmsmedias/canvas/img/nophoto.png');
                            $('#bloc_media_' + id_field + ' img').data('url', '');
                            $('#' + id_field ).val('');
                            $('#bloc_media_' + id_field + ' .card-crop').hide();
                        }
                    }
                }
            });
        });
    }
};

parseJsonMedia = function( json ) {
    $.each(json.files, function (index, row) {
        $('#' + row.key ).attr('src', row.file );
        $('#' + row.key ).data('url', row.file );
    });

    $('#' + json.id.key ).val( json.id.value );

    $('.card-crop').show();

    $('.openCrop').each(function() {
        $(this).attr('href', json.id.linkCrop );
    });

    var fieldid = $('#fileupload').data('fieldid') ;
    if ( $('#bloc_media_' + fieldid + ' .deleteMediaBtn' ).length ) {
        $('#bloc_media_' + fieldid + ' .deleteMediaBtn' ).show();
    }

    $.magnificPopup.close();
};

deleteValueMedia = function(base) {
    $(base + ' .deleteMediaBtn').click(function() {
        var fieldid = $(this).data('fieldid') ;
        $('#' + fieldid ).val('');
        $(this).hide();
        $('#bloc_media_' + fieldid + ' .card-img-top').attr('src' , site + '/assets/vendor/cmsmedias/canvas/img/nophoto.png');
    });
};

/*
#####################################################################################################################################
#####################################################        LINK         ###########################################################
#####################################################################################################################################
*/

initLink = function(base) {
    if ( $(base + ' .input-link').length ) {
        $(base + " .dropdown-menu.link").on('click', 'li a', function(){
            $( "#" + $(this).data('menu') ).html( $(this).text() + ' <span class="caret"></span>' );
            $( "#" + $(this).data('hidden') ).val( $(this).text() );
        });

        $(base + ' .input-link').change(function() {
            updateLink( $(this) );
        });

        $(base + ' .input-link').each(function() {
            updateLink( $(this) );
        });
    }
};

updateLink = function( str ) {
    if ( str.val().indexOf("https") !== -1 ) {
        str.val(str.val().replace('https://', ''));
        $( "#" + str.data('menu') ).html( 'https:// <span class="caret"></span>' );
        $( "#" + str.data('hidden') ).val( 'https://' );
    }

    if ( str.val().indexOf("http") !== -1 ) {
        str.val(str.val().replace('http://', ''));
        $( "#" + str.data('menu') ).html( 'http:// <span class="caret"></span>' );
        $( "#" + str.data('hidden') ).val( 'http://' );
    }

    if ( str.val().substr( str.val().length - 1, 1) == '/' ) {
        str.val( str.val().substr(0, str.val().length - 1) );
    }
};