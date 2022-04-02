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

?>
<nav class="navbar navbar-expand navbar-light bg-light sticky-top fm-nav">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-tinfyfm-bar" aria-controls="nav-tinfyfm-bar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button> 
      <div class="collapse navbar-collapse" id="nav-tinfyfm-bar">
        <div class="navbar-nav me-auto mb-2 mb-lg-0">
            <a href="./fm.php?cmd=show_newdir" data-bs-toggle="tooltip" title="new folder" data-target="#js-tools" class="nav-link js-link"><i class="icofont-plus icofont-sm"></i><i class="icofont-ui-folder icofont-2x"></i></a>
            <a data-bs-toggle="tooltip" href="javascript:;" onClick="fm.toggle_upload()" title="upload" class="nav-link"><i class="icofont-upload icofont-2x"></i></a>
            <a data-bs-toggle="tooltip" href="javascript:;" onClick="fm.mass_del()" id="js-mass-del-icon" title="upload" class="nav-link fm-hide"><i class="icofont-ui-delete icofont-2x"></i></a>
        </div>  
        <div class="navbar-nav">
            <a href="javascript:;" data-bs-toggle="tooltip" title="view cards" data-view="cards" class="nav-link js-fm-view"><i class="icofont-table icofont-2x"></i></a>
            <a href="javascript:;" data-bs-toggle="tooltip" title="view table" data-view="table" class="nav-link js-fm-view border-right"><i class="icofont-ui-note icofont-2x"></i></a>
        </div>
        <div class="navbar-nav">
            <a href="javascript:;" data-bs-toggle="tooltip" title="Filter images" data-type="image" class="nav-link js-fm-filter"><i class="icofont-ui-image icofont-2x"></i></a>
            <a href="javascript:;" data-bs-toggle="tooltip" title="Filter video" data-type="video" class="nav-link js-fm-filter"><i class="icofont-video-alt icofont-2x"></i></a>
            <a href="javascript:;" data-bs-toggle="tooltip" title="Filter music" data-type="music" class="nav-link js-fm-filter"><i class="icofont-music icofont-2x"></i></a>
        </div>
        <form class="d-flex ax-form" action="<?=$_SERVER['PHP_SELF']?>" data-target="#js-fm-main">
            <input type="hidden" name="cmd" value="search" />
            <input name="FORM[types][image]" type="hidden" value="0"/>
            <input name="FORM[types][video]" type="hidden" value="0"/>
            <input name="FORM[types][music]" type="hidden" value="0"/>
            <input name="FORM[view]" type="hidden" value="<?PHP echo fm::$fm['filter']['view'] ?>"/>
            <input name="nr" type="hidden" value="0"/>
            <input class="form-control me-2" name="FORM[q]" type="search" placeholder="Search" aria-label="Search">
            <button class="btn btn-outline-primary" type="submit"><i class="icofont-search"></i></button>
          </form>
      </div>   
</nav>