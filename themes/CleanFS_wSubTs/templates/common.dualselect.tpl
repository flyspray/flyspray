    <div class="double_select">
      
      <select class="dualselect_selectable" id="l{$id}" multiple="multiple"
        ondblclick="dualSelect(this, 'r', '{#$id}')">%s</select>
      
      <div class="dualselect_buttons">
        <button type="button" onmouseup="dualSelect('l', 'r', '{#$id}')">
          add &#8594;
        </button>
        <button type="button" onmouseup="dualSelect('r', 'l', '{#$id}')">
           &#8592; del
        </button>
      </div>
      
      <div class="dualselect_selected">
        <?php if($updown): ?><button type="button" onmouseup="selectMove('{#$id}', -1)">&#8593;</button><br /><?php endif; ?>

        <select id="r{$id}" multiple="multiple"
          ondblclick="dualSelect(this, 'l', '{#$id}')">%s</select>
        <?php if($updown): ?><button type="button" onmouseup="selectMove('{#$id}', 1)">&#8595;</button><?php endif; ?>
        
        <input type="hidden" value="{join(' ', $selected)}" id="v{$id}" name="{$name}" />
      </div>
      <div class="clear"></div>
    </div>