<p><?php echo Filters::noXSS(L('listnote')); ?></p>
<?php
$countlines = -1;
$categories = $proj->listCategories($proj->id, false, false, false);
$root = $categories[0];
unset($categories[0]);

if (count($categories)) : ?>
<div id="controlBox">
  <div class="grip"></div>
  <div class="inner">
      <a href="#" onclick="TableControl.up('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/up.png" alt="Up" /></a>
      <a href="#" onclick="TableControl.down('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/down.png" alt="Down" /></a>
      <a href="#" onclick="TableControl.shallower('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/left.png" alt="Left" /></a>
      <a href="#" onclick="TableControl.deeper('catTable'); return false;"><img src="<?php echo Filters::noXSS($this->themeUrl()); ?>/right.png" alt="Right" /></a>
  </div>
</div>
<?php endif; ?>
  <form action="<?php echo Filters::noXSS(CreateURL($do, 'cat', $proj->id)); ?>" method="post">
    <table class="list" id="catTable">
       <thead>
       <tr>
         <th><?php echo Filters::noXSS(L('name')); ?></th>
         <th><?php echo Filters::noXSS(L('owner')); ?></th>
         <th><?php echo Filters::noXSS(L('show')); ?></th>
         <th><?php echo Filters::noXSS(L('delete')); ?></th>
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
          <input id="categoryname<?php echo Filters::noXSS($countlines); ?>" class="text" type="text" size="15" maxlength="40" name="list_name[<?php echo Filters::noXSS($row['category_id']); ?>]" 
            value="<?php echo Filters::noXSS($row['category_name']); ?>" />
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
      </tr>
      <?php endforeach; ?>
      </tbody>
      <?php if($countlines > -1): ?>
      <tr>
        <td colspan="3"></td>
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

  <!-- Form to add a new category to the list -->
  <form action="<?php echo Filters::noXSS(CreateURL($do, 'cat', $proj->id)); ?>" method="post">
    <table class="list">
      <tr>
        <td>
          <input id="listnamenew" class="text" type="text" size="15" maxlength="40" name="list_name" />
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
