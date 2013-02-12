<h3>{L('lostpw')}</h3>
<div class="box">
    <p>{L('lostpwexplain')}</p>

    <form action="{CreateUrl('lostpw')}" method="post">
        <p><b>{L('username')}</b>

        <input type="hidden" name="action" value="lostpw.sendmagic" />
        <input class="text" type="text" value="{Req::val('user_name')}" name="user_name" size="20" maxlength="20" />
        <button type="submit">{L('sendlink')}</button>
        </p>
    </form>
</div>
