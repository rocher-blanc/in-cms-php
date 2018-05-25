$(function() {
    checkEditor();
    checkForm();
    checkBox();
    initCounterString();
    initDatePicker();
    initSelect();
    deleteValueMedia();
});

checkBox = function() {
    if ( $('select[data-plugin-selectPicker]').length ) {
        $('select[data-plugin-selectPicker]').selectpicker({
            iconBase: '',
            tickIcon: 'icon-line-check'
        });
    }
};

checkForm = function() {
    $('form').not('.submitReady').bind('submit', function(e) {
        if ( $('.form-process').length ) {
            $('.form-process').show();
        }

        $('.ed_field').removeClass('error');
        var $form = $(this);

        if ( $('#form-module').length ) {
            var serialize = $('#form-module form.submitReady').serialize();
        }
        else {
            var serialize = $form.serialize();
        }

        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: serialize,
            success: function(data) {
                if(data.result == true && data.url != '') {
                    redirect( data.url );
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

                if ( $('.form-process').length ) {
                    $('.form-process').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Notify(errorThrown, false);
                $('.form-process').hide();
            }
        });

    }).addClass('submitReady');
};

initDatePicker = function() {
    /* On met par defaut la langue FR pour le date picker */
    if ( $("input[data-plugin-datepicker]").length ) {
        $("input[data-plugin-datepicker]").datepicker({
            language: 'fr',
            autoclose: true,
            todayHighlight: true
        });
    }

    if ( $(".date-range").length ) {
        $(".date-range").datepicker({
            language: 'fr',
            autoclose: true,
            todayHighlight: true,
            inputs: $(".date-range input")
        });
    }
};

initCounterString = function() {
    if ( $(".input-group input[data-plugin-counter]").length ) {
        $(".input-group input[data-plugin-counter]").each(function() {
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

checkEditor = function() {
    if ( $('.wysiwyg').length ) {
        $('.wysiwyg').summernote({
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

uploadImage = function() {
    var token = $("meta[name=token]").attr("content") ;

    if ( $('#fileupload').length ) {
        $('#fileupload').fileupload({
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

        $(d).on('click', '.actionclickmedia', function (e) {
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

        $(d).on('click', '.deleteMedia', function (e) {
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

cropImage = function() {
    if ( $('#cropimage').length ) {
        var ratio = $('#cropimage').data('width') / $('#cropimage').data('height');
        var maxWidth = 1000;

        $('#cropimage').Jcrop({
            boxWidth: maxWidth,
            aspectRatio: ratio,
            onChange: updateCoordsCrop,
            onSelect: updateCoordsCrop,
            minSize: [$('#cropimage').data('width'),$('#cropimage').data('height')],
            setSelect: [0,0,$('#cropimage').data('width'),$('#cropimage').data('height')]
        });

        $('.saveCrop').click(function() {
            $('.saveCrop').parent().html('Sauvegarde en cours ...').addClass('t700 flash animated');

            $.ajax({
                url: $('#formCropImage').attr('action'),
                type: $('#formCropImage').attr('method'),
                data: $('#formCropImage').serialize(),
                dataType: 'json',
                success: function( ret ) {
                    if ( ret.result == true ) {
                        Notify(ret.msg, ret.result);
                    }

                    var img = $('#' + $('#cropimage').data('fieldid') );
                    var timestamp = Math.round(+new Date()/1000);

                    img.attr( 'src' , img.data('url') + '?' + timestamp ) ;

                    $(this).unbind();
                    $.magnificPopup.close();
                }
            });
        });
    }
};

updateCoordsCrop = function( c ) {
    if ( $('#modal-media canvas').width() < 1000 ) {
        $('#modal-media').css('width', $('.jcrop-holder img').width() );
    }

    var Rx = 1;
    var Ry = 1;

    //var Rx = $('#cropimage').width() / $('.jcrop-holder img').width();
    //var Ry = $('#cropimage').height() / $('.jcrop-holder img').height();

    $('#crop_x').val(c.x * Rx);
    $('#crop_y').val(c.y * Ry);
    $('#crop_x2').val(c.x2 * Rx);
    $('#crop_y2').val(c.y2 * Ry);
    $('#crop_width').val( $('#cropimage').data('width') );
    $('#crop_height').val( $('#cropimage').data('height') );

    $('.jcrop-active').css('width', $('.jcrop-holder img').width() ) ;
    $('.jcrop-active').css('height', $('.jcrop-holder img').height() ) ;

    // $('.saveCrop').removeClass('hide').show();
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

deleteValueMedia = function() {
    $('.deleteMediaBtn').click(function() {
        var fieldid = $(this).data('fieldid') ;
        $('#' + fieldid ).val('');
        $(this).hide();
        $('#bloc_media_' + fieldid + ' .card-img-top').attr('src' , site + '/assets/vendor/cmsmedias/canvas/img/nophoto.png');
    });
};