    <strong id="nosearches" <?php if(count($user->searches)): ?>class="hide"<?php endif; ?>><?php echo Filters::noXSS(L('nosearches')); ?></strong>
    <?php if(count($user->searches)): ?>
    <table id="mysearchestable">
    <?php foreach ($user->searches as $search): ?>
    <tr id="rs<?php echo Filters::noXSS($search['id']); ?>" <?php if($search == end($user->searches)): ?>class="last"<?php endif; ?>>
      <td><a href="<?php echo Filters::noXSS($baseurl); ?>?do=index&amp;<?php echo http_build_query(unserialize($search['search_string']), '', '&amp;'); ?>"><?php echo Filters::noXSS($search['name']); ?></a></td>
      <td class="searches_delete">
        <a href="javascript:deletesearch('<?php echo Filters::noXSS($search['id']); ?>','<?php echo Filters::noJsXSS($baseurl); ?>')">
        <img src="<?php echo Filters::noXSS($this->get_image('button_cancel')); ?>" width="12" height="12" title="<?php echo Filters::noXSS(L('delete')); ?>" alt="<?php echo Filters::noXSS(L('delete')); ?>" /></a>
      </td>
    </tr>
    <?php endforeach; ?>
    </table>
    <?php endif; ?>
