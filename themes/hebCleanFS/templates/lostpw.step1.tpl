<h3><?php echo Filters::noXSS(L('lostpw')); ?></h3>
<div class="box">
    <p><?php echo Filters::noXSS(L('lostpwexplain')); ?></p>

    <form action="<?php echo Filters::noXSS(CreateUrl('lostpw')); ?>" method="post">
        <p><b><?php echo Filters::noXSS(L('username')); ?></b>

        <input type="hidden" name="action" value="lostpw.sendmagic" />
        <input class="text" type="text" value="<?php echo Filters::noXSS(Req::val('user_name')); ?>" name="user_name" size="20" maxlength="20" />
        <button type="submit"><?php echo Filters::noXSS(L('sendlink')); ?></button>
        </p>
    </form>
</div>
