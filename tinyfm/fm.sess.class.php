<?php

/**
 * @package tinyFM
 * @author Harald Petrich
 *
 * @copyright  Copyright (C) Trebaxa GmbH&Co.KG. All rights reserved.
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 2.1, February 1999
 * 
 * https://www.tinyfm.io
 * 
 */

defined('FM_INSIDE') or die('Access denied.');

class fmsess {

    /**
     * fmsess_class::prevent_hijacking()
     * 
     * @return
     */
    protected static function prevent_hijacking() {
        if (!isset($_SESSION['user_agent'])) {
            return false;
        }
        if ($_SESSION['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
            return false;
        }
        return true;
    }

    /**
     * fmsess_class::init_session()
     * 
     * @return void
     */
    protected static function init_session() {
        $_SESSION = array('user_agent' => $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * fmsess_class::clear_session()
     * 
     * @return void
     */
    public static function clear_session() {
        $_SESSION = array();
        session_write_close();
        @session_destroy();
        session_regenerate_id(true);
        self::set_session_and_start();
        self::init_session();
    }

    /**
     * fmsess_class::set_session_and_start()
     * 
     * @return void
     */
    protected static function set_session_and_start() {
        $opt = array(
            'lifetime' => 60 * 60 * 8,
            'path' => '/',
            'domain' => (string )$_SERVER['SERVER_NAME'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true);
        session_set_cookie_params($opt['lifetime'], $opt['path'], $opt['domain'], $opt['secure'], $opt['httponly']);
        @session_start();
    }

    /**
     * fmsess_class::start_session()
     * 
     */
    public static function start_session() {
        session_name('fm');
        self::set_session_and_start();

        if (!self::prevent_hijacking()) {
            self::init_session();
        }
        @ini_set('session.referer_check', '');
        #@ini_set("session.use_cookies", "0");
        #echo '<pre>'.print_r(session_get_cookie_params(),true);die;
    }
}
