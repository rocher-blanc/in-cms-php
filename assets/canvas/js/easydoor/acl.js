$(function() {
    $('a.right').click(function () {
        var extension	= $(this).data('extension') ;
        var type 	  	= $(this).data('type') ;
        var group 	  	= $(this).data('group') ;
        var modext 	  	= $(this).data('modext') ;
        var right 	  	= $(this).data('right') ;
        var link	    = $(this);

        $(this).html('...');

        $.ajax({
            url: siteurl + 'ext/group/right',
            type: "post",
            data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + "&" + type + "=" + modext + "&right=" + right + "&group=" + group,
            success: function(html) {
                try {
                    var ret = jQuery.parseJSON( html ) ;
                } catch (e) {
                    return false ;
                }

                if ( link.hasClass('yes') ) {
                    link.html('<i class="icon-remove-sign"></i>').removeClass('yes').addClass('no') ;
                }
                else {
                    link.html('<i class="icon-ok-sign"></i>').removeClass('no').addClass('yes') ;
                }
            }
        });
    });
});