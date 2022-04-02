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

echo '<div class="row ">
  <div class="topbar d-flex">'; 
    fm::include('files.breadcrumb');
    fm::include('files.top');
echo '</div>
</div>';

if (fm::$fm['filter']['view']=='cards' || fm::$fm['filter']['view']=='') { 
 if (count(fm::$fm['dirs'])>0 || count(fm::$fm['files'])>0) {
    echo '<div id="js-tools"></div>
      <div class="fm-files" id="fm-files"> 
        <div class="row" >';
    foreach (fm::$fm['dirs'] as $dir) {
        echo '<div class="col-12 col-md-2 col-lg-2 mb-2 js-fm-card fm-card">
            <div class="card card-hover">
                <div class="overlay text-center" > 
                    <a data-bs-toggle="tooltip" href="javascript:;" title="delete" data-cmd="del_dir" data-info=\'' . htmlspecialchars(json_encode($dir)) . '\' class="js-del-link card-del-icon"><i class="icofont-ui-delete"></i></a>               
                    <div class="dropdown card-dropdown">
                      <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item js-dir-file" href="javascript:;" data-filename="'.htmlspecialchars( $dir['dir']).'" data-info=\'' . htmlspecialchars(json_encode($dir))  . '\' ><i class="icofont-edit"></i> rename</a></li>
                      </ul>
                    </div>
                    <div class="data js-single-select" onClick="fm.load_folder(this)" data-info=\'' . htmlspecialchars(json_encode($dir)) . '\'>
                        <dl>
                            <dt>size:</dt>
                            <dd>'.$dir['total_size'].'</dd>
                            <dt>files:</dt>
                            <dd>'.$dir['file_count'].'</dd>
                        </dl>
                    </div>
                    <div class="overlay-click-panel" onClick="fm.load_folder(this)" data-info=\'' . htmlspecialchars(json_encode($dir)) . '\'></div>                
                </div>
                <div class="image">
                    <a data-bs-toggle="tooltip" title="open folder" class="folder" href="javascript:;" style="display:block" onClick="fm.load_folder(this)" data-info=\'' . htmlspecialchars(json_encode($dir)) . '\'><i class="icofont-ui-folder icofont-5x"></i></a>
                </div>
                <div class="header">
                    ' . $dir['dir'] . '
                </div>             
           </div>
          </div>';
    }
    foreach (fm::$fm['files'] as $file) {
        $info="";
        foreach ($file['info'] as $key => $val) {
            $info.=$key.': '.$val.'<br>';
        }
        $info = htmlspecialchars($info);
        if ($file['type']=='image') {
            $con = '<div class="image" style="background-image: url('.$file['thumb'].');"></div>';
            $con.='<a style="display:none" class="fancy" href="'.$file['link'].'" data-fancybox="gallery">	<img src="'.$file['thumb'].'" alt="" /></a>';
            #$link_view = '<li><a class="dropdown-item" href="'.$file['link'].'" title="view" target="_blank"><i class="icofont-eye"></i> view</a></li>';
            $link_view = '<li><a class="dropdown-item" href="javascript:;" title="view" onclick="$(\'.fancy:first\').trigger(\'click\');"><i class="icofont-eye"></i> view</a></li>';
        } else {
            $con='<div class="icon">' . $file['preview'] . '</div>';
            $link_view = "";
        }
        
        echo '<div class="col-12 col-md-2 col-lg-2 mb-2 js-fm-card fm-card" 
        data-localfile="'.$file['localfileb64'].'" 
        data-ident="'.$file['ident'].'" 
        data-hash="'.$file['hash'].'"
        data-url="'.$file['link'].'" 
        data-type="'.$file['type'].'" 
        data-function="'.$file['def']['function'].'">
                    <div class="card card-hover">
                        <div class="overlay text-center">
                            '.((fm::is_standalone()) ? '<input type="checkbox" class="select js-file-checkbox" name="" value="1">' : '').'
                            <a data-bs-toggle="tooltip" href="javascript:;" title="delete" data-cmd="del_file" data-info=\'' . htmlspecialchars(json_encode(array('file' => $file['ident'],'hash' => $file['hash']))) . '\' class="js-del-link card-del-icon"><i class="icofont-ui-delete"></i></a>
                            <div class="dropdown card-dropdown">
                              <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"></button>
                              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                <li><a class="dropdown-item js-rename-file" href="javascript:;" data-filename="'.htmlspecialchars( $file['filename']).'" data-info=\'' . htmlspecialchars(json_encode(array('file' => $file['ident'], 'hash' => $file['hash']))) . '\' ><i class="icofont-edit"></i> rename</a></li>
                                <li><a class="dropdown-item" href="' . fm::$eurl . 'akey='.fm_config::get_access_key().'&cmd=download&ident='.$file['ident'].'" title="download"><i class="icofont-download"></i> download</a></li>
                                '.$link_view.'
                                <li><a class="dropdown-item" href="javascript:navigator.clipboard.writeText(\''.$file['link'].'\');" title="copy to clipboard"><i class="icofont-copy"></i> copy link</a></li>                                 
                              </ul>
                            </div>
                            <div class="data js-single-select">
                                <dl>
                                    <dt>size:</dt>
                                    <dd>'.$file['info']['size'].'</dd>
                                    <dt>date:</dt>
                                    <dd>'.$file['info']['time'].'</dd>
                                    '.((isset($file['imgsize'][0])) ? '<dt>dim:</dt>
                                    <dd>'.$file['info']['resolution'].'</dd>' : '').'
                                </dl>
                            </div>
                            <div class="overlay-click-panel js-single-select"></div>                       
                        </div>                
                    '.$con.'
                    <div class="header">
                        ' . fm::truncate($file['filename'],30) . '                    
                    </div>
               </div> 
            </div>';
    }

echo '</div>
</div><script>$(\'a.fancy\').fancybox({
		\'transitionIn\'	:	\'elastic\',
		\'transitionOut\'	:	\'elastic\',
		\'speedIn\'		:	600, 
		\'speedOut\'		:	200, 
		\'overlayShow\'	:	false
	});</script>
';
} else {
    echo '<div class="fm-files fm-table-view"><div class="alert alert-info">folder empty</div></div>';
 }
} elseif (fm::$fm['filter']['view']=='table') {
    require('tpl/fm.files.table.php');
}