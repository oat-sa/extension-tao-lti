<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

/**
 * A controller to bypass some restrictions on cookies
 * @author Joel Bout <joel@taotesting.com>
 * @package taoLti
 */
class taoLti_actions_CookieUtils extends tao_actions_CommonModule
{
    /**
     * Verifies whenever or not the cookie was set correctly
     * Redirects the user to his destination if it was
     * or prompts the user to restore the session if it wasn't
     */
    public function verifyCookie() {
        $sessionId = session_id();
        $sessionName = session_name();
        $url = $this->getRequestParameter('redirect');

        if ($sessionId ===  $this->getCookie($sessionName)) {
            $this->forwardUrl($url);
        } else {
            $this->setData('redirect', $url);
            $this->setView('cookieError.tpl');
        }
    }

    /**
     * Closses the current session, restores the session provided
     * in the parameter session, regenerates a new sessionid and
     * redirects the user to the original address
     */
    public function restoreSession() {
        $sessionId = session_id();
        $url = $this->getRequestParameter('redirect');

        // Close current session
        session_unset();
        session_destroy();

        // Restore session
        session_id($sessionId);
        session_start();

        // Regenerate new session id
        session_regenerate_id(true);

        common_Logger::d('regenerated session to id \'' . $sessionId . '\'');
        $this->forwardUrl($url);
    }
}