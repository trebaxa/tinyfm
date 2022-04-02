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
 
if (count(fm::$msge) > 0 || count(fm::$msg) > 0) {
    echo '<section id="msg-handler"><div class="container">';
    if (count(fm::$msge) > 0) {
        echo '<div class="alert alert-danger">' . implode('<br>', fm::$msge) . '</div>';
    }

    if (count(fm::$msg) > 0) {
        echo '<div class="alert alert-success">' . implode('<br>', fm::$msge) . '</div>';
    }
    echo '</div></section>';
}
