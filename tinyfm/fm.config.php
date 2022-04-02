<?PHP

/**
 * @package tinyFM
 * @author Harald Petrich
 *
 * @copyright  Copyright (C) Trebaxa GmbH&Co.KG. All rights reserved.
 * @license    GNU General Public License v3.0
 * 
 * https://www.tinyfm.io
 * 
 */

defined('FM_INSIDE') or die('Access denied.');

class fm_config {
    /**
     * These settings are only the basic settings as fallback. 
     * Please use the config.json file, to configure tinyFM. Not this PHP class. 
     * 
     * Don't forget to set your private access key!!!
     */
    protected static $config = array(
        /* realtive path to medialibary from install path of tinyfm (fm.php)*/
        'relative_path_to_files' => '../../file_server/',
        /* path to medialibary for URL www.mydomain.com/file_server/ */
        'path_to_files' => 'file_server/',
        /* path to tinyfm for URL www.mydomain.com/cjs/tinyfm/ */
        'path' => 'cjs/tinyfm/',
        'dateformat' => 'd.m.Y',
        'access_key' => '',
        'resize_images_to_max_size' => true,
        'file_permission' => 0755,
        'folder_permission' => 0755,
        'dropzone' => array(
            'maxFilesize' => 256,
            #'acceptedFiles' => '.pdf,.jpg,.png,.svg,.csv,.docx,.xlsx,.pptx,.gif,.mov,.avi,.jpeg,.mp4',
            'forbidden_ext' => '.py,.ini,.bmp,.php,.php3,.php2,.php1,.php4,.php5,.pl,.cgi,.asp,.cfm,.bat,.com,.exe',
            ),
        );

    /**
     * fm_config::get_config_value()
     * 
     * @param mixed $key
     * @return
     */
    public static function get_config_value($key) {
        if (strstr($key, '.')) {
            list($f, $t) = explode('.', $key);
            return static::$config[$f][$t];
        }
        return static::$config[$key];
    }

    /**
     * fm_config::set_config_value()
     * 
     * @param mixed $key
     * @param mixed $value
     * @return
     */
    public static function set_config_value($key, $value) {
        if (strstr($key, '.')) {
            list($f, $t) = explode('.', $key);
            static::$config[$f][$t] = $value;
        }
        static::$config[$key] = $value;
    }


    /**
     * fm_config::get_access_key()
     * 
     * @return
     */
    public static function get_access_key() {
        return md5(date('Ymd') . json_encode(self::get_config()) . self::clean($_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_HOST'] . $_SERVER['DOCUMENT_ROOT']));
    }

    /**
     * fm_config::clean()
     * 
     * @param mixed $string
     * @return
     */
    private static function clean($string) {
        return preg_replace("/[^0-9a-zA-Z]/", "", strval($string));
    }

    /**
     * fm_config::get_config()
     * 
     * @return
     */
    public static function get_config() {
        $config_file = dirname(__FILE__) . '/config/config.json';
        if (!is_dir(dirname(__FILE__) . '/config/')) {
            mkdir(dirname(__FILE__) . '/config/', 0755);
            self::save(array('access_key' => md5(time() . rand(1, 1000))));
        }
        if (!is_file($config_file)) {
            self::save(array('access_key' => md5(time() . rand(1, 1000))));
        }
        if (is_file($config_file)) {
            static::$config = array_merge(static::$config, json_decode(file_get_contents($config_file), true));
        }
        return static::$config;
    }

    /**
     * fm_config::save()
     * 
     * @return void
     */
    public static function save($arr) {
        $config_file = dirname(__FILE__) . '/config/config.json';
        if (is_file($config_file)) {
            $arr = array_merge(self::get_config(), $arr);
        }
        $config_file = realpath(dirname(__FILE__)) . '/config/config.json';
        file_put_contents($config_file, json_encode($arr, JSON_PRETTY_PRINT));
    }
}
