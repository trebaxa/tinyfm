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

echo '
<div class="cards-info d-flex">      
      <div class="flex-grow-1">            
        files: '.fm::$fm['folderinfo']['file_count'].' | totalsize: '.fm::human_filesize(fm::$fm['folderinfo']['totalbytes']).' 
      </div> 
      <div class="text-right"></div>      
</div>
';