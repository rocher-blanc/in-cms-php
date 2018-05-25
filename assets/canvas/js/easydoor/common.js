var message = false;
var d = document;

$(function() {
    checkboxSwitch();
});

checkboxSwitch = function() {
    if ( $('.bt-switch').length ) {
        $(".bt-switch").bootstrapSwitch();
    }
};

Notify = function(msg, result) {
    var id = new Date().getTime() ;
    var type = 'error' ;

    if ( result ) {
        type = 'success' ;
    }

    var $this = $('<a />');

    $this
        .attr('id', 'msg' + id)
        .attr('data-notify-type', type)
        .attr('data-notify-msg', msg)
        .attr('data-notify-position', "bottom-right")
        .attr('data-progress-bar', true);

    SEMICOLON.widget.notifications($this);
};

redirect = function( url ) {
    d.location.href = url ;
};

initSelect = function() {
    if ( $("select[data-plugin-selectTwo]").length ) {
        $("select[data-plugin-selectTwo]").select2({
            "language": {
                "noResults": function () {
                    return "Aucun résultat trouvé";
                }
            }
        });
    }
};

deleteElementExt = function( url ) {
    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content"),
        success: function(data) {
            $.magnificPopup.close();
            if( data.url != '') {
                redirect( data.url );
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.magnificPopup.close();
            Notify(errorThrown, false);
        }
    });
};