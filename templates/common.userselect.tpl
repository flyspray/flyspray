<input class="users text" size="30" type="text" name="{$name}" <?php if ($id): ?>id="{$id}"<?php endif; ?> value="{$value}" />
<span class="autocomplete hide" id="{$name}_complete"></span>
<script type="text/javascript">
    showstuff('{$name}_complete');
    new Ajax.Autocompleter('{$id}', '{$name}_complete', '{$baseurl}javascript/callbacks/usersearch.php', {})
</script>
