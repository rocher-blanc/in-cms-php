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
    $.ajax({
        type: "POST",
        url: url_ajax,
        data: $("meta[name=tokename]").attr("content") + '=' + $("meta[name=token]").attr("content") + '&template=' + idt,
        success: function(data){
            const json = JSON.parse(JSON.stringify(data));
            TopolPlugin.load(json);
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
        removeTopBar: false, // Hides the top bar of the email editor
        light: true, // set the editor theme to be light
        customFileManager: true, // sets the build in file manager to be disabled and change to call the callbacks provided below
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
            onOpenFileManager: function () {
                fileManager( config.urlFileManager , config.urlUpload );
            },
            onAutoSave: function (json) {
                // Called when the editor decides that it needs an autosave. Mostly when the user makes a change and does not save it immedietly.
                //console.log(json);
            }
        }
    };

    // Plugin script
    "use strict";!function(t){window.TopolPlugin||(window.TopolPlugin=new function(){var t={},n={},i={},o=[],a=!1;function e(t){"callback"==t.data.type&&"function"==typeof i.callbacks[t.data.action]&&i.callbacks[t.data.action].apply(window,t.data.data.args)}function c(n,i){a?t.contentWindow.postMessage({action:n,data:i},"*"):o.push({action:n,data:i})}this.init=function(l,s){i=l||{},(n=document.querySelectorAll(i.id)[0])?((t=document.createElement("iframe")).width="100%",t.height="100%",t.frameBorder="0",t.src="https://d5aoblv5p04cg.cloudfront.net/editor/plugin/index.html",n.appendChild(t),parsedOptions=JSON.parse(JSON.stringify(i)),window.addEventListener("message",e),t.onload=function(){a=!0;for(var t=o.length,n=0;n<t;n++){var i=o.shift();c(i.action,i.data)}}):console.error("Unable to find the element "+i.id),c("init",{options:Object.assign({},l,{callbacks:null})})},this.save=function(){c("save",{})},this.load=function(t){c("load",{json:t})},this.togglePreview=function(){c("togglePreview",{})},this.chooseFile=function(t){c("chooseFile",{url:t})}})}();
    // Plugin start
    TopolPlugin.init(TOPOL_OPTIONS);
};