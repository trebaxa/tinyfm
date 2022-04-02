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
<section class="mt-4 mb-3">
    <div class="row">
       <div class="col-12">
        <form method="POST" class="json-form" action="<?=$_SERVER['PHP_SELF']?>">
         <input type="hidden" name="cmd" value="create_dir" />
            <div class="form-group">
                <label>Directory name</label>
                <input autofocus="" required="" type="text" name="FORM[dir]" value="" id="js-dir-name" placeholder="enter directory name" class="form-control" />
            </div>
            <div class="btn-group mt-1">
                <button type="submit" class="btn btn-primary btn-sm">create</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fm.close()">close</button>
            </div>
        </form>
    </div>
</div> 
</section>
<script>
    $('#js-dir-name').focus();
</script>