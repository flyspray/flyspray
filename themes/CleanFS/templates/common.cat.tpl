<p><?php echo Filters::noXSS(L('listnote')); ?></p>
<?php if ($do=='pm'): ?>
<h3><?php echo Filters::noXSS(L('categoriesglobal')); ?></h3>
<table class="list" id="idtablesys">
<colgroup>
  <col class="cname" />
  <col class="cowner" />
  <col class="cshow" />
  <col class="cdelete" />
  <col class="cusage" />
</colgroup>
<thead>
<tr>
  <th><?php echo Filters::noXSS(L('name')); ?></th>
  <th><?php echo Filters::noXSS(L('owner')); ?></th>
  <th><?php echo Filters::noXSS(L('show')); ?></th>
  <th>&nbsp;</th> 
  <th><?php echo Filters::noXSS(L('usedintasks')); ?></th>
</tr>   
</thead>
<tbody>
<?php if (isset($sysrows) && count($sysrows)): ?>
<?php
$syscountlines=-1;    
foreach ($sysrows as $row):
$syscountlines++;
?>  
<tr>
  <td class="first"><span class="depthmark"><?php echo str_repeat('&rarr;', $row['depth']); ?></span><?php echo Filters::noXSS($row['category_name']); ?></td>
  <td><?php echo ($row['category_owner']==0)? '': Filters::noXSS($row['category_owner']); ?></td>
  <td title="<?php echo Filters::noXSS(L('showtip')); ?>"><?php echo $row['show_in_list']; ?></td>
  <td>&nbsp;</td>
  <td><?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="5"><?php echo Filters::noXSS(L('novalues')); ?></td></tr>
<?php endif; ?>
</tbody>
</table>
<?php endif; ?> 
<h3><?php echo $do=='pm' ? Filters::noXSS(L('categoriesproject')) : Filters::noXSS(L('categoriesglobal')); ?></h3>
<?php
$countlines = -1;
$categories = $proj->listCategories($proj->id, false, false, false);
if ( count($categories) ){
  $root = $categories[0];
  unset($categories[0]);

  if ((count($categories)*6 + 4) > ini_get('max_input_vars')) {
?>
<div class="error">A category tree update of this size requires sending more than <strong><?= ini_get('max_input_vars') ?></strong> key-value pairs (PHP ini setting <i>max_input_vars</i>).
But the current size for an update requires up to <?= (count($categories)*6 + 4) ?> key-value pairs.
Increase <strong>max_input_vars</strong> PHP ini setting before doing any update of this category tree! Otherwise you maybe get a messed up category tree in the database!</div>
<?php   
  }
} else{
  $root=array();
}

if (count($categories)) : ?>
<div id="controlBox">
  <div class="grip"></div>
  <div class="inner">
    <a style="display:block;text-align:center;" href="#" onclick="TableControl.up('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/up.png" alt="Up" /></a>   
    <a href="#" onclick="TableControl.shallower('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/left.png" alt="Left" /></a>
    <a href="#" onclick="TableControl.deeper('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/right.png" alt="Right" /></a>
    <a style="display:block;text-align:center;" href="#" onclick="TableControl.down('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/down.png" alt="Down" /></a>
</div>
</div>
<?php endif; ?>
<?php echo tpl_form(Filters::noXSS(CreateURL($do, 'cat', $proj->id))); ?>
    <table class="list" id="catTable">
       <thead>
       <tr>
         <th><?php echo Filters::noXSS(L('name')); ?></th>
         <th><?php echo Filters::noXSS(L('owner')); ?></th>
         <th><?php echo Filters::noXSS(L('show')); ?></th>
         <th><?php echo Filters::noXSS(L('delete')); ?></th>
         <th><?php echo Filters::noXSS(L('usedintasks')); ?></th>
       </tr>
     </thead>
     <tbody>
      <?php
      foreach ($categories as $row):
          $countlines++;
      ?>
      <tr class="depth<?php echo Filters::noXSS($row['depth']); ?>">
        <td class="first">
          <input type="hidden" name="lft[<?php echo Filters::noXSS($row['category_id']); ?>]" value="<?php echo Filters::noXSS($row['lft']); ?>" />
          <input type="hidden" name="rgt[<?php echo Filters::noXSS($row['category_id']); ?>]" value="<?php echo Filters::noXSS($row['rgt']); ?>" />
          <span class="depthmark"><?php echo str_repeat('&rarr;', intval($row['depth'])); ?></span>
          <input id="categoryname<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" maxlength="40" name="list_name[<?php echo Filters::noXSS($row['category_id']); ?>]" value="<?php echo Filters::noXSS($row['category_name']); ?>" />
        </td>
        <td title="<?php echo Filters::noXSS(L('categoryownertip')); ?>">
          <?php echo tpl_userselect('category_owner[' . $row['category_id'] . ']' . $countlines, $row['category_owner'], 'categoryowner' . $countlines); ?>

        </td>
        <td title="<?php echo Filters::noXSS(L('listshowtip')); ?>">
          <?php echo tpl_checkbox('show_in_list[' . $row['category_id'] . ']', $row['show_in_list'], 'showinlist'.$countlines); ?>

        </td>
        <td title="<?php echo Filters::noXSS(L('listdeletetip')); ?>">
          <input id="delete<?php echo Filters::noXSS($row['category_id']); ?>" type="checkbox"
          <?php if ($row['used_in_tasks']): ?>disabled="disabled"<?php endif; ?>
          name="delete[<?php echo Filters::noXSS($row['category_id']); ?>]" value="1" />
        </td>
        <td><?php echo $row['used_in_tasks'] >0 ? $row['used_in_tasks']:''; ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
      <?php if($countlines > -1): ?>
      <tr>
        <td colspan="4"></td>
        <td class="buttons">
          <input type="hidden" name="action" value="update_category" />
          <input type="hidden" name="list_type" value="category" />
          <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
          <button type="submit"><?php echo Filters::noXSS(L('update')); ?></button>
        </td>
      </tr>
      <?php endif; ?>
    </table>
    <?php if (count($categories)): ?>
    <script type="text/javascript">
      <?php
          echo 'TableControl.create("catTable",{
              controlBox: "controlBox",
              tree: true,
              spreadActiveClass: true
          });';
          echo 'new Draggable("controlBox",{
              handle: "grip"
          });';
      ?>
    </script>
    <?php endif; ?>
</form>
<hr />
<?php echo tpl_form(Filters::noXSS(CreateURL($do, 'cat', $proj->id))); ?>
    <table class="list">
      <tr>
        <td>
          <input id="listnamenew" class="text" type="text" maxlength="40" name="list_name" autofocus />
        </td>
        <td title="<?php echo Filters::noXSS(L('categoryownertip')); ?>">
          <?php echo tpl_userselect('category_owner', Req::val('category_owner'), 'categoryownernew'); ?>

        </td>
        <td title="<?php echo Filters::noXSS(L('categoryparenttip')); ?>">
          <label for="parent_id"><?php echo Filters::noXSS(L('parent')); ?></label>
          <select id="parent_id" name="parent_id">
            <option value="<?php echo Filters::noXSS($root['category_id']); ?>"><?php echo Filters::noXSS(L('notsubcategory')); ?></option>
            <?php echo tpl_options($proj->listCategories($proj->id, false), Req::val('parent_id')); ?>
          </select>
        </td>
        <td class="buttons">
          <input type="hidden" name="action" value="<?php echo Filters::noXSS($do); ?>.add_category" />
          <input type="hidden" name="area" value="<?php echo Filters::noXSS(Req::val('area')); ?>" />
          <input type="hidden" name="project_id" value="<?php echo Filters::noXSS($proj->id); ?>" />
          <button type="submit"><?php echo Filters::noXSS(L('addnew')); ?></button>
        </td>
      </tr>
    </table>
</form>
