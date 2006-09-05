				<div id="multiuserlist" class="hide popup">
                   {!tpl_userselect('assigned_select', null, 'assigned_select')}
                   <button type="button" onmouseup="adduserselect('{$baseurl}javascript/callbacks/useradd.php', $('assigned_select').value, 'assigned_to', '{L('usernotexist')}')">
                     {L('add')} &#8595;
                   </button>
                   <button type="button" onmouseup="dualSelect('r', '', 'assigned_to')">
                      &#8593; {L('del')}
                   </button>
                   <br />

                   <select size="10" name="rassigned_to" id="rassigned_to">
                     {!tpl_options($userlist)}
                   </select>
                   <input type="hidden" value="{Req::val('assigned_to', $old_assigned)}" id="vassigned_to" name="assigned_to" />
                   <button type="button" onclick="hidestuff('multiuserlist')">{L('OK')}</button>
				</div>