var message = false;
var d = document;

$(function() {
    checkboxSwitch('body');

    $(window).resize(function() {
        setTimeout(function(){ headerResizing(); }, 300);
    });
    $(window).scroll(function() {
        setTimeout(function(){ headerResizing(); }, 300);
    });
    headerResizing();
});

checkboxSwitch = function( base ) {
    if ( $( base + ' .bt-switch').length ) {
        $( base + " .bt-switch").bootstrapSwitch();
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

initSelect = function( base ) {
    if ( $(base + " select[data-plugin-selectTwo]").length ) {
        $(base + " select[data-plugin-selectTwo]").select2({
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
            if( data.result == true ) {
                if (data.url != '') {
                    redirect(data.url);
                }
            }
            else {
                Notify(data.msg, false);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.magnificPopup.close();
            Notify(errorThrown, false);
        }
    });
};

headerResizing = function() {
    if( $("#primary-menu").find("ul").length > 0 ) {
        var width = $("body").width();
        var $notif = $("#primary-menu").find("#top-cart").eq(0);
        var $ul = $("#primary-menu").find("ul").eq(0);

        // Clean header
        $ul.removeClass("small-space");
        $("#primary-menu").removeClass("two-lines");
        // $("#header").removeClass("static-sticky");
        $notif.css("padding-right", $("#primary-menu .testimonial").outerWidth());

        // Big screen
        // if( width > 992 ) {
            var firstTop = $ul.find("li").first().offset().top;
            var lastTop = $ul.find("li").last().offset().top;

            // Small space test
            if( firstTop > 0 ) {
                $ul.addClass("small-space");

                // Two lines test
                firstTop = $ul.find("li").first().offset().top;
                lastTop = $ul.find("li").last().offset().top;
                if( firstTop > 0 ) {
                    $("#primary-menu").addClass("two-lines");
                    // $("#header").addClass("static-sticky");
                }

            }
        // }
    }
};