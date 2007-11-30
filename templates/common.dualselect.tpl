<table class="double_select">
  <tr>
    <td class="c1">
      <select id="l{$id}" multiple="multiple"
        ondblclick="dualSelect(this, 'r', '{#$id}')">%s</select>
    </td>
    <td class="c2">
      <button type="button" onmouseup="dualSelect('l', 'r', '{#$id}')">
        add &#8594;
      </button>
      <br /><br />
      <button type="button" onmouseup="dualSelect('r', 'l', '{#$id}')">
         &#8592; del
      </button>
    </td>
    <td class="c3">
      <?php if($updown): ?><button type="button" onmouseup="selectMove('{#$id}', -1)">&#8593;</button><br /><?php endif; ?>
      
      <select id="r{$id}" multiple="multiple"
        ondblclick="dualSelect(this, 'l', '{#$id}')">%s</select>
      <br />
      <?php if($updown): ?><button type="button" onmouseup="selectMove('{#$id}', 1)">&#8595;</button><?php endif; ?>
      <input type="hidden" value="{join(' ', $selected)}" id="v{$id}" name="{$name}" />
    </td>
  </tr>
</table>

