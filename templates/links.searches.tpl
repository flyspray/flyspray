    <strong id="nosearches" <?php if(count($user->searches)): ?>class="hide"<?php endif; ?>>{L('nosearches')}</strong>
    <table id="mysearchestable">
    <?php foreach ($user->searches as $search): ?>
    <tr id="rs{$search['id']}" <?php if($search == end($user->searches)): ?>class="last"<?php endif; ?>>
      <td><a href="{$baseurl}?do=index&amp;{!http_build_query(unserialize($search['search_string']), '', '&')}">{$search['name']}</a></td>
      <td class="searches_delete">
        <a href="javascript:deletesearch('{$search['id']}','{#$baseurl}')">
        <img src="{$this->get_image('button_cancel')}" width="16" height="16" title="{L('delete')}" alt="{L('delete')}" /></a>
      </td>
    </tr>
    <?php endforeach; ?>
    </table>
