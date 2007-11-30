<?php if ($label): ?>
<label for="{$name}">{$label}</label>
<?php endif; ?>
<input id="{$name}" type="text" class="text" size="10" name="{$name}" value="{$date}" />

<a class="datelink" href="#" id="{$name}dateview"><img src="{$this->get_image('x-office-calendar')}" alt="{L('selectdate')}" /></a>
<script type="text/javascript">Calendar.setup({daFormat: '{#$dateformat}',inputField: "{$name}", button: "{$name}dateview"});</script>