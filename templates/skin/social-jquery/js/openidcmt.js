var ls = ls || {};

/**
 * Обработка комментариев для незарегистрированных пользователей
 */
ls.comments.add = function (formObj, targetId, targetType) {
    if (this.options.wysiwyg) {
        $('#' + formObj + ' textarea').val(tinyMCE.activeEditor.getContent());
    }
    formObj = $('#' + formObj);

    ls.ajax(aRouter['openidcmt']+'ajaxcheckcomment', formObj.serializeJSON(), function (result) {
        $('#comment-button-submit').removeAttr('disabled');
        if (!result) {
            this.enableFormComment();
            ls.msg.error('Error', 'Please try again later');
            return;
        }
        if (result.bStateError) {
            this.enableFormComment();
            ls.msg.error(null, result.sMsg);
        } else {
            // Если пользователь не залогинен, показываем форму авторизации
            if (result.bShowLoginForm) {
                $('#login_form').jqmShow();
            }
        }
    }.bind(this));
}

ls.tools.textPreview = function(textId, save, divPreview) {
	var text =(BLOG_USE_TINYMCE) ? tinyMCE.activeEditor.getContent()  : $('#'+textId).val();
	var ajaxUrl = aRouter['openidcmt']+'ajaxpreviewcomment';
	var ajaxOptions = {text: text, save: save};
	'*textPreviewAjaxBefore*'; '*/textPreviewAjaxBefore*';
	ls.ajax(ajaxUrl, ajaxOptions, function(result){
		if (!result) {
			ls.msg.error('Error','Please try again later');
		}
		if (result.bStateError) {
			ls.msg.error(result.sMsgTitle||'Error',result.sMsg||'Please try again later');
		} else {
			if (!divPreview) {
				divPreview = 'text_preview';
			}
			var elementPreview = $('#'+divPreview);
			'*textPreviewDisplayBefore*'; '*/textPreviewDisplayBefore*';
			if (elementPreview.length) {
				elementPreview.html(result.sText);
				'*textPreviewDisplayAfter*'; '*/textPreviewDisplayAfter*';
			}
		}
	});
}