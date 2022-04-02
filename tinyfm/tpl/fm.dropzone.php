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
<div class="col-12 fm-hide mb-3" id="js-dropzone">
    <div class="tinyfm-dropzone dropzone">
      <div class="text-center align-middle">
        <i class="icofont-upload icofont-5x"></i>
      </div>  
    </div>
    <div id="dropzonefeedback"></div>
</div>
<script>Dropzone.autoDiscover = false;

  let fmdropzone = new Dropzone("div.tinyfm-dropzone", { 
        url: "<?=$FM->server?>fm.php?cmd=upload&akey=<?=fm_config::get_access_key()?>",
        paramName: 'file', <?PHP
         foreach (fm_config::get_config()['dropzone'] as $key => $val) {
            echo $key.': "'.$val .'",'.PHP_EOL;
         }
        ?>
     init: function() {
        this.on("error", function(file){
                if (!file.accepted) {
                    this.removeFile(file);
                    alertify.error('Forbidden file!');                                        
                }
        });                            
      }   
  });

fmdropzone.on("success", function(file,responseText) {
    fmdropzone.removeFile(file);
    console.log(responseText);
    var result = jQuery.parseJSON(responseText);
    if (result.status=='failed') {        
        alertify.error('Failed: ' + result.filename);       
    } else {        
        alertify.success(result.filename);        
    }
    fm.upload_finished(result.file, result.ident);
    
});
fmdropzone.on("drop", function() {
     $('#drop-zone-files').html('');
     $('#dropzonefeedback').show();
});
fmdropzone.on("queuecomplete", function() {
     $('#drop-zone-files').html('');
     setTimeout("$('#dropzonefeedback').fadeOut()", 3000 );
     fm.toggle_upload();
     fm.reload_folder('');
     fm.init_drag();
});                    

</script>