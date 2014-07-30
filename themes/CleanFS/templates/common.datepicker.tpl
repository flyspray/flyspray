<?php if ($label): ?>
<label for="<?php echo Filters::noXSS($name); ?>"><?php echo Filters::noXSS($label); ?></label>
<?php endif; ?>
<input id="<?php echo Filters::noXSS($name); ?>" type="text" class="text" size="10" name="<?php echo Filters::noXSS($name); ?>" value="<?php echo Filters::noXSS($date); ?>" />

<a class="datelink" href="#" id="<?php echo Filters::noXSS($name); ?>dateview">
  <!--<img src="<?php echo Filters::noXSS($this->get_image('x-office-calendar')); ?>" alt="<?php echo Filters::noXSS(L('selectdate')); ?>" />-->
</a>
<script type="text/javascript">Calendar.setup({daFormat: '<?php echo Filters::noJsXSS($dateformat); ?>',inputField: "<?php echo Filters::noXSS($name); ?>", button: "<?php echo Filters::noXSS($name); ?>dateview"});</script>