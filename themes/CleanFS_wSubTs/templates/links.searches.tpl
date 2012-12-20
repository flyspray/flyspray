    <strong id="nosearches" <?php if(count($user->searches)): ?>class="hide"<?php endif; ?>>{L('nosearches')}</strong>
    <?php if(count($user->searches)): ?>
    <table id="mysearchestable">
    <?php foreach ($user->searches as $search): ?>
    <tr id="rs{$search['id']}" <?php if($search == end($user->searches)): ?>class="last"<?php endif; ?>>
      <td><a href="{$baseurl}?do=index&amp;{!http_build_query(unserialize($search['search_string']), '', '&amp;')}">{$search['name']}</a></td>
      <td class="searches_delete">
        <a href="javascript:deletesearch('{$search['id']}','{#$baseurl}')">
        <img src="{$this->get_image('button_cancel')}" width="12" height="12" title="{L('delete')}" alt="{L('delete')}" /></a>
      </td>
    </tr>
    <?php endforeach; ?>
    </table>
    <?php endif; ?>
