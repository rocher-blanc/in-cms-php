/*
    Version 1.01.2
 */

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
    initIcon( base );
    deleteValueMedia( base );
    checkImage( base );
    checkDocument( base );
    checkGallery( base );
    modalDepedency('body');
    tableDepedency( base );
    formDepedency( base );
    checkVideo( base );
};

modalConfig = function( onglet ) {
    return {
        type: 'ajax',
        closeBtnInside: false,
        callbacks: {
            ajaxContentAdded: function(mfpResponse) {
                SEMICOLON.widget.loadFlexSlider();
                SEMICOLON.initialize.resizeVideos();
                SEMICOLON.widget.masonryThumbs();
                checkboxSwitch('#myModal1');
                init('#myModal1');
                if( $("#myModal1 .tabs").length ) {
                    $("#myModal1 .tabs").tabs();
                }

                if ( typeof onglet !== 'undefined' ) {
                    $('#myModal1 #' + onglet ).trigger('click');
                }
            },
            open: function() {
                $('body').addClass('ohidden');
            },
            close: function() {
                $('body').removeClass('ohidden');
            }
        }
    };
};

modalDepedency = function ( base ) {
    if( $(base).find('a[data-modal="true"]').length > 0 ) {
        $(base).find('a[data-modal="true"]').magnificPopup( modalConfig() );
    }
};

formDepedency = function( base ) {
    loadForm( base + ' .depedencyForm' );
};

loadForm = function( className ) {
    $( className ).each(function() {
        var $div = $(this);
        $.ajax({
            url: $div.data('form') ,
            type: 'GET',
            success: function(html) {
                $div.html( html ) ;
                checkboxSwitch('#' +  $div.attr('id') );
                init('#' +  $div.attr('id') );
            }
        });
    });
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
            tickIcon: 'icon-line-check',
            countSelectedText: function(num) {
                if (num === 0) {
                    return 'Aucune sélection';
                }
                else if (num > 1) {
                    return '{0} options sélectionnées';
                }
            }
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
                browseIcon: "<i class=\"icon-line-upload\"></i> ",
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
        checkDeleteDocument();

        $(base + ' input[data-upload-document]').each(function(){
            var $this = $(this);
            var myForm = $this.closest('form');
            var tvalue = $("meta[name=token]").attr("content") ;
            var fieldname = $this.data('hidden') ;

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

                maxFileCount: 100,
                uploadAsync: false,
                browseLabel: "Parcourir",
                browseClass: "button button-mini button-rounded",
                browseIcon: "<i class=\"icon-file\"></i> ",
            }).on("filebatchselected", function(event, files) {
                myForm.find('.form-process').fadeIn();
                $this.fileinput("upload");
            }).on("filebatchuploadsuccess", function(event, files) {
                myForm.find('.kv-upload-progress').hide();

                $(files.response).each(function(index, data) {
                    $('<li id="document_{{ doc.id }}">\n' +
                        '            <div class="input-group">\n' +
                        '                <span class="input-group-addon">\n' +
                        '                    <i class="fa fa-file'+data.ico.class+'-o" title="'+data.name+'" style="cursor: help;"></i>\n' +
                        '                </span>\n' +
                        '                <input type="hidden" name="'+fieldname+'['+data.id+']" value="'+data.id+'">\n' +
                        '                <input type="text" placeholder="'+data.name+'" class="form-control" name="'+fieldname+'_alt['+data.id+']" id="id_'+fieldname+'_alt['+data.id+']" value="" />\n' +
                        '                <span class="input-group-addon">\n' +
                        '                    <i class="icon-trash fright deletedoc" data-id="'+data.id+'" data-field="'+$this.data('fieldname')+'" data-url="'+data.url+'"></i>\n' +
                        '                </span>\n' +
                        '            </div>\n' +
                        '        </li>').appendTo( $('#list_doc_' + fieldname) );
                });

                checkDeleteDocument();
                myForm.find('.form-process').fadeOut();
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

checkGallery = function(base) {
    if ( $(base + ' input[data-upload-gallery]').length ) {
        checkDeleteGallery();

        $(base + ' input[data-upload-gallery]').each(function(){
            var $this = $(this);
            var myForm = $this.closest('form');
            var tvalue = $("meta[name=token]").attr("content") ;
            var fieldname = $this.data('hidden') ;

            $('#list_gallery_' + fieldname).sortable({
                items: "div:not(.btn-bar)",
                handle: '.movegallery',
                update: function( event, ui ) {
                    var arraySort = $(this).sortable('toArray');
                    $.ajax({
                        url: $('#list_gallery_' + fieldname).data('sortable-url'),
                        type: "post",
                        data: {
                            csrf_token:tvalue,
                            field: $this.data('fieldname'),
                            id: $('#id_element').val(),
                            order: arraySort
                        }
                    });
                }
            });

            $this.fileinput({
                language: 'fr',
                uploadUrl: $this.data('uploadurl'),
                mainClass: "input-group-md upload-image",

                uploadExtraData:{
                    id:$('#id_element').val(),
                    field:$this.data('field'),
                    fieldname:$this.data('fieldname'),
                    csrf_token:tvalue
                },

                showCaption: false,
                showRemove: false,

                showUpload: false,
                showPreview: false,
                showCancel: false,
                showProgress: false,

                maxFileCount: 100,
                uploadAsync: false,
                browseLabel: "Parcourir",
                browseClass: "button button-mini button-rounded",
                browseIcon: "<i class=\"icon-file\"></i> ",
            }).on("filebatchselected", function(event, files) {
                myForm.find('.form-process').fadeIn();
                $this.fileinput("upload");
            }).on("filebatchuploadsuccess", function(event, files) {
                myForm.find('.kv-upload-progress').hide();

                $('#list_gallery_' + fieldname).find('.clear').remove();
                
                $(files.response).each(function(index, data) {
                    $('<div class="mini-img" id="gallery-'+data.id+'">\n' +
                        '    <img src="'+data.image100+'"/>\n' +
                        '    <div class="btn-bar"><i class="icon-trash deletegallery" data-url="'+data.url+'" data-image-id="'+data.id+'"></i>&nbsp;&nbsp;<i class="icon-move movegallery"></i></div>\n' +
                        '</div>').appendTo( $('#list_gallery_' + fieldname) );
                });

                $('<div class="clear"></div>').appendTo( $('#list_gallery_' + fieldname) );

                checkDeleteGallery();

                myForm.find('.form-process').fadeOut();
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

checkDeleteGallery = function() {
    $('.deletegallery').not('.deleteGalleryReady').click(function() {
        var $this = $(this);

        $this.parent().parent().fadeOut(400, function() {
            $this.parent().parent().remove();
        });

        $.ajax({
            url: $this.data('url'),
            type: "post",
            dataType: 'json',
            data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + "&id=" + $this.data('image-id'),
            success: function( json ) {

            }
        });


    }).addClass('deleteGalleryReady');
};

checkDeleteDocument = function() {
    $('.deletedoc').not('.deleteReady').click(function() {
        var $this = $(this);

        $.ajax({
            url: $this.data('url'),
            type: "post",
            dataType: 'json',
            data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&element=' + $('#id_element').val() + '&field=' + $this.data('field'),
            success: function( json ) {
                $('#document_' + $this.data('id') ).remove();
                Notify(json.msg, json.result);
            }
        });
    }).addClass('deleteReady');
};

checkForm = function(base) {
    $(base + ' form').not('.submitReady').bind('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $form = $(this);
        var mod = $form.data('slug');
        if ( $(base + ' .'+mod+'-form-process').length ) {
            $(base + ' .'+mod+'-form-process').show();
        }

        $(base + ' .ed_field').removeClass('error');

        var onglet    = $('#onglet-form-module .ui-state-active a').attr('id');
        var valbutton = $("button[type=submit]:focus").val();
        var serialize = $form.serialize() + "&buttonaction=" + valbutton;

        if ( $form.attr('id') == 'form-seo-module' ) {
            serialize += "&" + $('#form-element-module').serialize();
        }
        else if ( $form.attr('id') == 'form-element-module' && $('#form-seo-module').length == 1 ) {
            serialize += "&" + $('#form-seo-module').serialize();
        }

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            dataType: "json",
            data: serialize,
            success: function(data) {
                if(data.result == true && data.url != '') {
                    if ( $form.data('depedency') == true ) {
                        if ( valbutton == 'stay' ) {
                            $.magnificPopup.close();
                            Notify(data.msg, data.result);
                            loadTable( '#tabs-' + $form.data('slug') + ' .depedencyContent' );

                            var config = modalConfig( onglet ) ;
                            config.items = {
                                src: data.url + "?o=" + onglet
                            };

                            $.magnificPopup.open(config);
                        }
                        else {
                            $.magnificPopup.close();
                            Notify(data.msg, data.result);
                            loadTable( '#tabs-' + $form.data('slug') + ' .depedencyContent' );
                        }
                    }
                    else {
                        if ( valbutton == 'stay' ) {
                            redirect( data.url + "?o=" + onglet );
                        }
                        else {
                            redirect( data.url );
                        }
                    }
                }
                else {
                    Notify(data.msg, data.result);
                    try {
                        if ( data.tab ) {
                            $('#onglet-' + data.tab ).trigger('click');
                        }
                    }
                    catch(e) {};

                    $('.error').removeClass('error');

                    if ( data.field ) {
                        if ( $('#field-' + data.field).find('input[type=text]').length ) {
                            $('#field-' + data.field).find('input[type=text]').addClass('error').focus();
                        }
                        else if ( $('#field-' + data.field).find('textarea').length ) {
                            $('#field-' + data.field).find('textarea').addClass('error').focus();
                        }
                    }

                    if ( typeof data.fields != 'undefined' ) {
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

    $(base + ' form.submitReady[data-condition="true"]').not('.conditionReady').each(function() {
        var $this = $(this);
        var route = $this.data('route-condition');
        var mod = $this.data('slug');

        $this.find('select').change(function() {
            refreshShowIf( base , mod , route , $this );
        });

        $this.find('.bootstrap-switch').on('switchChange.bootstrapSwitch', function (e, data) {
            refreshShowIf( base , mod , route , $this );
        });
    }).addClass('conditionReady');
};

refreshShowIf = function( base , mod , route , $this ) {
    if ( $(base + ' .'+mod+'-form-process').length ) {
        $(base + ' .'+mod+'-form-process').show();
    }
    var serialize = $this.serialize();
    $.ajax({
        url: route,
        type: 'POST',
        data: serialize,
        success: function(data) {
            if ( $(base + ' .'+mod+'-form-process').length ) {
                $(base + ' .'+mod+'-form-process').hide();
            }

            if ( data.tabs && data.tabs.length ) {
                $.each(data.tabs, function(i, tab) {
                    var linktab = $('#tabs-link-' + tab.key );
                    if ( tab.show == true ) {
                        linktab.removeClass('hide').show();
                    }
                    else {
                        linktab.hide();
                    }

                    if ( tab.group && tab.group.length ) {
                        $.each(tab.group, function(i, group) {
                            var eltgrp = $('#group_form_' + group.key );
                            if ( group.show == true ) {
                                eltgrp.removeClass('hide').show();
                            }
                            else {
                                eltgrp.hide();
                            }
                        });
                    }
                });
            }

            if ( data.fields.length ) {
                $.each(data.fields, function(i, elt) {
                    var field = $("div[data-field='"+ elt.name+"']");
                    if ( elt.show == true ) {
                        if ( field.data('show') == 'hidden' ) {
                            field.removeClass('hidden');
                        }
                        else {
                            field.removeClass('hide').show();
                        }
                    }
                    else {
                        if ( field.data('show') == 'hidden' ) {
                            field.addClass('hidden');
                        }
                        else {
                            field.addClass('hide');
                        }
                    }
                });
            }
        }
    });
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

    if ( $(base + " input[data-plugin-datetimepicker]").length ) {
        var today = new Date();
        $(base + " input[data-plugin-datetimepicker]").datetimepicker({
            format: "dd/mm/yyyy - hh:ii",
            autoclose: true,
            todayBtn: true,
            minuteStep: 10,
            startDate: new Date(today.setMinutes(today.getMinutes() + 5)),
            locale: 'fr'
        });
    }

    if ( $(base + " input[data-plugin-olddatetimepicker]").length ) {
        var today = new Date();
        $(base + " input[data-plugin-olddatetimepicker]").datetimepicker({
            format: "dd/mm/yyyy - hh:ii",
            autoclose: true,
            todayBtn: true,
            minuteStep: 10,
            locale: 'fr'
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
        var token = $("meta[name=token]").attr("content") ;

        $(base + ' .wysiwyg').each(function() {
            var $textarea = $(this);

            $textarea.summernote({
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
                        form_data.append('csrf_token', token);
                        let $this = $(this);
                        console.log(form_data);
                        $.ajax({
                            url: $textarea.data('url'),
                            data: form_data,
                            type: "POST",
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                $this.summernote("insertImage", data);
                            }
                        });
                    },
                    onPaste : function(e) {
                        var thisNote = $(this).next(".note-editor").find(".note-editable");
                        setTimeout(function() {
                            thisNote.html( CleanPastedHTML( thisNote.html() ) );
                        }, 50);
                    }
                }
            });
        });
    }
};

// from here : https://github.com/summernote/summernote/issues/303#issuecomment-53713694
CleanPastedHTML = function(input) {
    // 1. remove line breaks / Mso classes
    var stringStripper = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g;
    var output = input.replace(stringStripper, ' ');
    // 2. strip Word generated HTML comments
    var commentSripper = new RegExp('<!--(.*?)-->','g');
    var output = output.replace(commentSripper, '');
    var tagStripper = new RegExp('<(/)*(meta|link|span|\\?xml:|st1:|o:|font)(.*?)>','gi');
    // 3. remove tags leave content if any
    output = output.replace(tagStripper, '');
    // 4. Remove everything in between and including tags '<style(.)style(.)>'
    var badTags = ['style', 'script','applet','embed','noframes','noscript'];

    for (var i=0; i< badTags.length; i++) {
        tagStripper = new RegExp('<'+badTags[i]+'.*?'+badTags[i]+'(.*?)>', 'gi');
        output = output.replace(tagStripper, '');
    }
    // 5. remove attributes ' style="..."'
    var badAttributes = ['style', 'start'];
    for (var i=0; i< badAttributes.length; i++) {
        var attributeStripper = new RegExp(' ' + badAttributes[i] + '="(.*?)"','gi');
        output = output.replace(attributeStripper, '');
    }
    return output;
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
                        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + "&field=" + $(this).data('field') + '&data=' + JSON.stringify(data._response.result.files),
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
                data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + "&field=" + $('#fileupload').data('field') + '&dataid=' + $(this).data('id'),
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

/*
#####################################################################################################################################
#####################################################        ICON         ###########################################################
#####################################################################################################################################
*/

initIcon = function(base) {
    if ( $(base + ' .list-icon').length ) {
        var $container = $(base + ' .list-icon');
        $container.find(".icon").on("click", function() {
            $container.find("input").val( $(this).data("icon") );
            $container.find(".active").removeClass("active");
            $(this).addClass("active");
        });
    }
};

/*
#####################################################################################################################################
#####################################################        VIDEO         ##########################################################
#####################################################################################################################################
*/

getVideoID = function(url) {
    if(url.indexOf('?') != -1 ) {
        var query = decodeURI(url).split('?')[1];
        var params = query.split('&');
        for(var i=0,l = params.length;i<l;i++)
            if(params[i].indexOf('v=') === 0)
                return params[i].replace('v=','');
    }
    else if (url.indexOf('youtu.be') != -1) {
        return decodeURI(url).split('youtu.be/')[1];
    }
    else if (url.indexOf('vimeo.com/') != -1) {
        return decodeURI(url).split('vimeo.com/')[1];
    }
    else if (url.indexOf('dai.ly/') != -1) {
        return decodeURI(url).split('dai.ly/')[1];
    }
    return null;
};

checkVideo = function( base ) {
    if ( $( base ).find("input[data-video]").length ) {
        $( base ).find('input[data-video]').each(function() {
            $(this).change(function() {
                var url = $(this).val();
                var video_id = getVideoID(url);
                var video_div = "#video_" + $(this).attr("id");

                if (video_id != null) {
                    if (url.indexOf('vimeo.com/') != -1) {
                        $(video_div).html('<iframe class="embed-responsive-item" src="//player.vimeo.com/video/' + video_id + '" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
                    }
                    else if (url.indexOf('dai.ly/') != -1) {
                        $(video_div).html('<iframe class="embed-responsive-item" src="//www.dailymotion.com/embed/video/' + video_id + '" allowfullscreen></iframe>');
                    }
                    else {
                        $(video_div).html('<iframe class="embed-responsive-item" src="//www.youtube.com/embed/' + video_id + '" allowfullscreen></iframe>');
                    }
                    $(video_div).show();
                }
                else {
                    $(video_div).html("Aucune vidéo");
                }
            });
            if ($(this).val() != "")
            {
                $(this).trigger("change");
            }
        });
    }
}