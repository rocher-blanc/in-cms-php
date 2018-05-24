$(function() {
    checkboxSwitch();
    initDatePicker();
    checkForm();
    initLink();
});

checkboxSwitch = function() {
    if ( $('.bt-switch').length ) {
        $(".bt-switch").bootstrapSwitch();
    }
};

checkForm = function() {
    $('form').not('.submitReady').bind('submit', function(e) {
        $('.form-process').show();
        var $form = $(this);

        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: $('#form-module form.submitReady').serialize(),
            success: function(data) {
                if(data.result == true && data.url != '') {
                    redirect( data.url );
                }
                else {
                    Notify(data.msg, data.result);
                }

                $('.form-process').hide();
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

/*  */
initLink = function() {
    if ( $('.input-link').length ) {
        $(".dropdown-menu.link").on('click', 'li a', function(){
            $( "#" + $(this).data('menu') ).html( $(this).text() + ' <span class="caret"></span>' );
            $( "#" + $(this).data('hidden') ).val( $(this).text() );
        });

        $('.input-link').change(function() {
            updateLink( $(this) );
        });

        $('.input-link').each(function() {
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
}