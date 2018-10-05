$(function() {
    init('body');
});

if (typeof Notify !== "function") {
    Notify = function( msg, result ) {
        alert( msg );
    };
}

if (typeof redirect !== "function") {
    redirect = function( url ) {
        if(typeof url !== "undefined") {
            document.location.href = url ;
        }
    };
}

init = function( base ) {
    checkForm( base );
    checkBox( base );
    checkboxSwitch( base );
    initDatePicker( base );
    initSelect( base );
};

checkBox = function( base ) {
    if ( $(base + ' select[data-plugin-selectPicker]').length ) {
        $(base + ' select[data-plugin-selectPicker]').selectpicker({
            iconBase: '',
            tickIcon: 'icon-line-check'
        });
    }
};

checkboxSwitch = function( base ) {
    if ( $( base + ' .bt-switch').length ) {
        $( base + " .bt-switch").bootstrapSwitch();
    }
};

checkForm = function(base) {
    $(base + ' form.ajax').not('.submitReady').bind('submit', function(e) {
        var $form = $(this);
        var mod = $form.data('slug');
        if ( $(base + ' .'+mod+'-form-process').length ) {
            $(base + ' .'+mod+'-form-process').show();
        }

        $(base + ' .ed_field').removeClass('error');
        var serialize = new FormData($form.get(0));

        e.preventDefault();
        e.stopPropagation();

        $(base + ' form.ajax').find('.error').removeClass('error');

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: serialize,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(data) {
                if( data.result ) {
                    //$(base + ' form.ajax').trigger("reset");
                }
                console.log(data.url);
                console.log(data.result);

                if(data.result == true && typeof data.url !== "undefined") {
                    redirect( data.url );
                }
                else {
                    Notify(data.msg, data.result);
                }

                if ( $(base + ' .'+mod+'-form-process').length ) {
                    $(base + ' .'+mod+'-form-process').hide();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Notify(errorThrown, false);
                if ( $(base + ' .'+mod+'-form-process').length ) {
                    $(base + ' .'+mod+'-form-process').hide();
                }
            }
        });
        return false;
    }).addClass('submitReady');

    $(base + ' form.submitReady[data-condition="true"]').not('.conditionReady').each(function() {
        var $this = $(this);
        var route = $this.attr('action');

        $this.find('select').change(function() {
            refreshShowIf( base , route , $this );
        });

        $this.find('.bootstrap-switch').on('switchChange.bootstrapSwitch', function (e, data) {
            refreshShowIf( base , route , $this );
        });
    }).addClass('conditionReady');
};

refreshShowIf = function( base , route , $this ) {
    var serialize = $this.serialize();
    $.ajax({
        url: route,
        type: 'POST',
        data: serialize + "&show=1",
        success: function(data) {
            if ( data.tabs.length ) {
                $.each(data.tabs, function(i, tab) {
                    var linktab = $('#tabs-link-' + tab.key );
                    if ( tab.show == true ) {
                        linktab.removeClass('hide').show();
                    }
                    else {
                        linktab.hide();
                    }

                    if ( tab.group.length ) {
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
                    var field = $("[name='"+ elt.name+"']").closest('.ed_field');
                    if ( elt.show == true ) {
                        field.removeClass('hide').show();
                    }
                    else {
                        field.hide();
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

    if ( $(base + " .date-range").length ) {
        $(base + " .date-range").datepicker({
            language: 'fr',
            autoclose: true,
            todayHighlight: true,
            inputs: $(".date-range input")
        });
    }
};
