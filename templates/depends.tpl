<div class="box"><p><b>{L('pruninglevel')}: </b>{!implode(" &nbsp;|&nbsp; \n", $strlist)}</p>
<h2><a href="{CreateUrl('details', $task_id)}">FS#{!$task_id}</a>: {L('dependencygraph')}</h2>

<?php if ($fmt == 'svg'): ?>
<object class="depimage" data="{$image}"
    width="{$width}" height="{$height}"
    type="image/svg+xml">
</object>
<?php else: ?>
    <div>{!$map}</div>
       
    <img src="{$image}" alt="Dependencies for task {$task_id}" class="depimage" usemap="#{$graphname}" />

<?php endif; ?>

<p>{sprintf(L('pagegenerated'), $time)}<p>
</div>