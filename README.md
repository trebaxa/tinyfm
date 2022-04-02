# tinyfm
Tiny filemanager for tinyMCE or also as standalone solution


## Configure tintyMCE
```
...
external_filemanager_path:"!!PATH_TO_TINYFM_INSTALLATION!!",     
filemanager_access_key:"!!ENTER_ACCESSKEY_FROM_TINYMFM_CONFIG!!",
external_plugins: { "tinyfm" :  "!!PATH_TO_TINYFM_INSTALLATION!!/js/plugin.min.js",
...
/*add "tinyfm" to your plugin list*/
plugins: [
       "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
       "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
       "save table contextmenu directionality emoticons template paste textcolor tinyfm "
      ], 
...
```
