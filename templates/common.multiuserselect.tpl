                <div>
                   {!tpl_userselect('assigned_select', null, 'assigned_select', array('width' => 22, 'onkeydown' => 'return entercheck(event, false)', 'onkeyup' => 'return entercheck(event, true)'))}
                   <button type="button" onmouseup="adduserselect('{$baseurl}javascript/callbacks/useradd.php', $('assigned_select').value, 'assigned_to', '{!addslashes(L('usernotexist'))}')">
                      +
                   </button>
                   <button type="button" onmouseup="dualSelect('r', '', 'assigned_to')">
                      &mdash;
                   </button>
                   <br />

                   <select size="8" style="width:200px;" name="rassigned_to" onkeypress="deleteuser(event)" id="rassigned_to">
                     {!tpl_options($userlist)}
                   </select>
                   <input type="hidden" value="{Req::val('assigned_to', $old_assigned)}" id="vassigned_to" name="assigned_to" />
				</div>
                <script type="text/javascript">
                function entercheck(e, add)
                {
                    var keynum;
                    keynum = (e.keyCode) ? e.keyCode : e.which;
                    if (keynum == 13) {
                        if (add && $('assigned_select').value) {
                            adduserselect('{$baseurl}javascript/callbacks/useradd.php', $('assigned_select').value, 'assigned_to', '{!addslashes(L('usernotexist'))}');
                        }
                        return false;
                    }
                    return true;
                }

                function deleteuser(e)
                {
                    var keynum;
                    keynum = (e.keyCode) ? e.keyCode : e.which;
                    if (keynum == 46) {
                        dualSelect('r', '', 'assigned_to');
                        return false;
                    }
                    return true;
                }
                remove_0val('rassigned_to');
                fill_userselect('{$baseurl}javascript/callbacks/useradd.php', 'assigned_to');
                </script>