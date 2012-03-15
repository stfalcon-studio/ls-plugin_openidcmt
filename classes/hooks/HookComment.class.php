<?php

/* ---------------------------------------------------------------------------
 * @Plugin Name: OpenIdCmt
 * @Author: Web-studio stfalcon.com
 * @License: GNU GPL v2, http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * ----------------------------------------------------------------------------
 */

class PluginOpenidcmt_HookComment extends Hook
{

    public function RegisterHook()
    {
        $this->AddHook('module_user_authorization_after', 'PostDraftCommentAfter');
    }

    /**
     * Публикуем сохраненный в сессии комментарий
     *
     * @param array $aData
     */
    public function PostDraftCommentAfter($aData)
    {
        if (!isset($aData['params'][0]) || empty($aData['params'][0])) {
            return;
        }

        $oCurrentUser = $aData['params'][0];

        // Get previous comment data
        $aCommentData = (array) unserialize($this->Session_Get('openidcmt_draft_data'));

        if (isset($aCommentData['sTargetId'])) {
            /**
             * Проверяем топик
             */
            if (!($oTopic = $this->Topic_GetTopicById($aCommentData['sTargetId']))) {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                return;
            }

            /**
             * Проверяем запрет на добавления коммента автором топика
             */
            if ($oTopic->getForbidComment()) {
                $this->Message_AddErrorSingle($this->Lang_Get('topic_comment_notallow'), $this->Lang_Get('error'));
                return;
            }

            $oCommentParent = null;

            $sText = isset($aCommentData['sText']) ? $aCommentData['sText'] : '';

            $sParentId = isset($aCommentData['sParentId']) ? (int) $aCommentData['sParentId'] : 0;

            if ($sParentId != 0) {
                /**
                 * Проверяем существует ли комментарий на который отвечаем
                 */
                if (!($oCommentParent = $this->Comment_GetCommentById($sParentId))) {
                    $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
                /**
                 * Проверяем из одного топика ли новый коммент и тот на который отвечаем
                 */
                if ($oCommentParent->getTargetId() != $oTopic->getId()) {
                    $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
            } else {
                /**
                 * Корневой комментарий
                 */
                $sParentId = null;
            }

            $oCommentNew = Engine::GetEntity('Comment');
            $oCommentNew->setTargetId($oTopic->getId());
            $oCommentNew->setTargetType('topic');
            $oCommentNew->setTargetParentId($oTopic->getBlog()->getId());
            $oCommentNew->setUserId($oCurrentUser->getId());
            $oCommentNew->setText($sText);
            $oCommentNew->setDate(date("Y-m-d H:i:s"));
            $oCommentNew->setUserIp(func_getIp());
            $oCommentNew->setPid($sParentId);
            $oCommentNew->setTextHash(md5($sText));

            $sReturnUrl = $oTopic->getUrl();
            $this->Hook_Run('comment_add_before', array(
                'oCommentNew'    => $oCommentNew,
                'oCommentParent' => $oCommentParent,
                'oTopic'         => $oTopic
            ));
            /**
             * Добавляем коммент
             */
            if ($this->Comment_AddComment($oCommentNew)) {
                // Удаляем из сессии данные о черновике комментария
                $this->Session_Drop('openidcmt_draft_data');

                $this->Hook_Run('comment_add_after', array(
                    'oCommentNew'    => $oCommentNew,
                    'oCommentParent' => $oCommentParent,
                    'oTopic'         => $oTopic
                ));

                $this->Viewer_AssignAjax('sCommentId', $oCommentNew->getId());
                if ($oTopic->getPublish()) {
                    /**
                     * Добавляем коммент в прямой эфир если топик не в черновиках
                     */
                    $oCommentOnline = Engine::GetEntity('Comment_CommentOnline');
                    $oCommentOnline->setTargetId($oCommentNew->getTargetId());
                    $oCommentOnline->setTargetType($oCommentNew->getTargetType());
                    $oCommentOnline->setTargetParentId($oCommentNew->getTargetParentId());
                    $oCommentOnline->setCommentId($oCommentNew->getId());

                    $this->Comment_AddCommentOnline($oCommentOnline);
                }
                /**
                 * Сохраняем дату последнего коммента для юзера
                 */
                $oCurrentUser->setDateCommentLast(date("Y-m-d H:i:s"));
                $this->User_Update($oCurrentUser);
                /**
                 * Отправка уведомления автору топика
                 */
                $oUserTopic = $oTopic->getUser();
                if ($oCommentNew->getUserId() != $oUserTopic->getId()) {
                    $this->Notify_SendCommentNewToAuthorTopic($oUserTopic, $oTopic, $oCommentNew, $oCurrentUser);
                }
                /**
                 * Отправляем уведомление тому на чей коммент ответили
                 */
                if ($oCommentParent and $oCommentParent->getUserId() != $oTopic->getUserId() and $oCommentNew->getUserId() != $oCommentParent->getUserId()) {
                    $oUserAuthorComment = $oCommentParent->getUser();
                    $this->Notify_SendCommentReplyToAuthorParentComment($oUserAuthorComment, $oTopic, $oCommentNew, $oCurrentUser);
                }
                $this->Message_AddNoticeSingle($this->Lang_Get('opencmtid_comment_send'), $this->Lang_Get('attention'), true);

                // Подменяем URL для возврата на страницу комментария
                $sReffererUrl = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER'], '/') : '';
                if ($sReffererUrl == $sReturnUrl) {
                    $sReturnUrl .= "#comment{$oCommentNew->getId()}";
                    $_SERVER['HTTP_REFERER'] = $sReturnUrl;
                } else {
                    $this->Session_Set('openidcmt_return', "{$sReturnUrl}#comment{$oCommentNew->getId()}");
                }
            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            }
        }
    }

}
