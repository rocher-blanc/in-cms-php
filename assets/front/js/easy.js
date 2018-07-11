$(function() {
    init('body');
});

init = function( base ) {
    checkForm( base );
    checkBox( base );
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

checkForm = function(base) {
    $(base + ' form.ajax').not('.submitReady').bind('submit', function(e) {
        var $form = $(this);
        var mod = $form.data('slug');
        if ( $(base + ' .'+mod+'-form-process').length ) {
            $(base + ' .'+mod+'-form-process').show();
        }

        $(base + ' .ed_field').removeClass('error');
        var serialize = $(base + ' form.submitReady').serialize();

        e.preventDefault();
        e.stopPropagation();

        $(base + ' form.ajax').find('.error').removeClass('error');

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: serialize,
            success: function(data) {
                if(data.result == true && data.url != '') {
                    $(base + ' form.ajax').trigger("reset");
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

                            if ( $('#field-' + value.field).find('input[type=text]').length ) {
                                $('#field-' + value.field).find('input[type=text]').addClass('error');
                            }
                            else if ( $('#field-' + value.field).find('textarea').length ) {
                                $('#field-' + value.field).find('textarea').addClass('error');
                            }
                        });
                    }
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
