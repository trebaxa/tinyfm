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

class fm_master {
    const IMG_MAX_WIDTH = '4000';
    const IMG_MAX_HEIGHT = '4000';
    const IMG_COMPRESS = '90';

    public static $fm = array();
    public static $fm_root = "";
    public static $root_to_files = "";
    public static $eurl = "";
    public static $server = "";
    public static $url = "";
    public static $root = "";
    public static $msge = array();
    public static $msg = array();
    public static $plugins = "";
    public static $standalone = false;

    protected static $fext = array(
        'image' => array(
            'function' => 'apply_file',
            'icon' => 'icofont-image',
            'ext' => array(
                'jpg',
                'jpeg',
                'webp',
                'tiff',
                'bmp',
                'ico',
                'png',
                'bmp',
                'svg',
                'gif'),
            ),
        'video' => array(
            'function' => 'apply_file',
            'icon' => 'file-avi-mp4',
            'ext' => array(
                'mov',
                'm4v',
                'wma',
                'flv',
                'mp4',
                'avi',
                'webm',
                '3gp',
                'wmv',
                'mpeg',
                'mkv',
                'mpg',
                'mpe'),
            ),
        'music' => array(
            'function' => 'apply_file',
            'icon' => 'icofont-music',
            'ext' => array(
                'mp3',
                'mpga',
                'm4a',
                'ac3',
                'aiff',
                'mid',
                'ogg',
                'wav')),
        'file' => array(
            'function' => 'apply_file',
            'icon' => 'icofont-file-document',
            'ext' => array()),
        'archive' => array(
            'function' => 'apply_file',
            'icon' => 'icofont-archive',
            'ext' => array(
                'iso',
                'dmg',
                'zip',
                'rar',
                '7z',
                'tar',
                'tar.gz')),
        );

    protected static $umlaute = array(
        "ä" => 'ae',
        'ö' => 'oe',
        'ü' => 'ue',
        'ß' => 'ss',
        ',' => '-');
    /**
     * fm_master::__construct()
     * 
     * @return void
     */
    function __construct() {
    }


    /**
     * fm_master::set_standalone()
     * 
     * @param mixed $s
     * @return void
     */
    protected static function set_standalone($s) {
        self::$standalone = (int)$s == 1;
        $_SESSION['fm']['standalone'] = self::$standalone;
    }

    /**
     * fm_master::is_standalone()
     * 
     * @return
     */
    public static function is_standalone() {
        if (isset($_SESSION['fm']['standalone']))
            self::$standalone = $_SESSION['fm']['standalone'];
        return self::$standalone;
    }

    /**
     * fm_master::change_file_ext()
     * 
     * @param mixed $file
     * @param mixed $ext
     * @return
     */
    public static function change_file_ext($file, $ext) {
        $org_ext = self::get_ext($file);
        $file = str_replace('.' . $org_ext, '', $file);
        return $file . '.' . strtolower($ext);
    }

    /**
     * fm_master::get_target()
     * 
     * @return
     */
    public static function get_target() {
        if (isset($_GET['t'])) {
            return trim(strip_tags((string )$_GET['t']));
        }
        else {
            return "";
        }

    }

    /**
     * fm_master::direct_download()
     * 
     * @param mixed $file
     * @param bool $download
     * @return void
     */
    public static function direct_download($file, $download = true) {
        if (file_exists($file)) {
            $dis = "attachment";
            switch (self::get_ext($file)) {
                case 'pdf':
                    $ctype = "pdf";
                    $dis = "inline";
                    break;
                case 'zip':
                    $ctype = "zip";
                    break;
                default:
                    $ctype = "octet-stream";
            }
            header('Content-Description: File Transfer');
            header('Content-Type: application/' . $ctype);
            if ($download == true) {
                header('Content-Disposition: ' . $dis . '; filename="' . basename($file) . '"');
            }
            else {
                header('Content-Disposition: inline; filename="' . basename($file) . '"');
            }
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_end_flush();
            readfile($file);
        }
        else {
            echo 'No file found:' . basename($file);
        }
        exit();
    }

    /**
     * fm_master::get_file_type()
     * 
     * @param mixed $fileinfo
     * @return
     */
    protected static function get_file_type($fileinfo, $all_ext) {
        $type = $all_ext[strtolower($fileinfo->getExtension())];
        $type = ($type == "") ? 'file' : $type;
        return $type;
    }

    /**
     * fm_master::load_plugins()
     * 
     * @return void
     */
    public static function load_plugins() {
        $str = "";
        $d = dir(static::$fm_root . "plugins");
        while (false !== ($entry = $d->read())) {
            if (is_file($d->path . '/' . $entry . '/plugin.js')) {
                $str .= '<script src="' . self::$server . 'plugins/' . $entry . '/plugin.js?a=' . time() . '"></script>';
            }
        }
        $d->close();
        return $str;
    }

    /**
     * fm_master::gen_file_hash()
     * 
     * @param mixed $file
     * @return
     */
    public static function gen_file_hash($file) {
        return md5($file);
    }

    /**
     * fm_master::set_file_opt()
     * 
     * @param mixed $fileinfo
     * @param mixed $all_ext
     * @return void
     */
    protected static function set_file_opt($fileinfo, $all_ext) {
        $p = DIRECTORY_SEPARATOR . fm_config::get_config_value('path_to_files');
        $path = substr($fileinfo->getPathname(), stripos($fileinfo->getPathname(), $p));
        $path = str_ireplace(array($p, basename($path)), '', $path);
        $type = self::get_file_type($fileinfo, $all_ext);
        $imgsize = array();
        switch ($type) {
            case 'image':
                if ($fileinfo->getExtension() == 'svg') {
                    $preview = '<img data-path="' . $path . '" alt="' . $fileinfo->getFilename() . '" src="' . self::get_domain() . fm_config::get_config_value('path_to_files') . $path .
                        $fileinfo->getFilename() . '" class="img-fluid"/>';
                    $thumb = self::get_domain() . fm_config::get_config_value('path_to_files') . $path . $fileinfo->getFilename();
                }
                else {
                    $thumb = self::img_crop($fileinfo->getPathname(), static::$fm_root . 'cache/crop_' . self::gen_file_hash($fileinfo->getPathname()) . '.' . $fileinfo->
                        getExtension(), 370, 370);
                    $preview = '<img data-path="' . $path . '" alt="' . $fileinfo->getFilename() . '" src="' . $thumb . '" class="img-fluid"/>';
                    $imgsize = getimagesize($fileinfo->getPathname());
                }
                break;
            case 'file':
                $preview = '<i data-path="' . $path . '" class="icofont-file-file icofont-5x"></i>';
                break;
            case 'video':
                $preview = '<i data-path="' . $path . '" class="icofont-file-avi-mp4 icofont-5x"></i>';
                break;
            default:
                $preview = '<i data-path="' . $path . '" class="icofont-file-file icofont-5x"></i>';
                break;
        }

        $info = array(
            'hash' => self::gen_file_hash($fileinfo->getPathname()),
            'thumb' => $thumb,
            'imgsize' => $imgsize,
            'filename' => $fileinfo->getFilename(),
            'ext' => $fileinfo->getExtension(),
            'path' => self::add_trailing_slash($path),
            'link' => self::get_domain() . rtrim(fm_config::get_config_value('path_to_files'), '/') . self::add_trailing_slash($path, true) . $fileinfo->getFilename(),
            'ident' => base64_encode($fileinfo->getPathname()),
            'localfile' => $fileinfo->getPathname(),
            'localfileb64' => base64_encode($fileinfo->getPathname()),
            'type' => $type,
            'def' => self::$fext[$type],
            'info' => array(
                'size' => self::human_filesize(filesize($fileinfo->getPathname())),
                'time' => date(fm_config::get_config_value('dateformat') . " H:i:s", filemtime($fileinfo->getPathname())),
                'resolution' => $imgsize[0] . 'x' . $imgsize[1],
                ),
            'preview' => $preview);
        if (count($imgsize) == 0) {
            unset($info['info']['res']);
        }
        return $info;
    }

    /**
     * fm_master::img_resize()
     * 
     * @param mixed $filename
     * @param mixed $dest_file
     * @param mixed $maxWidth
     * @param mixed $maxHeight
     * @param integer $compress_rate
     * @return
     */
    public static function img_resize($filename, $dest_file, $maxWidth, $maxHeight, $compress_rate = 85) {
        if (is_file($dest_file) && filesize($dest_file) > 0) {
            return self::get_tinyfm_url() . 'cache/' . basename($dest_file);
        }
        else {
            $image = new Zebra_Image();
            $image->source_path = $filename;
            $image->target_path = $dest_file;
            $image->jpeg_quality = $compress_rate;
            $image->png_compression = 8;
            $image->webp_quality = 80;
            $image->preserve_aspect_ratio = true;
            $image->auto_handle_exif_orientation = true;
            $image->enlarge_smaller_images = false;
            $image->preserve_time = true;
            # $image->handle_exif_orientation_tag = true;
            list($width, $height, $type, $attr) = getimagesize($filename);
            if ($width == 0)
                return $dest_file;
            $newHeight = $height / $width * $maxWidth;

            if ($newHeight > $maxHeight) {
                $res = $image->resize(0, $maxHeight);
                #  echo $width.'x'.$height.' ' .$newHeight. ' '.$maxHeight;die;
            }
            else {
                $res = $image->resize($maxWidth, 0);
            }
            # self::zebra_report($res);

            return self::get_tinyfm_url() . 'cache/' . basename($dest_file);
        }
    }

    /**
     * fm_master::func_is_available()
     * 
     * @param mixed $func
     * @return
     */
    private static function func_is_available($func) {
        if (ini_get('safe_mode'))
            return false;
        $disabled = ini_get('disable_functions');
        if ($disabled) {
            $disabled = explode(',', $disabled);
            $disabled = array_map('trim', $disabled);
            return !in_array($func, $disabled);
        }
        return true;
    }

    /**
     * fm_master::get_clean_root_file()
     * 
     * @param mixed $src_file
     * @return
     */
    public static function get_clean_root_file($src_file) {
        return str_replace(array(basename($src_file), realpath($_SERVER['DOCUMENT_ROOT'])), '', $src_file) . basename($src_file);
    }

    /**
     * fm_master::resize_picture_imageick()
     * 
     * @param mixed $filename
     * @param mixed $dest_file
     * @param mixed $maxWidth
     * @param mixed $maxHeight
     * @param integer $compress_rate
     * @return
     */
    public static function resize_picture_imageick($filename, $dest_file, $maxWidth, $maxHeight, $compress_rate = 85) {
        $filename = ltrim(str_replace(fm_config::get_config_value('path_to_files'), fm_config::get_config_value('relative_path_to_files'), self::get_clean_root_file($filename)),
            '/');
        if (self::get_ext($filename) != 'svg') {
            if (!self::func_is_available('system')) {
                ini_set('memory_limit', '256M');
                if (extension_loaded('imagick')) {
                    try {
                        $img = new Imagick($filename);
                        $img->setCompressionQuality($compress_rate);
                        $img->thumbnailImage(min($img->getImageWidth(), $max_width), min($img->getImageHeight(), $max_height), TRUE);
                        $img->writeImage($dest_file);
                        $img->readImage($dest_file);
                        if ($img->getImageColorspace() != \Imagick::COLORSPACE_SRGB) {
                            $img->transformimagecolorspace(\Imagick::COLORSPACE_SRGB);
                            $img->writeImage($dest_file);
                        }

                    }
                    catch (Exception $e) {
                        #  echo 'Caught exception: ', $e->getMessage(), "n";
                    }
                }
                else {
                    # GD Resize
                    self::img_resize($filename, $dest_file, $maxWidth, $maxHeight);
                }

            }
            else {
                $cmd = "convert -auto-orient " . $filename . " -colorspace sRGB -quality " . $compress_rate . "% -strip -resize '" . $maxWidth . "x" . $maxHeight .
                    ">' -colorspace sRGB " . $dest_file;

                $lastLine = system($cmd, $retval);
            }
        }
        return $filename;
    }

    /**
     * fm_master::enough_memory()
     * 
     * @param mixed $file
     * @param mixed $max_width
     * @param mixed $max_height
     * @return
     */
    private static function enough_memory($file, $max_width, $max_height) {
        if (file_exists($file)) {
            $kilobyte64 = 65536; // number of bytes in 64K
            $memory_usage = memory_get_usage();
            if (ini_get('memory_limit') > 0) {
                $mem = ini_get('memory_limit');
                $memlimit = 0;
                if (strpos($mem, 'M') !== false)
                    $memlimit = abs(intval(str_replace(array('M'), '', $mem) * 1024 * 1024));
                if (strpos($mem, 'G') !== false)
                    $memlimit = abs(intval(str_replace(array('G'), '', $mem) * 1024 * 1024 * 1024));
                $imgsize = getimagesize($file);
                $image_bits = (isset($imgsize['bits'])) ? $imgsize['bits'] : 0;
                $image_memory_usage = $kilobyte64 + ($imgsize[0] * $imgsize[1] * ($image_bits >> 3) * 2);
                $thumb_memory_usage = $kilobyte64 + ($max_width * $max_height * ($image_bits >> 3) * 2);
                $memory_needed = abs(intval($memory_usage + $image_memory_usage + $thumb_memory_usage));
                if ($memory_needed > $memlimit) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * fm_master::crop()
     * 
     * @param mixed $filename
     * @param mixed $dest_file
     * @param mixed $max_width
     * @param mixed $max_height
     * @param string $gravity
     * @return
     */
    public static function img_crop($filename, $dest_file, $max_width, $max_height, $gravity = "center") {
        if (is_file($dest_file) && filesize($dest_file) > 0) {
            return self::get_tinyfm_url() . 'cache/' . basename($dest_file);
        }
        else {
            if (self::enough_memory($filename, $max_width, $max_height) == true) {
                $image = new Zebra_Image();
                $image->source_path = $filename;
                $image->target_path = $dest_file;
                $image->jpeg_quality = 80;
                $image->preserve_aspect_ratio = true;
                $image->enlarge_smaller_images = false;
                $image->preserve_time = true;
                $gravity = strtolower($gravity);
                switch ($gravity) {
                    case "center":
                        $pos = ZEBRA_IMAGE_CROP_CENTER;
                        break;
                    case "northwest":
                        $pos = ZEBRA_IMAGE_CROP_TOPLEFT;
                        break;
                    case "north":
                        $pos = ZEBRA_IMAGE_CROP_TOPCENTER;
                        break;
                    case "northeast":
                        $pos = ZEBRA_IMAGE_CROP_TOPRIGHT;
                        break;
                    case "west":
                        $pos = ZEBRA_IMAGE_CROP_MIDDLELEFT;
                        break;
                    case "east":
                        $pos = ZEBRA_IMAGE_CROP_MIDDLERIGHT;
                        break;
                    case "southwest":
                        $pos = ZEBRA_IMAGE_CROP_BOTTOMLEFT;
                        break;
                    case "south":
                        $pos = ZEBRA_IMAGE_CROP_BOTTOMCENTER;
                        break;
                    case "southeast":
                        $pos = ZEBRA_IMAGE_CROP_BOTTOMRIGHT;
                        break;
                }
                $pos = ZEBRA_IMAGE_CROP_CENTER;
                $res = $image->resize($max_width, $max_height, $pos); # self::zebra_report($res);
                return self::get_tinyfm_url() . 'cache/' . basename($dest_file);
            }
            else {
                $p = DIRECTORY_SEPARATOR . fm_config::get_config_value('path_to_files');
                $path = substr($filename, stripos($filename, $p));
                $path = str_ireplace(array($p, basename($path)), '', $path);
                return self::get_domain() . rtrim(fm_config::get_config_value('path_to_files'), '/') . self::add_trailing_slash($path, true) . basename($filename);

            }
        }
    }

    /**
     * fm_master::msg()
     * 
     * @param mixed $str
     * @return void
     */
    public static function msg($str) {
        self::$msg[] = trim($str);
    }

    /**
     * fm_master::msge()
     * 
     * @param mixed $str
     * @return void
     */
    public static function msge($str) {
        self::$msge[] = trim($str);
    }

    /**
     * fm_master::echo()
     * 
     * @param mixed $tpl
     * @return void
     */
    public static function include($tpl) {
        include (static::$fm_root . 'tpl/fm.' . $tpl . '.php');
    }

    /**
     * fm_master::echo()
     * 
     * @param mixed $tpl
     * @return void
     */
    public static function echo($tpl) {
        self::include('err');
        self::include($tpl);
        self::include('js');
        exit(0);
    }

    /**
     * fm_master::get_realtivepath_to_files()
     * 
     * @return
     */
    public static function get_realtivepath_to_files() {
        $r = realpath($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . fm_config::get_config_value('path_to_files'));
        $r = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', $r);
        $b = self::add_trailing_slash($r, true);
        return $b;
    }

    /**
     * fm_master::get_folder_size()
     * 
     * @param mixed $folder
     * @return
     */
    protected static function get_folder_size($folder) {
        $total_size = $k = 0;
        foreach (new DirectoryIterator($folder) as $file) {
            if ($file->isFile()) {
                $total_size += $file->getSize();
                $k++;
            }
        }
        return array('total_size' => $total_size, 'file_count' => $k);
    }

    /**
     * fm_master::get_dirs()
     * 
     * @param string $folder
     * @return
     */
    public static function get_dirs($folder = "") {
        $folder = ltrim($folder, DIRECTORY_SEPARATOR);
        $arr = array();
        foreach (new DirectoryIterator(fm::$root . $folder) as $fileinfo) {
            if ($fileinfo->isDot())
                continue;
            if ($fileinfo->isDir()) {
                $p = self::get_realtivepath_to_files();
                $pos = stripos($fileinfo->getPathname(), $p);
                $path = substr($fileinfo->getPathname(), $pos);
                $path = str_ireplace($p, '', $path);
                $info = self::get_folder_size($fileinfo->getPathname());
                $arr[] = array(
                    'dir' => $fileinfo->getFilename(),
                    'path' => $path,
                    'total_size' => self::human_filesize($info['total_size']),
                    'file_count' => $info['file_count']);
            }
        }
        return $arr;
    }

    /**
     * fm_master::only_alphanums()
     * 
     * @param mixed $string
     * @return
     */
    public static function only_alphanums($string) {
        $string = trim(strtr($string, static::$umlaute));
        $string = preg_replace("/[^0-9a-zA-Z-]/", "", strval($string));
        return $string;
    }

    /**
     * fm_master::arr_trim()
     * 
     * @param mixed $arr
     * @return
     */
    public static function arr_trim($arr) {
        foreach ((array )$arr as $key => $wert)
            if (!is_array($wert)) {
                $arr[$key] = trim($arr[$key]);
            }
            else {
                $arr[$key] = self::arr_trim($arr[$key]);
            }
            return $arr;
    }

    /**
     * fm_master::ej()
     * 
     * @param string $java_call_func
     * @param string $jsparams
     * @return void
     */
    public static function ej($java_call_func = '', $jsparams = '') {
        if (count(self::$msg) == 0) {
            self::msg('saved');
        }
        $arr = array(
            'msg' => implode('<br>', (array )self::$msg),
            'msge' => implode('<br>', (array )self::$msge),
            'jsfunction' => $java_call_func,
            'jsparams' => $jsparams);
        self::$msg = array();
        self::$msge = array();
        echo json_encode($arr);
        exit(0);
    }


    /**
     * fm_master::get_tinyfm_url()
     * 
     * @param bool $remove_trail
     * @return
     */
    public static function get_tinyfm_url($remove_trail = false) {
        $parsedUrl = parse_url($_SERVER['HTTP_HOST']);
        $host = explode('.', $parsedUrl['path']);
        $subdomains = array_slice($host, 0, count($host) - 2);
        if (count($subdomains) == 0) {
            $ret = self::get_http_protocol() . "://www." . self::add_trailing_slash($_SERVER['HTTP_HOST']) . self::add_trailing_slash(fm_config::get_config_value('path'));
        }
        else {
            $ret = self::get_http_protocol() . "://" . self::add_trailing_slash($_SERVER['HTTP_HOST']) . self::add_trailing_slash(fm_config::get_config_value('path'));
        }
        if ($remove_trail == true) {
            $ret = rtrim($ret, DIRECTORY_SEPARATOR);
        }
        #  self::get_domain();
        return $ret;
    }

    /**
     * fm_master::get_cms_url()
     * 
     * @param bool $remove_trail
     * @return
     */
    public static function get_cms_url($remove_trail = false) {
        $parsedUrl = parse_url($_SERVER['HTTP_HOST']);
        $host = explode('.', $parsedUrl['path']);
        $subdomains = array_slice($host, 0, count($host) - 2);
        if (count($subdomains) == 0) {
            $ret = self::get_http_protocol() . "://www." . self::add_trailing_slash($_SERVER['HTTP_HOST']);
        }
        else {
            $ret = self::get_http_protocol() . "://" . self::add_trailing_slash($_SERVER['HTTP_HOST']);
        }
        if ($remove_trail == true) {
            $ret = rtrim($ret, DIRECTORY_SEPARATOR);
        }
        #  self::get_domain();
        return $ret;
    }

    /**
     * fm_master::human_filesize()
     * 
     * @param mixed $bytes
     * @param integer $decimals
     * @return
     */
    public static function human_filesize($bytes, $decimals = 2) {
        $size = array(
            'Bytes',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    /**
     * fm_master::echoarr()
     * 
     * @return void
     */
    public static function echoarr($arr) {
        echo '<pre>' . print_r($arr, true) . '</pre>';
    }

    /**
     * fm::assign()
     * 
     * @param mixed $key
     * @param mixed $val
     * @return void
     */
    public static function assign($key, $val) {
        static::$fm[$key] = $val;
    }

    /**
     * fm_master::get_domain()
     * 
     * @return
     */
    public static function get_domain() {
        return self::get_http_protocol() . "://" . self::add_trailing_slash($_SERVER['HTTP_HOST']);
    }


    /**
     * fm_master::truncate()
     * 
     * @param mixed $string
     * @param integer $length
     * @param string $etc
     * @param bool $break_words
     * @param bool $middle
     * @return
     */
    public static function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false) {
        if ($length === 0) {
            return '';
        }

        if (isset($string[$length])) {
            $length -= min($length, strlen($etc));
            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            }
            if (!$middle) {
                return substr($string, 0, $length) . $etc;
            }
            return substr($string, 0, $length / 2) . $etc . substr($string, -$length / 2);
        }
        return $string;
    }

    /**
     * fm_master::delete_dir_with_subdirs()
     * 
     * @param mixed $dir
     * @return void
     */
    public static function delete_dir_with_subdirs($dir) {
        if (is_dir($dir)) {
            $dir = (substr($dir, -1) != "/") ? $dir . "/" : $dir;
            $openDir = opendir($dir);
            while ($file = readdir($openDir)) {
                if (!in_array($file, array(".", ".."))) {
                    if (!is_dir($dir . $file))
                        @unlink($dir . $file);
                    else
                        self::delete_dir_with_subdirs($dir . $file);
                }
            }
            closedir($openDir);
            @rmdir($dir);
        }
    }

    /**
     * fm_master::get_http_protocol()
     * 
     * @return
     */
    public static function get_http_protocol() {
        return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    }

    /**
     * fm_master::relative_path()
     * 
     * @param mixed $folder
     * @return
     */
    protected static function relative_path($folder, $root = "") {
        $root = ($root == "") ? self::$root_to_files : $root;
        return str_replace($root, '', $folder);
    }

    /**
     * fm_master::arr_trimsthsc()
     * 
     * @param mixed $arr
     * @return
     */
    protected static function arr_trimsthsc($arr) {
        foreach ((array )$arr as $key => $wert)
            if (!is_array($wert)) {
                $arr[$key] = trim(htmlspecialchars(strip_tags($arr[$key])));
            }
            else {
                $arr[$key] = self::arr_trimsthsc($arr[$key]);
            }
            return $arr;
    }

    /**
     * fm_master::get_current_path()
     * 
     * @return
     */
    protected static function get_current_path() {
        return self::relative_path($_SESSION['fm']['root']);
    }


    /**
     * fm_master::get_ext()
     * 
     * @param mixed $filename
     * @return
     */
    public static function get_ext($filename) {
        return strtolower(substr(strrchr($filename, "."), 1));
    }

    /**
     * fm_master::add_trailing_slash()
     * 
     * @param mixed $str
     * @param bool $first
     * @return
     */
    public static function add_trailing_slash($str, $first = false) {
        $str .= (substr($str, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR);
        if ($first == true && substr($str, 0, 1) != DIRECTORY_SEPARATOR) {
            $str = DIRECTORY_SEPARATOR . $str;
        }
        return (string )$str;
    }

    /**
     * fm_master::validate_upload_file()
     * 
     * @param mixed $_FILE_ARR
     * @param bool $sizelimit
     * @return
     */
    protected static function validate_upload_file($_FILE_ARR, $sizelimit = false) {
        $msge = "";
        if (isset($_FILE_ARR['error']) && $_FILE_ARR['error'] > 0) {
            if (isset($_FILE_ARR['error']) && $_FILE_ARR['error'] == UPLOAD_ERR_NO_FILE) {
                return array(true, $msge);
            }
            switch ($_FILE_ARR['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $msge .= 'The uploaded file exceeds the upload_max_filesize directive in php.ini. ';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $msge .= ' The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $msge .= 'The uploaded file was only partially uploaded. ';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $msge .= 'No file was uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $msge .= 'Missing a temporary folder.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $msge .= 'Failed to write file to disk. ';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $msge .= 'A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. ';
                    break;
            }
        }
        if ($_FILE_ARR['name'] == "") {
            return array(true, $msge);
        }
        $sizebytes = fm_config::get_config_value('dropzone.maxFilesize') * 1024 * 1024;
        $ext = strtolower('.' . self::get_ext($_FILE_ARR['name']));
        $ret = true;
        $acf = fm_config::get_config_value('dropzone.acceptedFiles');
        if (in_array($ext, explode(',', fm_config::get_config_value('dropzone.forbidden_ext'))) || ($acf != "" && !in_array($ext, explode(',', $acf)))) {
            $msge .= '[' . $_FILE_ARR['type'] . '] | ".' . self::get_ext($_FILE_ARR['name']) . '" not allowed.';
            return array(false, $msge);
        }
        if (($sizelimit == true) && ($_FILE_ARR['size'] > $sizebytes)) {
            $msge .= 'Filesize to big: ' . self::human_filesize($_FILE_ARR['size']) . '. Allowed: ' . self::human_filesize($sizebytes);
            return array(false, $msge);
        }
        return array($ret, $msge);
    }


    /**
     * fm_master::clean_cache()
     * 
     * @param mixed $folder
     * @param integer $max_files
     * @param mixed $only_delete
     * @return void
     */
    protected static function clean_cache($folder = "", $max_files = 600, $only_delete = array()) {
        if (!isset($_SESSION['cache_cleared'])) {
            $folder = (empty($folder) ? static::$fm_root . 'cache/' : $folder);
            $not_delete = array('.htaccess');
            $CacheDirOldFilesAge = array();
            if ($dirhandle = opendir($folder)) {
                while (false !== ($oldcachefile = readdir($dirhandle))) {
                    $forbidden_found = false;
                    foreach ($not_delete as $forbidden) {
                        if (strstr($oldcachefile, $forbidden)) {
                            $forbidden_found = true;
                            break;
                        }
                    }
                    $allowed_found = false;
                    if (is_array($only_delete)) {
                        foreach ($only_delete as $allowed) {
                            if (strstr($oldcachefile, $allowed)) {
                                $allowed_found = true;
                                break;
                            }
                        }
                    }
                    else
                        $allowed_found = true;
                    if ($forbidden_found == false && $allowed_found == true) {
                        $CacheDirOldFilesAge[$oldcachefile] = fileatime($folder . $oldcachefile);
                        if ($CacheDirOldFilesAge[$oldcachefile] == 0) {
                            $CacheDirOldFilesAge[$oldcachefile] = filemtime($folder . $oldcachefile);
                        }
                    }
                }
            }
            asort($CacheDirOldFilesAge);
            $TotalCachedFiles = count($CacheDirOldFilesAge);
            $DeletedKeys = array();
            foreach ($CacheDirOldFilesAge as $oldcachefile => $filedate) {
                if ($TotalCachedFiles > $max_files) {
                    $TotalCachedFiles--;
                    if ($oldcachefile != '.' && $oldcachefile != '..' && !is_dir($folder . $oldcachefile)) {
                        if (file_exists($folder . $oldcachefile))
                            @unlink($folder . $oldcachefile);
                    }
                }
                else {
                    break;
                }
            }
            clearstatcache();
        }
        $_SESSION['cache_cleared'] = true;
    }

    /**
     * fm_master::format_file_name()
     * 
     * @param mixed $filename
     * @param bool $beautify
     * @return
     */
    protected static function format_file_name($filename, $beautify = true) {
        $filename = (string )$filename;
        $filename = mb_strtolower($filename, 'UTF-8');
        $filename = trim(strtr($filename, static::$umlaute));
        // sanitize filename
        $filename = preg_replace('~
        [<>:"/\\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://www.rfc-editor.org/rfc/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x', '-', $filename);
        // avoids ".", ".." or ".hiddenFiles"
        $filename = ltrim($filename, '.-');
        // optional beautification
        if ($beautify == true) {
            $filename = self::beautify_filename($filename);
        }
        // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
        return $filename;
    }

    /**
     * fm_master::beautify_filename()
     * 
     * @param mixed $filename
     * @return
     */
    protected static function beautify_filename($filename) {
        // reduce consecutive characters
        $filename = preg_replace(array(
            // "file   name.zip" becomes "file-name.zip"
            '/ +/',
            // "file___name.zip" becomes "file-name.zip"
            '/_+/',
            // "file---name.zip" becomes "file-name.zip"
            '/-+/'), '-', $filename);
        $filename = preg_replace(array( // "file--.--.-.--name.zip" becomes "file.name.zip"
                '/-*\.-*/', // "file...name..zip" becomes "file.name.zip"
                '/\.{2,}/'), '.', $filename);
        // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
        $filename = mb_strtolower($filename, mb_detect_encoding($filename));
        // ".file-name.-" becomes "file-name"
        $filename = trim($filename, '.-');
        return $filename;
    }

}
