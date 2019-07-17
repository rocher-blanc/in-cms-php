var which;

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
    checkBox( base );
    checkboxSwitch( base );
    initDatePicker( base );
    initSelect( base );
    initFieldImage( base );
    checkForm( base );
};

initFieldImage = function( base ) {
    if ( $(base + ' a.showfieldupload').length ) {
        $(base + ' a.showfieldupload').click(function() {
            $('#' + $(this).data('field') ).removeClass('hide');
            $(this).parent().hide();
        });
    }
};

checkBox = function( base ) {
    if ( $(base + ' select[data-plugin-selectPicker]').length ) {
        $(base + ' select[data-plugin-selectPicker]').selectpicker({
            iconBase: '',
            tickIcon: 'icon-line-check'
        });
    }
};

test = function() {
    alert('ça marche');
};

checkboxSwitch = function( base ) {
    if ( $( base + ' .bt-switch').length ) {
        $( base + " .bt-switch").bootstrapSwitch();
    }
};

checkForm = function(base) {
    $("button, input").bind('click', function(e) {
        which = $(this);
    });

    $(base + ' form.ajax').not('.submitReady').bind('submit', function(e) {
        which.prop("disabled",true);

        var $form = $(this);

        if( typeof $form.attr("submitting") === 'undefined' ) {
            var mod = $form.data('slug');
            if ( $(base + ' .'+mod+'-form-process').length ) {
                $(base + ' .'+mod+'-form-process').show();
            }
            $(base + ' .ed_field').removeClass('error');
            var serialize = new FormData($form.get(0));

            e.preventDefault();
            e.stopPropagation();

            $form.find("[type='submit']").attr("disabled","disabled").addClass("disabled temp-disabled");
            $form.attr("submitting", "1");

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
                    // Result is success
                    if( data.result ) {
                        if ( typeof $form.data('callback') !== 'undefined' ) {
                            window[$form.data('callback')]();
                        }
                        else {
                            // Test to redirection
                            if( typeof data.url !== "undefined" && data.url.trim().length > 0 ) {
                                if ( typeof data.timer !== "undefined" ) {
                                    setTimeout(function(){
                                        redirect(data.url);
                                    }, data.timer);
                                }
                                else {
                                    redirect(data.url);
                                }
                            }
                        }
                    }
                    // Result is not success
                    else {
                        if ( data.tab ) $('#onglet-' + data.tab ).click();

                        if ( data.field ) {
                            if ( $('#field-' + data.field).find('input, textarea').length ) {
                                $('#field-' + data.field).find('input, textarea').addClass('error').focus();
                            }
                        }

                        if ( data.fields ) {
                            $.each(data.fields, function( index, value ) {
                                $('#field-' + value.field).addClass('error');
                            });
                        }
                    }
                    // Hide process icon
                    if ( $(base + ' .'+mod+'-form-process').length ) {
                        $(base + ' .'+mod+'-form-process').hide();
                    }
                    // Send notification
                    Notify(data.msg, data.result);
                },
                complete: function() {
                    $form.removeAttr("submitting");
                }
            });

            which.prop("disabled",false);
        }

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
        dataType: 'json',
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
