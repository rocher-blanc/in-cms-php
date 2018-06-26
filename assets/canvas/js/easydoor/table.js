$(function() {
    tableCustomization();
});

tableCustomization = function() {
    $('.dropdown-menu .dropdown-item').click(function(e) {
        var $link = $(this);
        var $parent = $link.parent();
        var urlCustomization = $parent.data('action') ;
        var active = 1;

        e.stopPropagation();
        if ( $link.hasClass('active') ) {
            $link.removeClass('active');
            active = 0;
        }
        else {
            $link.addClass('active');
        }

        $.ajax({
            type: "POST",
            url: urlCustomization,
            data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&field=' + $link.data('name') + '&active=' + active,
            success: function(data){
                reloadTable( $( '#' + $parent.data('table') ).find('form.tableFormSeach') , $parent.data('table') )
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Notify(errorThrown, false);
            }
        });
    });
};

listenFormTable = function( base ) {
    $( '#' + base ).find('form.tableFormSeach').each(function() {
        var $form = $(this);

        /** GESTION DES FILTRES DE RECHERCHE **/
        if ( $form.find("a[data-filter]").length ) {
            $form.find("a[data-filter]").click(function(e) {
                e.preventDefault();

                $form.find('.tableOrderSearch').val( $(this).data('order') );
                $form.find('.tableBySearch').val( $(this).data('by') );
                $form.submit();
            });
        }

        /** GESTION DES SWITCHS **/
        $form.find('.switch label').click(function() {
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
        if ( $form.find('.daterange').length ) {
            $form.find('.daterange').daterangepicker({
                autoUpdateInput: false,
                "applyClass": "button-color",
                "cancelClass": "button-light",
                "buttonClasses": "button button-rounded button-mini nomargin",
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

            $form.find('.daterange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            $form.find('.daterange').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

        }

        if( $form.find('[data-lightbox="ajax"]').length > 0 ) {
            $form.find('[data-lightbox="ajax"]').magnificPopup({
                type: 'ajax',
                closeBtnInside: false,
                callbacks: {
                    ajaxContentAdded: function(mfpResponse) {
                        SEMICOLON.widget.loadFlexSlider();
                        SEMICOLON.initialize.resizeVideos();
                        SEMICOLON.widget.masonryThumbs();
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

        /** GESTION DES RECHERCHES **/
        $form.not('.submitReady').bind('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();

            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serialize(),
                success: function(html) {
                    $( '#' + $form.data('container') ).html( html ) ;
                    listenFormTable( base ) ;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Notify(errorThrown, false);
                }
            });

        }).addClass('submitReady');

        /** GESTION DES RECHERCHES - BOUTON REINITIALISER **/
        $form.find('.initsearch').click(function() {
            reloadTable( $form , base );
        });

        /** GESTION DE LA PAGINATION **/
        $form.find('.page-link').click(function() {
            $form.find('.tablePage').val( $(this).data('page') );
            $form.submit();
        });

        /** GESTION DE L'ORDER **/
        if ( $form.find('.table-dnd').length ) {
            $form.find('.table-dnd').tableDnD({
                onDragStart: function(table, row) {
                    $( "#" + $(row).data('tr') ).addClass('myDragClass');
                    var originalOrder = $.tableDnD.serialize();
                },
                dragHandle: '.orderTable',
                onDragClass: 'myDragClass',
                onDrop: function(table, row) {
                    var data   = $.tableDnD.serialize();
                    var module = $('#module4JS').val() ;

                    $.ajax({
                        type : 'POST',
                        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&' + data,
                        url : siteurl + "module/" + module + "/order/0/" + $("meta[name=token]").attr("content"),
                        success: function(data){
                            Notify(data.msg, data.result);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Notify(errorThrown, false);
                        }
                    });
                }
            });
        }

        /** GESTION DES SELECT **/
        initSelect( '#' + base );
    });
};

deleteElement = function( url , base ) {
    $.ajax({
        type: "POST",
        url: url,
        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content"),
        success: function(data) {
            $.magnificPopup.close();
            Notify(data.msg, data.result);

            if(data.result == true) {
                $( '#' + base ).find('form.tableFormSeach').each(function() {
                    reloadTable( $(this) , base );
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.magnificPopup.close();
            Notify(errorThrown, false);
        }
    });
};


reloadTable = function( $form , base ) {
    $.ajax({
        url: $form.attr('action') ,
        type: 'GET',
        success: function(html) {
            $( '#' + $form.data('container') ).html( html ) ;
            listenFormTable( base ) ;
        }
    });
};