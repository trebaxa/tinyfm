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
define('FM_INSIDE', true);

#error_reporting(E_ALL);

require ('plugins/zebra/Zebra_Image.php');
require ('fm.config.php');
require ('fm.sess.class.php');
require ('fm.master.class.php');
require ('fm.info.class.php');
require ('fm.class.php');

fm::run();
fm::load_root();

?>
<!DOCTYPE HTML>
<html lang="de-DE">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>tinyFM - the filemanager</title>
        <link rel="stylesheet" href="<?=fm::$server?>icofont/icofont.min.css"/>        
        <link rel="stylesheet" href="<?=fm::$server?>css/main.css">
        <script>
          var _fm = window._fm = window._fm || [];
          _fm.push(['server', "<?=fm::$server?>"]);          
          _fm.push(['url', "<?=fm::$url?>"]);
          _fm.push(['akey', "<?=fm_config::get_access_key()?>"]);
          _fm.push(['field_id', "<?=fm::get_target()?>"]);
        </script>
        <script src="<?=fm::$server?>js/app.min.js"></script>
        <script src="<?=fm::$server?>js/fm.js?a=<?=time()?>"></script>
        <?=fm::$plugins?>
    </head>
    <body>    
   	<input type="hidden" name="url" id="js-url" value=""/>
    <div class="container-fluid">
       <div class="row">
        <?PHP fm::include('nav'); ?>
        <?PHP include ('tpl/fm.dropzone.php');?>
        <div class="col-12" id="js-fm-main">           
            <?PHP include ('tpl/fm.files.php');?>
        </div>
       </div>   
         <?PHP include ('tpl/fm.footer.php');?>        
      </div>
    </body>
</html>