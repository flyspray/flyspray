<input class="users text" <?php echo join_attrs($attrs); ?> type="text" name="<?php echo Filters::noXSS($name); ?>" <?php if ($id): ?>id="<?php echo Filters::noXSS($id); ?>"<?php endif; ?> value="<?php echo Filters::noXSS($value); ?>" />
<span class="autocomplete" id="<?php echo Filters::noXSS($name); ?>_complete"></span>
<script type="text/javascript">
    showstuff('<?php echo Filters::noJsXSS($name); ?>_complete');
    new Ajax.Autocompleter('<?php echo Filters::noJsXSS($id); ?>', '<?php echo Filters::noJsXSS($name); ?>_complete', '<?php echo Filters::noXSS($baseurl); ?>js/callbacks/usersearch.php', {})
</script>
