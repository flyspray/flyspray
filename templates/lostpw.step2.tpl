<fieldset class="box">
<legend>{L('changepass')}</legend>

    <form action="{CreateUrl('lostpw')}" method="post">
    <table>
      <tr>
        <td><label for="pass1">{L('changepass')}</label></td>
        <td><input class="password" id="pass1" type="password" value="{Req::val('pass1')}" name="pass1" size="20" /></td>
      </tr>
      <tr>
        <td><label for="pass2">{L('confirmpass')}</label></td>
        <td><input class="password" id="pass2" type="password" value="{Req::val('pass2')}" name="pass2" size="20" /></td>
      </tr>
      </table>
      
      <div>
        <input type="hidden" name="action" value="lostpw.chpass" />
        <input type="hidden" name="magic_url" value="{Req::val('magic_url')}" />
        <button type="submit">{L('savenewpass')}</button>
      </div>
    </form>
</fieldset>

