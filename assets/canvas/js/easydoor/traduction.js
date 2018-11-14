$(function() {

    $(".translate-cell").dblclick(function() {
        editCell( this );
    });

    addLang( $(".translate-lang-select").find("option").eq(1).val() );


    $("#search-key").on('input', function() {
        var key = $(this).val().trim().toLowerCase();
        var $table = $("#translate-table");
        var $tbody = $table.find("tbody");

        $tbody.find(".highlight-translate").removeClass("highlight-translate");

        if( key.length === 0 ) {
            $tbody.find("tr").show();
        }
        else {
            $tbody.find("tr").each(function(index, tr) {
                $(tr).hide();

                $(tr).find("td").each(function(i, td) {
                    if( $(td).text().toLowerCase().indexOf(key) > -1 ) {
                        $(td).addClass("highlight-translate");
                        $(tr).show();
                    }
                });
            });
        }
    });


    $(".add-key").click(function () {
        var $input = $(this).prev(".form-group").find("input");
        var key = $input.val().trim();

        if( key.length > 0 ) {
            key = key.trim().replace(/ /g, '_').replace(/[\u0300-\u036f]/g, "").toLowerCase();
            var data = {
                new_key: key
            };
            data[ $("meta[name=tokename]").attr("content") ] = $("meta[name=token]").attr("content");

            $.ajax({
                url     : "{{ siteUrl('ext/langue/traduction/add-key') }}",
                data    : data,
                method  : "POST",
                dataType: 'json',
                success: function (response) {

                    Notify( response.msg, response.result );

                    if( response.result ) {
                        addKey( key );

                        $input.val("");

                        $("html, body").animate({
                            scrollTop : $("#translate-table").find("tbody").find("tr").last().offset().top - 100
                        }, 700);
                    }

                }
            });
        }
    });


    $(".translate-lang-select").change(function() {
        var value = $(".translate-lang-select").val();
        if( value !== "" ) {
            addLang(value);
            $(".translate-lang-select").val("").trigger("click");
        }
    });

});


var addCSRF = function( values ) {
    values['{{ csrf_key }}'] = "{{ csrf_token }}";
    return values;
};


var editCell = function( cell ) {
    var $cell       = $(cell);
    var $content    = $cell.find(".content");
    var $editor     = $cell.find(".editor");
//            var type        = getType( $content.find("pre").eq(0).html() );


    if( $editor.html().trim().length === 0 ) {
        $content.hide();

        var $save = $("<button>")
            .attr("type", "button")
            .addClass("ed-button button button-rounded button-green button-mini button-3d")
            .append( $("<i>").addClass("icon-line-check") )
            .append( $("<span>").text("Enregistrer") )
            .click( function() { saveCell(cell); } )
            .appendTo($editor);

        var $cancel = $("<button>")
            .attr("type", "button")
            .addClass("ed-button button button-rounded button-red button-mini button-3d")
            .append( $("<i>").addClass("icon-line-cross") )
            .append( $("<span>").text("Annuler") )
            .click( function() { cancelCell(cell); } )
            .appendTo($editor);

        var $addHtmlBtn = $("<button>")
            .attr("type", "button")
            .addClass("toHTML")
            .addClass("ed-button button button-rounded button-yellow button-mini button-3d")
            .append( $("<i>").addClass("icon-code") )
            .append( $("<span>").text("Ajouter du HTML") )
            .click( function() { setToHTML( $(this).parents(".translate-cell") ) } )
            .appendTo($editor);

        var $removeHtmlBtn = $("<button>")
            .attr("type", "button")
            .addClass("toText")
            .addClass("ed-button button button-rounded button-brown button-mini button-3d")
            .append( $("<i>").addClass("icon-type") )
            .append( $("<span>").text("Supprimer le HTML") )
            .click( function() { setToText( $(this).parents(".translate-cell") ) } )
            .appendTo($editor);

        $("<br>").appendTo($editor);

        var $textarea = $("<textarea>")
            .addClass("form-control")
            .attr("rows", 3)
            .val( $content.find("pre").eq(0).html() )
            .appendTo($editor)
            .focus();

        if( haveHtml( $content.find("pre").eq(0).html() ) ) {
            $addHtmlBtn.hide();
            setWysiwyg($textarea);
        }
        else {
            $removeHtmlBtn.hide();
        }
    }
};


var setToHTML = function( cell ) {
    var $cell       = $(cell);
    var $editor     = $cell.find(".editor");

    $cell.find(".toHTML").hide();
    $cell.find(".toText").show();
    setWysiwyg( $editor.find("textarea") );
};

var setToText = function( cell ) {
    var $cell       = $(cell);
    var $editor     = $cell.find(".editor");
    var $textarea   = $editor.find("textarea");

    $cell.find(".toHTML").show();
    $cell.find(".toText").hide();


    $textarea.summernote('destroy');
    $textarea.val( stripTag($textarea.val()) );
};


var saveCell = function( cell ) {
    var $cell       = $(cell);
    var $content    = $cell.find(".content");
    var $editor     = $cell.find(".editor");

    var value = $editor.find("textarea").val();

    $content.show()
        .find("pre").eq(0).html( value );
    $editor.html("");

    var data = {
        key   : $cell.parents("tr").find("td").eq(0).text(),
        lang  : $cell.attr("data-lang"),
        value : value
    };
    data[ $("meta[name=tokename]").attr("content") ] = $("meta[name=token]").attr("content");

    $.ajax({
        url     : "{{ siteUrl('ext/langue/traduction/update-translate') }}",
        data    : data,
        method  : "POST",
        dataType: 'json',
        success: function (response) {
            Notify( response.msg , response.result );
        }
    });
};


var cancelCell = function( cell ) {
    var $cell       = $(cell);
    var $content    = $cell.find(".content");
    var $editor     = $cell.find(".editor");

    $content.show();
    $editor.html("");
};


var setWysiwyg = function( element ) {
    $(element).summernote({
        lang: 'fr-FR',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'strikethrough', /*'color'*/]],
            ['clear', ['clear']],
            ['para', ['ul', 'ol'/*, 'paragraph'*/]],
            ['insert', ['link'/*, 'picture', 'video'*/]],
            ['view', [/*'fullscreen', */'codeview']],
            ['misc', ['print']],
        ]
    });
};


var addLang = function( lang_locale ) {
    requestGetLang( lang_locale );
};


var addLangColumn = function( lang_locale, title ) {
    var $table = $("#translate-table");
    var $thead = $table.find("thead");
    var $tbody = $table.find("tbody");

    // Add in thead
    var $thead_tr = $thead.find("tr").eq(0);

    var th = $("<th>")
        .attr("data-lang", lang_locale)
        .text( title )
        .appendTo( $thead_tr );

    // Add in tbody
    $tbody.find("tr").each( function(index, tr) {
        var key = $(tr).find("td").attr("data-key");

        createCell( key, lang_locale, "text", "" )
            .appendTo( tr );
    });
};


var addKey = function( key ) {
    $tbody = $("#translate-table tbody");
    var $tr = $("<tr>")
        .appendTo( $tbody);

    var $td = $("<td>")
        .addClass( "td-key" )
        .attr( "data-key", key );
    if( $("meta[name=isadmin]").attr("content") == "1" ) {
        $td.append(
            $("<a>")
                .attr("href", "{{ siteUrl('ext/langue/traduction/remove-key/') }}" + key)
                .attr("data-lightbox", "ajax")
                .append(
                    $("<i>")
                        .addClass("icon-trash")
                )
                .css('margin-right', "5px")
                .css('cursor', "pointer")
                .magnificPopup({
                    type: 'ajax'
                })
        );
    }
    $td.append(
        $("<strong>")
            .text( key )
    )
        .appendTo($tr);

    var lang_number = $("#translate-table thead tr").eq(0).find("th").length - 1;
    for( var i=0; i<lang_number; i++ ) {
        var this_lang = $("#translate-table thead tr").eq(0).find("th").eq(i+1).attr("data-lang");
        createCell( key, this_lang, "" )
            .dblclick( function() {  editCell(this)  } )
            .appendTo($tr);
    }
};


var setTranslate = function( key, lang, content ) {
    var $table = $("#translate-table");
    var $tbody = $table.find("tbody");

    var finded = false;

    $tbody.find("tr").each( function(index, tr) {
        if( $(tr).find("td").eq(0).text().trim().toLowerCase() === key ) {
            var $cell = $(tr).find("td[data-lang="+ lang +"]");

            $cell.find(".content pre").html(content);
            finded = true;
            return true;
        }
    });

    if( ! finded ) {
        addKey( key );
        setTranslate( key, lang, content );
    }
};


var createCell = function( key, lang, content ) {
    return $("<td>")
        .addClass("translate-cell")
        .attr("data-lang", lang)
        .append(  $("<div>").addClass("content").append(  $("<pre>").html( content )  )  )
        .append(  $("<div>").addClass("editor")                                          )
        .dblclick( function() {  editCell(this)  } )
};


var addTranslate = function( key, lang_id ) {

};


var requestGetLang = function( lang_locale ) {
    var data = {
        lang_locale : lang_locale
    };
    data[ $("meta[name=tokename]").attr("content") ] = $("meta[name=token]").attr("content");


    $.ajax({
        url     : "{{ siteUrl('ext/langue/traduction/get-lang') }}",
        data    : data,
        method  : "POST",
        dataType: 'json',
        success: function (response) {

            addLangColumn( response.lang.locale, response.lang.title );

            for( key in response.keys ) {
                var value = response.keys[key];

                setTranslate( key.toLowerCase(), response.lang.locale, value.value );
            }

            console.log( response );
            $(".translate-lang-select").find("option[value='"+response.lang.locale+"']").remove();
            if( $(".translate-lang-select").find("option").length < 2 ) {
                $(".translate-lang-select").hide();
            }
        }
    });
};


var deleteKey = function( key ) {
    var data = {
        key : key
    };
    data[ $("meta[name=tokename]").attr("content") ] = $("meta[name=token]").attr("content");

    $.ajax({
        url     : "{{ siteUrl('ext/langue/traduction/remove-key-action') }}",
        data    : data,
        method  : "POST",
        dataType: 'json',
        success: function (response) {

            Notify( response.msg , response.result );

            if( response.result ) {
                var $td = $("td[data-key='"+ key +"']").eq(0);
                var $tr = $td.parents("tr");
                $tr.fadeOut(500);
                setTimeout(function() {
                    $tr.remove();
                }, 600);
            }
            $.magnificPopup.close();

        }
    });
};


var stripTag = function( text ) {
    return text.replace(/(<([^>]+)>)/ig,"");
};

var haveHtml = function( text ) {
    return stripTag(text) != text;
};

var getType = function( text ) {
    return haveHtml(text) ? "html" : "text";
}