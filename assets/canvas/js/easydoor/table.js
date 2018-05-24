$(function() {
    listenTable();
});

listenTable = function() {
    /** GESTION DES FILTRES DE RECHERCHE **/
    if ( $("a[data-filter]").length ) {
        $("a[data-filter]").click(function(e) {
            e.preventDefault();

            $('#orderSearch').val( $(this).data('order') );
            $('#bySearch').val( $(this).data('by') );
            $('#formSeach').submit();
        });
    }

    /** GESTION DES SWITCHS **/
    $('.switch label').click(function() {
        var checkbox = $(this).parent().find('.switch-toggle:checked');

        if ( checkbox.length == 1 ) {
            var url = $(this).parent().data('urldisable');
        }
        else {
            var url = $(this).parent().data('urlenable');
        }

        $.ajax({
            type: "GET",
            url: url,
            success: function(data){
                Notify(data.msg, data.result);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Notify(errorThrown, false);
            }
        });
    });

    /** GESTION DES DATE RANGE PICKER **/
    $('.daterange').daterangepicker({
        "applyClass": "button button-rounded button-mini nomargin button-color",
        "cancelClass": "button button-rounded button-mini nomargin button-light",
        "locale": {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Valider",
            "cancelLabel": "Annuler",
            "fromLabel": "De",
            "toLabel": "à",
            "customRangeLabel": "Custom",
            "daysOfWeek": [
                "Dim",
                "Lun",
                "Mar",
                "Mer",
                "Jeu",
                "Ven",
                "Sam"
            ],
            "monthNames": [
                "Janvier",
                "Février",
                "Mars",
                "Avril",
                "Mai",
                "Juin",
                "Juillet",
                "Août",
                "Septembre",
                "Octobre",
                "Novembre",
                "Décembre"
            ],
            "firstDay": 1
        }
    });

    /** GESTION DES RECHERCHES **/
    $('#formSeach').not('.submitReady').bind('submit', function(e) {
        var $form = $(this);

        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $form.serialize(),
            success: function(html) {
                $('#table-content').html( html ) ;
                listenTable();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Notify(errorThrown, false);
            }
        });

    }).addClass('submitReady');

    $('.initsearch').click(function() {
        reloadTable();
    });
};

deleteElement = function( url ) {
    $.ajax({
        type: "POST",
        url: url,
        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content"),
        success: function(data) {
            $.magnificPopup.close();
            Notify(data.msg, data.result);

            if(data.result == true) {
                reloadTable();
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.magnificPopup.close();
            Notify(errorThrown, false);
        }
    });
};

reloadTable = function() {
    $.ajax({
        url: $('#url_table').val() ,
        type: 'GET',
        success: function(html) {
            $('#table-content').html( html ) ;
            listenTable();
        }
    });
};