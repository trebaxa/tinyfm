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

if (count(fm::$fm['dirs'])>0 || count(fm::$fm['files'])>0) {

echo '<div id="js-tools"></div>
<div class="fm-files fm-table-view" id="fm-files">
 <table class="table table-hover table-bordered">
    <thead>
        <tr>
            <th class="w-1"></th>         
            <th>label</th>
            <th>size</th>
            <th>info</th>
            <th class="w-3"></th>
        </tr>
    </thead>
    <tbody>    
';

    foreach (fm::$fm['dirs'] as $dir) {
        echo '<tr class="js-fm-card">
                <td><a data-bs-toggle="tooltip" title="open folder" class="folder" href="javascript:;" style="display:block" onClick="fm.load_folder(this)" data-info=\'' . htmlspecialchars(json_encode($dir)) . '\'><i class="icofont-ui-folder"></i></a></td>
                <td>' . $dir['dir'] . '</td>
                <td>'.$dir['total_size'].'</td>
                <td>file count: '.$dir['file_count'].'</td>
                <td class="text-right">
                    <div class="dropdown card-dropdown">
                      <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButtontable1" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtontable1">
                        <li><a data-bs-toggle="tooltip" href="javascript:;" title="delete" data-cmd="del_dir" data-info=\'' . htmlspecialchars(json_encode($dir)) . '\' class="js-del-link dropdown-item"><i class="icofont-ui-delete"></i> delete</a></li>
                        <li><a class="dropdown-item js-dir-file" href="javascript:;" data-filename="'.htmlspecialchars( $dir['dir']).'" data-info=\'' . htmlspecialchars(json_encode($dir))  . '\' ><i class="icofont-edit"></i> rename</a></li>
                      </ul>
                    </div>
                </td>    
          </tr>
          ';
    }
     foreach (fm::$fm['files'] as $file) {
        $info="";
        foreach ($file['info'] as $key => $val) {
            $info.=$key.': '.$val.'<br>';
        }
        $info = htmlspecialchars($info);
        if ($file['type']=='image') {
          if (fm::is_standalone()) {
            $con = '<a class="fancy" href="'.$file['link'].'" data-fancybox="gallery"><img src="'.$file['thumb'].'" class="img-fluid thumb image cursor-pointer"></a> ';            
          } else {
            $con = '<img src="'.$file['thumb'].'" class="img-fluid js-single-select thumb image cursor-pointer"> ';
          }            
        } else {
            $con = '<div class="icon image '.((fm::is_standalone()) ? '' : 'js-single-select ' ).'">' . $file['preview'] . '</div> ';
        }
        echo '<tr class="js-fm-card" 
        data-localfile="'.$file['localfileb64'].'" 
        data-ident="'.$file['ident'].'" 
        data-hash="'.$file['hash'].'"
        data-url="'.$file['link'].'" 
        data-type="'.$file['type'].'" 
        data-function="'.$file['def']['function'].'">
            <td>'.((fm::is_standalone()) ? '<input type="checkbox" class="select js-file-checkbox" name="" value="1">' : '').'</td>
            <td>'.$con.''.((fm::is_standalone()) ? '<a href="' . fm::$eurl . 'akey='.fm_config::get_access_key().'&cmd=download&ident='.$file['ident'].'" title="download">'.$file['filename'].'</a>' : '<a href="javascript:;" class="js-single-select">' . $file['filename'] . '</a>').'</td>
            <td>'.$file['info']['size'].'</td>
            <td>'.$file['info']['time'].''.((isset($file['imgsize'][0])) ? '<br>'.$file['info']['resolution'] : '').'</td>
            <td class="text-right">
                <div class="dropdown card-dropdown">
                  <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButtontable" data-bs-toggle="dropdown" aria-expanded="false"></button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButtontable">
                    <li><a data-bs-toggle="tooltip" href="javascript:;" title="delete" data-cmd="del_file" data-info=\'' . htmlspecialchars(json_encode(array('file' => $file['ident'],'hash' => $file['hash']))) . '\' class="dropdown-item js-del-link"><i class="icofont-ui-delete"></i> delete</a></li>
                    <li><a class="dropdown-item js-rename-file" href="javascript:;" data-filename="'.htmlspecialchars( $file['filename']).'" data-info=\'' . htmlspecialchars(json_encode(array('file' => $file['ident'],'hash' => $file['hash']))) . '\' ><i class="icofont-edit"></i> rename</a></li>
                    <li><a class="dropdown-item" href="' . fm::$eurl . 'akey='.fm_config::get_access_key().'&cmd=download&ident='.$file['ident'].'" title="download"><i class="icofont-download"></i> download</a></li>
                    <li><a class="dropdown-item" href="javascript:navigator.clipboard.writeText(\''.$file['link'].'\');" title="copy to clipboard"><i class="icofont-copy"></i> copy link</a></li>
                    <!--<li>'.(($file['type']=='image') ? '<a class="dropdown-item" onclick="$(\'a.fancy:first\').trigger(\'click\');" href="javascript:;" title="view"><i class="icofont-eye"></i> view</a>' : '<a class="dropdown-item" href="'.$file['link'].'" title="view" target="_blank"><i class="icofont-eye"></i> view</a>').'</li>-->
                  </ul>
                </div>
            </td>     
            </tr>';
    }
 echo '</tbody>
 </table>
 </div><script>$(\'a.fancy\').fancybox({
		\'transitionIn\'	:	\'elastic\',
		\'transitionOut\'	:	\'elastic\',
		\'speedIn\'		:	600, 
		\'speedOut\'		:	200, 
		\'overlayShow\'	:	false
	});
    $(".icofont-5x").addClass("icofont-2x").removeClass("icofont-5x");
    </script>';   
 } else {
    echo '<div class="fm-files fm-table-view"><div class="alert alert-info">folder empty</div></div>';
 }