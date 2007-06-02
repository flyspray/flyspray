<fieldset>
<legend>{L('registernewuser')}</legend>

<form action="{$baseurl}index.php" name="registernewuser" method="post" id="registernewuser">
  <p>{L('entercode')}</p>
  <table class="box">
    <tr>
      <td><label for="confirmation_code">{L('confirmationcode')}</label></td>
      <td><input id="confirmation_code" class="text" name="confirmation_code" value="{Req::val('confirmation_code')}" type="text" size="20" maxlength="20" /></td>
    </tr>
    <tr>
      <td><label for="user_pass">{L('password')}</label></td>
      <td><input id="user_pass" class="password" name="user_pass" value="{Req::val('user_pass')}" type="password" size="20" maxlength="100" /> <em>{L('minpwsize')}</em></td>
    </tr>
    <tr>
      <td><label for="user_pass2">{L('confirmpass')}</label></td>
      <td><input id="user_pass2" class="password" name="user_pass2" value="{Req::val('user_pass2')}" type="password" size="20" maxlength="100" /></td>
    </tr>
  </table>

    <div>
        <input type="hidden" name="action" value="register.registeruser" />
        <input type="hidden" name="magic_url" value="{Req::val('magic_url')}" />
        <button type="submit" name="buSubmit">{L('registeraccount')}</button>
    </div>
</form>
</fieldset>
