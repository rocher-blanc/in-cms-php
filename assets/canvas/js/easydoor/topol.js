let saveTemplate = function( url_post , url_redirect , html , json , redirect ) {
    $.ajax({
        type: "POST",
        url: url_post,
        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&html=' + html + '&json=' + JSON.stringify(json),
        success: function(data){
            if ( redirect ) {
                document.location.href = url_redirect;
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Notify(errorThrown, false);
        }
    });
};

let duplicateTemplate = function( idt , url_ajax ) {
    console.log('duplicateTemplate is call');

    $.ajax({
        type: "POST",
        url: url_ajax,
        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&template=' + idt,
        success: function(data){
            const json = JSON.parse(JSON.stringify(data));
            TopolPlugin.load(json);
            console.log(json);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Notify(errorThrown, false);
        }
    });

};

let sendTest = function( url_send , email ) {
    TopolPlugin.save();

    $.ajax({
        type: "POST",
        url: url_send,
        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&email=' + email,
        success: function(data){
            Notify('Un email de test vient d\'être envoyé', true);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Notify(errorThrown, false);
        }
    });
};

let fileManager = function( url_file_manager , url_upload ) {
    $.magnificPopup.open({
        items: {
            src: url_file_manager
        },
        type:'ajax',
        callbacks: {
            ajaxContentAdded: function() {
                $('#content-media a.insert').each(function() {
                    $(this).click(function() {
                        TopolPlugin.chooseFile( $(this).find('img').attr('src') );
                        $.magnificPopup.close();
                    });
                });
            }
        }
    });
};

let drawTopol = function( config ) {
    if ( typeof config.duplicateId == 'string' && typeof config.duplicateSelect == 'string' ) {
        $( config.duplicateId ).click(function() {
            let template = $( config.duplicateSelect ).val() ;
            if ( template != null ) {
                duplicateTemplate( template , config.urlDuplicate );
            }
        });
    }

    // Plugin Settings
    var TOPOL_OPTIONS = {
        id: config.id,
        authorize: {
            apiKey: config.apiKey,
            userId: config.userId
        },
        language: "fr",
        templateId: config.idTopol,
        mergeTags: config.tags,
        removeTopBar: false, // Hides the top bar of the email editor
        light: true, // set the editor theme to be light
        customFileManager: false, // sets the build in file manager to be disabled and change to call the callbacks provided below
        // URL or Callback when clicked on Save & close
        callbacks: {
            onSaveAndClose: function (json, html) {
                saveTemplate( config.urlSave , config.urlRedirect , html, json, true);
            },
            onSave: function (json, html) {
                saveTemplate( config.urlSave , '' , html, json, false);
            },
            onTestSend: function (email, json, html) {
                sendTest( config.urlSend , email);
            },
            onInit() {
                console.log('test onInit');

                setTimeout(function(){
                    duplicateTemplate(config.duplicate.id, config.duplicate.route);
                }, 1000);
            },
            onOpenFileManager: function () {
                fileManager( config.urlFileManager , config.urlUpload );
            },
            onAutoSave: function (json) {
                // Called when the editor decides that it needs an autosave. Mostly when the user makes a change and does not save it immedietly.
                //console.log(json);
            }
        }
    };

    // Plugin start
    TopolPlugin.init(TOPOL_OPTIONS);
};