<?PHP

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

class fm_info extends fm_master {

    public static $totalsize = 0;
    public static $file_count = 0;

    /**
     * fm_info::add_bytes()
     * 
     * @param mixed $bytes
     * @return void
     */
    public static function add_bytes($bytes) {
        static::$totalsize += (int)$bytes;
    }

    /**
     * fm_info::set_file_count()
     * 
     * @param mixed $bytes
     * @return void
     */
    public static function set_file_count($count) {
        static::$file_count = (int)$count;
    }

    /**
     * fm_info::get_infos()
     * 
     * @return
     */
    public static function get_infos() {
        return array(
            'totalbytes' => static::$totalsize,
            'file_count' => static::$file_count,
            );
    }
}
