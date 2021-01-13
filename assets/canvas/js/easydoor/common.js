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

select2MatchStart = function(params, data) {
    if( typeof params.term === "undefined" ) return data;
    if( params.term.trim() === "" )          return data;

    return data.text.indexOf(params.term.trim()) > -1
        ? data
        : null;
};

initSelect = function( base ) {
    if ( $(base + " select[data-plugin-selectTwo]").length ) {
        $(base + " select[data-plugin-selectTwo]").select2({
            "language": {
                "noResults": function () {
                    return "Aucun résultat trouvé";
                }
            },
            matcher: select2MatchStart
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



aAjax = function( url , modal ) {
    $.ajax({
        type: "GET",
        url: url,
        dataType: 'json',
        success: function(data) {
            if ( modal ) {
                $.magnificPopup.close();
            }

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
            if ( modal ) {
                $.magnificPopup.close();
            }

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

            // Small space test
            if( firstTop > $(window).scrollTop() ) {
                $ul.addClass("small-space");

                // Two lines test
                firstTop = $ul.find("li").first().offset().top;
                if( firstTop > $(window).scrollTop() ) {
                    $("#primary-menu").addClass("two-lines");
                    // $("#header").addClass("static-sticky");
                }

            }
        // }
    }
};

modalConfig = function( onglet ) {
    return {
        type: 'ajax', 
        closeBtnInside: false,
        callbacks: {
            ajaxContentAdded: function(mfpResponse) {
                SEMICOLON.widget.loadFlexSlider();
                SEMICOLON.initialize.resizeVideos();
                SEMICOLON.widget.masonryThumbs();
                checkboxSwitch('#myModal1');
                init('#myModal1');
                if( $("#myModal1 .tabs").length ) {
                    $("#myModal1 .tabs").tabs();
                }

                if ( typeof onglet !== 'undefined' ) {
                    $('#myModal1 #' + onglet ).trigger('click');
                }
            },
            open: function() {
                $('body').addClass('ohidden');
            },
            close: function() {
                $('body').removeClass('ohidden');
            }
        }
    };
};