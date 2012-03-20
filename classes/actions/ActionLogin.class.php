<?php

/* ---------------------------------------------------------------------------
 * @Plugin Name: OpenIdCmt
 * @Author: Web-studio stfalcon.com
 * @License: GNU GPL v2, http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * ----------------------------------------------------------------------------
 */

class PluginOpenidcmt_ActionLogin extends PluginOpenidcmt_Inherit_ActionLogin
{

    /**
     * Авторизация пользователя
     */
    protected function EventLogin()
    {

        // Get stored return url
        $sReturnUrl = $this->Session_Get('openidcmt_return') ? : Config::Get('path.root.web') . '/';

        $this->Session_Drop('openidcmt_return');

        $sReffererUrl = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER'], '/') . '/' : '';
        if (isPost('submit_login') && $sReffererUrl && (strpos($sReffererUrl, Router::GetPath('login')) !== false)) {
            $_SERVER['HTTP_REFERER'] = $sReturnUrl;
        }

        parent::EventLogin();
    }

}