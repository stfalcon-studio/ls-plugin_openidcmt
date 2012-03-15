window.addEvent('domready', function () {
    if (window['lsCmtTreeClass']) {
        lsCmtTreeClass.prototype.addComment = function (formObj, targetId, targetType) {
            var thisObj = this;
            formObj = $(formObj);
            var params = formObj.toQueryString();
            params = params + '&security_ls_key=' + LIVESTREET_SECURITY_KEY;
            if (BLOG_USE_TINYMCE) {
                $('form_comment').getElement('input[name=submit_comment]').set('disabled', 'disabled');
            }
            new Request.JSON({
                url:aRouter['openidcmt'] + 'ajaxcheckcomment',
                noCache:true,
                data:params,
                onSuccess:function (result) {
                    if (!result) {
                        thisObj.enableFormComment();
                        msgErrorBox.alert('Error', 'Please try again later');
                        return;
                    }
                    if (result.bStateError) {
                        thisObj.enableFormComment();
                        msgErrorBox.alert(result.sMsgTitle, result.sMsg);
                    } else {
                        if (result.bShowLoginForm) {
                            showLoginForm();
                        }
                    }
                },
                onFailure:function () {
                    if (BLOG_USE_TINYMCE) {
                        $('form_comment').getElement('input[name=submit_comment]').set('disabled', '');
                    }
                    msgErrorBox.alert('Error', 'Please try again later');
                }
            }).send();
        }

    }
});

function ajaxTextPreview(textId,save,divPreview) {
	var text;
	if (BLOG_USE_TINYMCE && tinyMCE && (ed=tinyMCE.get(textId))) {
		text = ed.getContent();
	} else {
		text = $(textId).value;
	}
	save=save ? 1 : 0;
	new Request.JSON({
		url: aRouter['openidcmt']+'ajaxpreviewcomment',
		noCache: true,
		data: { text: text, save: save, security_ls_key: LIVESTREET_SECURITY_KEY },
		onSuccess: function(result){
			if (!result) {
                msgErrorBox.alert('Error','Please try again later');
        	}
            if (result.bStateError) {
            	msgErrorBox.alert('Error','Please try again later');
            } else {
            	if (!divPreview) {
            		divPreview='text_preview';
            	}
            	if ($(divPreview)) {
            		$(divPreview).set('html',result.sText).setStyle('display','block');
            	}
            }
		},
		onFailure: function(){
			msgErrorBox.alert('Error','Please try again later');
		}
	}).send();
}