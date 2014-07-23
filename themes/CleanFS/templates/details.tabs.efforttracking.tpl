<div id="effort" class="tab">
    <form enctype="multipart/form-data" action="<?php echo Filters::noXSS(CreateUrl('details', $task_details['task_id'])); ?>" method="post">
        <input type="hidden" name="action" value="details.efforttracking"/>
        <button type="submit" name="start_tracking" value="true"><?php echo Filters::noXSS(L('starteffort')); ?></button>
        </br>
        <label for="effort_to_add"><?php echo Filters::noXSS(L('manualeffort')); ?></label>
        <input id="effort_to_add" name="effort_to_add" class="text" type="text" size="5" maxlength="100" value='00:00'>
        <button type="submit" name="manual_effort" value="true"><?php echo Filters::noXSS(L('addeffort')); ?></button>

        <table class="userlist history">
            <tr>
                <th><?php echo Filters::noXSS(L('date')); ?></th>
                <th><?php echo Filters::noXSS(L('user')); ?></th>
                <th><?php echo Filters::noXSS(L('effort')); ?> (H:M)</th>
                <th></th>
            </tr>
            <?php
            foreach($effort->details as $details){
            ?>
            <tr>
                <td><?php echo Filters::noXSS(formatDate($details['date_added'], true)); ?></td>
                <td><?php echo tpl_userlink($details['user_id']); ?></td>
                <td><?php
            if($details['effort'] == 0)
             { ?>
                    <?php echo Filters::noXSS(L('trackinginprogress')); ?> (<?php

                    echo ConvertSeconds(time()-$details['start_timestamp']);

                    ?>)
                    <?php }
             else
             {
                echo ConvertSeconds($details['effort']);
             } ?>
                </td>
                <td>
                    <?php if($user->id == $details['user_id'] & is_null($details['end_timestamp'])){ ?>
                    <button type="submit" name="stop_tracking" value="true"><?php echo Filters::noXSS(L('endeffort')); ?></button>
                    <button type="submit" name="cancel_tracking" value="true"><?php echo Filters::noXSS(L('cleareffort')); ?></button>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </form>
</div>