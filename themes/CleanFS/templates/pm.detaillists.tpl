<!-- add DC 19/02/2015  -->
<?php
///retrieve $lists_id
if(Post::num('lists_id') !=0) $lists_id = Post::num('lists_id');
if(Get::num('lists_id')  !=0) $lists_id = Get::num('lists_id');
///retrieve $list_name
if(Post::num('list_name') !=0) $list_name = Post::num('list_name');
if(Get::num('list_name')  !=0) $list_name = Get::num('list_name'); 
?>
<div id="toolbox">
  <h3><?php echo Filters::noXSS($list_name); ?> : <?php echo Filters::noXSS(L('customsfielded')); ?></h3>
  <?php
  echo "COMMON_DETAILLISTS=>lists_id=>$lists_id=>$list_name<br>";
  echo "lists_id=>$lists_id<br>";
  $this->assign('list_type', 'detaillists');
  $this->assign('rows', $proj->detailLists($lists_id));
  $this->display('common.detaillists.tpl');
  ?>
</div>