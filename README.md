# tinyfm - responsive filemanager for tinymce
Tiny filemanager for tinyMCE or also as standalone solution

## Install
Copy the /tinyfm folder to your server. For example www.mydomain.com/cjs/tinyfm
Configure tinyMCE and tinyFM as descripted below.

### configure config.json
#### cjs/tinyfm/config/config.json
```
{
    "relative_path_to_files": "..\/..\/file_server\/",
    "path_to_files": "file_server\/",
    "path": "cjs\/tinyfm\/",
    "dateformat": "d.m.Y",
    "access_key": "YOUR_OWN_ACCESS_KEY",
    "resize_images_to_max_size": true,
    "file_permission": 493,
    "folder_permission": 493,
    "dropzone": {
        "maxFilesize": "256",
        "acceptedFiles": "",
        "forbidden_ext": ""
    },
    "ssl": "true"
}
```
```
path: path to installation of tinfym. for example: cjs/tinfyfm
relative_path_to_files: realtive path to medialibary from install path of tinyfm (where fm.php is located)
path_to_files: the path to folder where the files are stored => www.mydomain.com/path_to_files/
file_permission: 493 is equal with "0755". 
resize_images_to_max_size: if true max width and height of images is set to 4000px

From point of view from your domain:
www.mydomain.com/file_server/
www.mydomain.com/cjs/tinyfm/

**dont forget to escape the "/" in json like cjs\/tinyfm\/**
```

### Configure tintyMCE
```
...
external_filemanager_path:"!!PATH_TO_TINYFM_INSTALLATION!!",     
filemanager_access_key:"!!ENTER_ACCESSKEY_FROM_TINYMFM_CONFIG!!",
external_plugins: { "tinyfm" :  "!!PATH_TO_TINYFM_INSTALLATION!!/js/plugin.min.js",
...
/*add "tinyfm" to your plugin list*/
plugins: ["tinyfm"], 
...
```
```
!!PATH_TO_TINYFM_INSTALLATION!! => /cjs/tinyfm/
!!ENTER_ACCESSKEY_FROM_TINYMFM_CONFIG!! => Accesskey defined in config.json
```

## Standalone call from a backend of your software
```
<iframe src="../cjs/tinyfm/fm.php?nr=1&standalone=1&lang=de&akey=!!ENTER_ACCESSKEY_FROM_TINYMFM_CONFIG!!" style="width:100%;height:800px;border:0px"></iframe>
```
