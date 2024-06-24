<div class="datepickerwrap">
<?php if ($label): ?>
<label for="<?php echo Filters::noXSS($name); ?>"><?php echo Filters::noXSS($label); ?></label>
<?php endif; ?>
<input id="<?php echo Filters::noXSS($name); ?>" type="text" class="text" size="10" name="<?php echo Filters::noXSS($name); ?>" placeholder=" " value="<?php echo Filters::noXSS($date); ?>" />
<a class="datebutton" href="#" id="<?php echo Filters::noXSS($name); ?>dateview" title="<?= eL('selectdate') ?>"><span class="far fa-calendar fa-xl"></span><span class="far fa-calendar-days fa-xl"></span></a>
<script type="text/javascript">Calendar.setup({daFormat: '<?php echo Filters::noJsXSS($dateformat); ?>',inputField: "<?php echo Filters::noXSS($name); ?>", button: "<?php echo Filters::noXSS($name); ?>dateview"});</script>
</div>
