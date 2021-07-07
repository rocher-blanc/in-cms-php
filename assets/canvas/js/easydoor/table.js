$(function() {
    tableCustomization();
    initExportButton();
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
                reloadTable( $( '#' + $parent.data('table') ).find('form.tableFormSearch') , $parent.data('table') )
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Notify(errorThrown, false);
            }
        });
    });
};

listenFormTable = function( base , firstCall ) {
    $( '#' + base ).find('form.tableFormSearch').each(function() {
        var $form = $(this);

        if( firstCall === true && localStorage.getItem( $form.data('container') ) !== null ) {

            // Retrait de la page conservée
            var data = localStorage.getItem( $form.data('container') ).split('&');
            for( var i in data ) {
                if( data[i].substr(0,4) === 'page' ) {
                    data.splice( i , 1 );
                    i--;
                }
            }
            data = data.join('&');

            // Chargement du tableau filtré
            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: data,
                success: function(html) {
                    $( '#' + $form.data('container') ).html( html ) ;
                    listenFormTable( base ) ;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Notify(errorThrown, false);
                }
            });

        }
        else {
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

            /** GESTION DES DUPLICATE **/
            $form.find('.duplicate').click(function(e) {
                e.preventDefault();
                e.stopPropagation();

                $link = $(this);

                $.ajax({
                    type: "POST",
                    url: $link.attr('href'),
                    data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content"),
                    success: function(data) {
                        Notify(data.msg, data.result);
                        reloadTable( $form , base );
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Notify(errorThrown, false);
                    }
                });
            });

            /** GESTION DES ICONS AJAX **/
            $form.find('.ajaxtable').click(function(e) {
                e.preventDefault();
                e.stopPropagation();

                $link = $(this);

                $.ajax({
                    type: "POST",
                    url: $link.attr('href'),
                    data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content"),
                    success: function(data) {
                        Notify(data.msg, data.result);
                        reloadTable( $form , base );
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Notify(errorThrown, false);
                    }
                });
            });

            /** GESTION DES DEFAUT **/
            $form.find('.default').click(function(e) {
                e.preventDefault();
                e.stopPropagation();

                $link = $(this);

                $.ajax({
                    type: "POST",
                    url: $link.attr('href'),
                    data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content"),
                    success: function(data) {
                        Notify(data.msg, data.result);
                        reloadTable( $form , base );
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

            /** GESTION DES SELECT PICKER **/
            $form.find('select[data-plugin-selectPicker]').selectpicker({
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

            /** GESTION DES RECHERCHES **/
            $form.not('.submitReady').bind('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var data  = $form.serializeArray();
                var query = [];
                for( var i=0 ; i<data.length ; i++ ) {
                    if( data[i].name !== 'active' ) {
                        query.push( data[i].name + '=' + data[i].value );
                    }
                    else
                    {
                        data.splice( i , 1 );
                        i--;
                    }
                }

                localStorage.setItem( $form.data('container') , query.join('&') );

                $.ajax({
                    type: $form.attr('method'),
                    url: $form.attr('action'),
                    data: data,
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
                localStorage.removeItem( $form.data('container') );
                reloadTable( $form , base );
            });

            /** GESTION DE LA PAGINATION **/
            $form.find('.page-link').click(function() {
                $form.find('.tablePage').val( $(this).data('page') );
                $form.submit();
            });

            /** GESTION DE L'ORDER **/
            if ( $('.table-parent').length ) {
                $form.find('.table-dnd').each(function() {
                    var $table = $(this);
                    $table.tableDnD({
                        onDragStart: function (table, row) {
                            var parent = $(row).parent().parent().data('parent');
                            $("#" + $(row).data('tr')).addClass('myDragClass');
                            $('.table-parent').find('tr').each(function () {
                                if ($(this).data('parent') != parent) {
                                    $(this).hide();
                                }
                            });
                        },
                        dragHandle: '.orderTable',
                        onDragClass: 'myDragClass',
                        onDrop: function (table, row) {
                            $.ajax({
                                type: 'POST',
                                data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&' + $.tableDnD.serialize(),
                                url: siteurl + "module/" + $table.data('name') + "/order/0/" + $("meta[name=token]").attr("content"),
                                success: function (data) {
                                    Notify(data.msg, data.result);
                                    $('.table-parent').find('tr').each(function () {
                                        $(this).show();
                                    });
                                }
                            });
                        }
                    });
                });
            }
            else if ( $form.find('.table-dnd').length ) {
                $form.find('.table-dnd').each(function() {
                    var $table = $(this);
                    $table.tableDnD({
                        onDragStart: function(table, row) {
                            $( "#" + $(row).data('tr') ).addClass('myDragClass');
                            var originalOrder = $.tableDnD.serialize();
                        },
                        dragHandle: '.orderTable',
                        onDragClass: 'myDragClass',
                        onDrop: function(table, row) {
                            var data   = $.tableDnD.serialize();

                            $.ajax({
                                type : 'POST',
                                data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&' + data,
                                url : siteurl + "module/" + $table.data('name') + "/order/0/" + $("meta[name=token]").attr("content"),
                                success: function(data){
                                    Notify(data.msg, data.result);
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    Notify(errorThrown, false);
                                }
                            });
                        }
                    });
                });
            }

            /** GESTION DES SELECT **/
            initSelect( '#' + base );

            /** GESTION DES MODALS **/
            if (typeof modalDepedency === "function") {
                modalDepedency('#' + $form.data('container'));
            }

            if( $form.find('a[data-modal="true"]').length > 0 ) {
                $form.find('a[data-modal="true"]').magnificPopup(modalConfig());
            }

            /** GESTION DES BOUTONS D'ACTION MASSIVE **/
            $form.find( "[data-module-table-action-many-checkbox]" ).on("click", function() {
                if( $("[data-module-table-action-many-checkbox]:checked").length > 0 ) {
                    $(".action-massive").show();
                }
                else {
                    $(".action-massive").hide();
                }
            });
            $form.find( "[data-module-table-action-many-reverse]" ).on("click", function() {
                revertSelection();
            });
            $form.find( "[data-module-table-action-many-disable]" ).on("click", function() {
                disableMany();
            });
            $form.find( "[data-module-table-action-many-enable]" ).on("click", function() {
                enableMany();
            });
        }
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
                $( '#' + base ).find('form.tableFormSearch').each(function() {
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

deleteManyElement = function( url , base ) {
    var data = {};
    data[ $("meta[name=tokename]").attr("content") ] = $("meta[name=token]").attr("content");
    data[ 'listIds' ] = [];

    $("[data-module-table-action-many-checkbox]:checked").each(function(index, el) {
        data.listIds.push( $(el).attr('data-id') );
    });

    $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(data) {
            $.magnificPopup.close();
            Notify(data.msg, data.result);

            if(data.result == true) {
                $( '#' + base ).find('form.tableFormSearch').each(function() {
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

revertSelection = function() {
    $("[data-module-table-action-many-checkbox]").each(function(index, el) {
        $(el).prop( 'checked' , !$(el).prop('checked') );
    });
};

disableMany = function() {
    var countdown = $("[data-module-table-action-many-checkbox]:checked").length;
    $("[data-module-table-action-many-checkbox]:checked").each(function(index, el) {
        var url = $(this).parents("tr").find("[data-urldisable]").attr("data-urldisable");
        if( typeof url !== "undefined" ) {
            $.ajax({
                url: url,
                method: "GET",
                success: function() {
                    countdown--;
                    if( countdown <= 0 ) {
                        window.location.reload();
                    }
                }
            });
        }
    });
};

enableMany = function() {
    var countdown = $("[data-module-table-action-many-checkbox]:checked").length;
    $("[data-module-table-action-many-checkbox]:checked").each(function(index, el) {
        var url = $(this).parents("tr").find("[data-urlenable]").attr("data-urlenable");
        if( typeof url !== "undefined" ) {
            $.ajax({
                url: url,
                method: "GET",
                success: function() {
                    countdown--;
                    if( countdown <= 0 ) {
                        window.location.reload();
                    }
                }
            });
        }
    });
};

reloadTable = function( $form , base ) {
    var iddiv = $('#' + $form.data('container')) ;

    if ( typeof iddiv.data('table') !== "undefined" ) {
        $.ajax({
            url: iddiv.data('table') + "?test=2",
            type: 'GET',
            success: function(html) {
                iddiv.html( html ) ;
                listenFormTable( base ) ;
            }
        });
    }
    else {
        $.ajax({
            url: $form.attr('action') + "?test=1",
            type: 'GET',
            success: function(html) {
                iddiv.html( html ) ;
                listenFormTable( base ) ;
            }
        });
    }
};

initExportButton = function() {
    $("[data-export-btn]").on("click", function() {
        var $btn = $("[data-export-btn]");

        var filters = $('.tableFormSearch').serializeArray();
        for( let i in filters ) {
            switch( filters[i].name ) {
                case "elmt_per_page" :
                    filters[i].value = 'all';
            }
        }

        var url = $btn.attr('href').split('?')[0] + '?' + $.param(filters);

        var a   = document.createElement('a');
        a.href = url;
        document.body.append(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);

        return false;
    });
}