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

class fm extends fm_master {


    /**
     * fm::__construct()
     * 
     * @return void
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * fm::init()
     * 
     * @return void
     */
    private static function init() {
        fm_config::set_config_value('path_to_files', ltrim(self::add_trailing_slash(fm_config::get_config_value('path_to_files')), DIRECTORY_SEPARATOR));
        fm_config::set_config_value('path', ltrim(self::add_trailing_slash(fm_config::get_config_value('path')), DIRECTORY_SEPARATOR));
        if (empty(fm_config::get_config_value('file_permission'))) {
            fm_config::set_config_value('file_permission', 0755);
        }
        self::$root_to_files = fm::$root = self::add_trailing_slash(realpath($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::add_trailing_slash(ltrim(fm_config::
            get_config_value('path_to_files'), DIRECTORY_SEPARATOR))));
        if (!isset($_SESSION['fm']['root'])) {
            $_SESSION['fm']['root'] = fm::$root;
        }
        self::$server = self::get_tinyfm_url();
        self::$url = self::get_cms_url();
        self::$fm_root = self::add_trailing_slash(realpath($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . self::add_trailing_slash(fm_config::get_config_value('path')));
        self::$plugins = self::load_plugins();
        self::$eurl = $_SERVER['PHP_SELF'] . '?';
        if (isset($_GET['standalone'])) {
            self::set_standalone($_GET['standalone']);
        }
        if (isset($_REQUEST['nr'])) {
            $_SESSION['fm']['set']['nr'] = (int)$_REQUEST['nr'] === 1;
        }
    }

    /**
     * fm::run()
     * 
     * @return void
     */
    public static function run() {
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
        mb_http_input('UTF-8');
        mb_language('uni');
        mb_regex_encoding('UTF-8');
        ob_start('mb_output_handler');
        date_default_timezone_set('Europe/Paris');
        fmsess::start_session();
        self::check_config();
        self::check_access();
        self::init();
        self::clean_cache();
        $method = 'cmd_' . trim(strip_tags($_REQUEST['cmd']));
        if (method_exists('fm', $method)) {
            self::$method();
        }
        elseif (!method_exists('fm', $method) && !empty($_REQUEST['cmd'])) {
            die('Access denied.');
        }
    }

    /**
     * fm::check_config()
     * ensure we have an individual access key set. For YOUR security
     * @return void
     */
    protected static function check_config() {
        fm_config::get_config();
        if (empty(fm_config::get_config_value('access_key'))) {
            fm_config::save(array('access_key' => md5(time() * rand(0, 100))));
        }
        if (!is_dir(dirname(__FILE__) . '/cache/')) {
            mkdir(dirname(__FILE__) . '/cache/', 0755);
        }
    }

    /**
     * fm::cmd_load_folder()
     * 
     * @return void
     */
    public static function cmd_load_folder() {
        $opt = json_decode($_GET['json'], true);
        $_SESSION['fm']['root'] = self::add_trailing_slash(realpath(fm::$root . $opt['path']));
        $dirs = self::get_dirs($opt['path']);
        $filter = (isset($_SESSION['fm']['filter']) ? $_SESSION['fm']['filter'] : array());
        $files = self::get_files($opt['path'], $filter);
        self::assign('dirs', $dirs);
        self::assign('files', $files);
        self::assign('filter', $filter);
        self::assign('breadcrumb', self::gen_breadcrumb($opt['path']));
        self::echo('files');
    }

    /**
     * fm::cmd_load_root()
     * 
     * @return void
     */
    public static function load_root() {
        $_SESSION['fm']['root'] = self::add_trailing_slash(realpath(fm::$root));
        self::assign('dirs', self::get_dirs());
        $filter = (isset($_SESSION['fm']['filter']) ? $_SESSION['fm']['filter'] : array());
        if (isset($_GET['filetype']) && $_GET['filetype'] != "file" && $_GET['filetype'] != "") {
            $_SESSION['fm']['set']['filetype'] = (string )$_GET['filetype'];
            $filter['types'][$_GET['filetype']] = 1;
            if ($_GET['filetype'] == 'media') {
                $filter['types']['video'] = 1;
                $filter['types']['music'] = 1;
            }
        }
        self::assign('files', self::get_files('', $filter));
        self::assign('filter', $filter);
        self::assign('breadcrumb', fm::gen_breadcrumb());
    }


    /**
     * fm::cmd_search()
     * 
     * @return void
     */
    public static function cmd_search() {
        $filter = self::arr_trimsthsc($_POST['FORM']);
        $filter['q'] = trim(self::only_alphanums(strip_tags((String )$filter['q'])));
        $dirs = self::get_dirs(self::get_current_path());
        $files = self::get_files(self::get_current_path(), $filter);
        $_SESSION['fm']['filter'] = $filter;
        self::assign('dirs', $dirs);
        self::assign('files', $files);
        self::assign('filter', $filter);
        self::assign('breadcrumb', self::gen_breadcrumb(self::get_current_path()));
        self::echo('files');
    }


    /**
     * fm::cmd_reload_folder()
     * 
     * @return void
     */
    public static function cmd_reload_folder() {
        $folder = self::get_current_path();
        $dirs = self::get_dirs($folder);
        $filter = (isset($_SESSION['fm']['filter']) ? $_SESSION['fm']['filter'] : array());
        $files = self::get_files($folder, $filter);
        self::assign('dirs', $dirs);
        self::assign('files', $files);
        self::assign('filter', $filter);
        self::assign('breadcrumb', self::gen_breadcrumb($folder));
        self::echo('files');
    }

    /**
     * fm::cmd_download()
     * 
     * @return void
     */
    public static function cmd_download() {
        self::direct_download(base64_decode($_GET['ident']));
    }

    /**
     * fm::cmd_create_dir()
     * 
     * @return void
     */
    public static function cmd_create_dir() {
        $FORM = self::arr_trim($_POST['FORM']);
        $folder = self::add_trailing_slash($_SESSION['fm']['root']) . self::format_file_name(self::only_alphanums($FORM['dir']));
        if (is_dir($folder)) {
            self::msge('exists');
        }
        else {
            if (!mkdir($folder, 0755)) {
                self::msge('failed');
            }
            else {
                chmod($folder, fm_config::get_config_value('folder_permission'));
                self::msg(array_pop(explode('/', $folder)) . ' created');
            }
        }
        self::ej('fm.reload_folder', '');
    }

    /**
     * fm::cmd_del_dir()
     * 
     * @return void
     */
    public static function cmd_del_dir() {
        $arr = json_decode($_GET['ident'], true);
        $folder = self::add_trailing_slash($_SESSION['fm']['root']) . self::only_alphanums($arr['dir']);
        if (is_dir($folder) && $folder != self::$root_to_files) {
            self::delete_dir_with_subdirs($folder);
        }
        else {
            self::msge(self::relative_path($folder, $_SESSION['fm']['root']) . ' failed');
        }
        self::msg(self::relative_path($folder, $_SESSION['fm']['root']) . ' deleted');
        self::ej();
    }

    /**
     * fm::cmd_rename_file()
     * 
     * @return void
     */
    public static function cmd_rename_file() {
        $arr = json_decode($_GET['ident'], true);
        $file = base64_decode($arr['file']);
        if (is_file($file)) {
            $ext = self::get_ext($file);
            $v = trim(strip_tags($_GET['v']));
            if (strstr($v, '.')) {
                $v = str_replace(self::get_ext($v), '', $v) . $ext;
            }
            else {
                $v .= '.' . $ext;
            }
            $new = str_replace('/' . basename($file), '/' . self::format_file_name($v), $file);
            rename($file, $new);
        }
        self::msg(basename($new) . ' renamed');
        self::ej('fm.on_rename', '"' . self::gen_file_hash($file) . '","' . self::gen_file_hash($new) . '","' . base64_encode($new) . '"');
    }

    /**
     * fm::cmd_rename_dir()
     * 
     * @return void
     */
    public static function cmd_rename_dir() {
        $arr = json_decode($_GET['ident'], true);
        $f = self::format_file_name(trim($_GET['v']));
        $dir = self::$root_to_files . $arr['path'];
        $arr = explode('/', $arr['path']);
        array_pop($arr);
        $new_dir = self::$root_to_files . implode('/', $arr) . '/' . $f;
        if (is_dir($dir)) {
            rename($dir, $new_dir);
        }
        self::msg('renamed');
        self::ej();
    }

    /**
     * fm::cmd_del_file()
     * 
     * @return void
     */
    public static function cmd_del_file() {
        $arr = json_decode($_GET['ident'], true);
        $file = base64_decode($arr['file']);
        if (is_file($file)) {
            @unlink($file);
        }
        else {
            self::msge(basename($file) . ' failed');
        }
        self::msg(basename($file) . ' deleted');
        self::ej();
    }

    /**
     * fm::cmd_mass_del()
     * 
     * @return void
     */
    public static function cmd_mass_del() {
        $arr = json_decode($_GET['ids'], true);
        foreach ($arr as $ident) {
            $file = base64_decode($ident);
            if (is_file($file)) {
                @unlink($file);
            }
            else {
                self::msge(basename($file) . ' failed');
            }
        }
        self::msg('files deleted');
        self::ej();
    }


    /**
     * fm::cmd_upload()
     * 
     * @return void
     */
    public static function cmd_upload() {
        list($result, $msge) = self::validate_upload_file($_FILES['file'], TRUE);
        if ($msge != "") {
            echo json_encode(array('status' => 'failed', 'filename' => $_FILES['file']['name'] . $msge));
            exit();
        }
        $local_file = $_SESSION['fm']['root'] . self::format_file_name($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $local_file)) {
            chmod($local_file, fm_config::get_config_value('file_permission'));
            if (self::get_ext($local_file) == 'jfif') {
                rename($local_file, self::change_file_ext($local_file, 'jpg'));
                $local_file = self::change_file_ext($local_file, 'jpg');
            }
            if (fm_config::get_config_value('resize_images_to_max_size') == true) {
                self::resize_picture_imageick($local_file, $local_file, self::IMG_MAX_WIDTH, self::IMG_MAX_HEIGHT, self::IMG_COMPRESS);
            }
        }
        echo json_encode(array(
            'status' => 'ok',
            'local_file' => self::get_clean_root_file($local_file),
            'filename' => basename($local_file),
            'ident' => md5($local_file),
            'file' => base64_encode($local_file)));
        exit();
    }

    /**
     * fm::cmd_show_newdir()
     * 
     * @return void
     */
    public static function cmd_show_newdir() {
        self::echo('newfolder');
    }

    /**
     * fm::gen_breadcrumb()
     * 
     * @param mixed $str
     * @return
     */
    public static function gen_breadcrumb($str = "") {
        $arr = explode('/', $str);
        $bc = $pp = "";
        foreach ($arr as $a) {
            $pp .= $a . '/';
            $dir = array('dir' => basename($path), 'path' => $pp);
            $bc .= '<li class="breadcrumb-item"><a href="javascript:;" onClick="fm.load_folder(this)" data-info=\'' . htmlspecialchars(json_encode($dir)) . '\'>' . $a .
                '</a></li>';
        }
        $bc = '<ol class="breadcrumb"><li class="breadcrumb-item"><a href="javascript:;" onClick="fm.load_folder(this)" data-info=\'' . htmlspecialchars(json_encode(array
            ('dir' => '/', 'path' => ''))) . '\'>' . rtrim(self::get_realtivepath_to_files(), '/') . '</a></li>' . $bc . '</ol>';
        return $bc;
    }


    /**
     * fm::get_files()
     * 
     * @param string $folder
     * @return
     */
    public static function get_files($folder = "", $filter = array()) {
        $folder = ltrim($folder, DIRECTORY_SEPARATOR);
        $arr = array();
        foreach (self::$fext as $key => $fext) {
            foreach ($fext['ext'] as $ext) {
                $all_ext[$ext] = $key;
            }
        }

        $filter_set = false;
        if (count($filter) > 0) {
            $it = new RecursiveDirectoryIterator(fm::$root . $folder);
            foreach (new RecursiveIteratorIterator($it) as $fileinfo) {
                if ($fileinfo->getFilename() == '.' || $fileinfo->getFilename() == '..') {
                    continue;
                }
                if ($_SESSION['fm']['set']['nr'] == true && strstr(str_replace(fm::$root . $folder, '', $fileinfo->getPathname()), '/')) {
                    continue;
                }
                if (isset($filter['q']) && $filter['q'] != "") {
                    $filter_set = true;
                    if (str_replace($filter['q'], '*', $fileinfo) != $fileinfo) {
                        fm_info::add_bytes($fileinfo->getSize());
                        $arr[self::gen_file_hash($fileinfo->getPathname())] = self::set_file_opt($fileinfo, $all_ext);
                    }
                }
                $type = self::get_file_type($fileinfo, $all_ext);
                if (isset($filter['types'][$type]) && (int)$filter['types'][$type] == 1) {
                    $filter_set = true;
                    fm_info::add_bytes($fileinfo->getSize());
                    $arr[self::gen_file_hash($fileinfo->getPathname())] = self::set_file_opt($fileinfo, $all_ext);
                }
                if (array_sum($filter['types']) > 0) {
                    $filter_set = true;
                }
            }
        }
        if ($filter_set == false) {
            foreach (new DirectoryIterator(fm::$root . $folder) as $fileinfo) {
                if ($fileinfo->isDot())
                    continue;
                if ($fileinfo->isFile()) {
                    fm_info::add_bytes($fileinfo->getSize());
                    $arr[self::gen_file_hash($fileinfo->getPathname())] = self::set_file_opt($fileinfo, $all_ext);
                }
            }
        }
        fm_info::set_file_count(count($arr));
        self::assign('folderinfo', fm_info::get_infos());
        return $arr;
    }

    /**
     * fm::check_access()
     * 
     * @return void
     */
    private static function check_access() {
        if (!isset($_REQUEST['akey']) || (isset($_REQUEST['akey']) && $_REQUEST['akey'] != fm_config::get_access_key())) {
            die('Access denied. Just reload page or check access key.' . date('H:i:s'));
        }
    }

}
