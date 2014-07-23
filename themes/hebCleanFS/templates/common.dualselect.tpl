    <div class="double_select">
      
      <select class="dualselect_selectable" id="l<?php echo Filters::noXSS($id); ?>" multiple="multiple"
        ondblclick="dualSelect(this, 'r', '<?php echo Filters::noJsXSS($id); ?>')">%s</select>
      
      <div class="dualselect_buttons">
        <button type="button" onmouseup="dualSelect('l', 'r', '<?php echo Filters::noJsXSS($id); ?>')">
          הוסף &#8592;
        </button>
        <button type="button" onmouseup="dualSelect('r', 'l', '<?php echo Filters::noJsXSS($id); ?>')">
           &#8594; מחק
        </button>
      </div>
      
      <div class="dualselect_selected">
        <?php if($updown): ?><button type="button" onmouseup="selectMove('<?php echo Filters::noJsXSS($id); ?>', -1)">&#8593;</button><br /><?php endif; ?>

        <select id="r<?php echo Filters::noXSS($id); ?>" multiple="multiple"
          ondblclick="dualSelect(this, 'l', '<?php echo Filters::noJsXSS($id); ?>')">%s</select>
        <?php if($updown): ?><button type="button" onmouseup="selectMove('<?php echo Filters::noJsXSS($id); ?>', 1)">&#8595;</button><?php endif; ?>
        
        <input type="hidden" value="<?php echo Filters::noXSS(join(' ', $selected)); ?>" id="v<?php echo Filters::noXSS($id); ?>" name="<?php echo Filters::noXSS($name); ?>" />
      </div>
      <div class="clear"></div>
    </div>
