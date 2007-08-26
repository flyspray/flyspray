                <div>
                   <label for="find_user" class="inline" style="margin:0;padding:0;">{L('find')}</label>:
                   <input type="text" style="vertical-align:middle" class="text" value="{Post::val('find_user')}" name="find_user" id="find_user" onkeyup="return entercheck(event)" />
                   <a href="javascript:unselectAll()">{L('noone')}</a>
                   <br />

                   <select size="8" style="width:200px;" name="rassigned_to[]" id="rassigned_to" multiple="multiple">
                     <?php foreach ($userlist as $group => $users): ?>
                        <optgroup label="{$group}">
                        <?php foreach ($users as $info): ?>
                        <option value="{$info[0]}" <?php if (in_array($info[0], $assignees)): ?>selected="selected"<?php endif; ?>>{$info[1]}</option>
                        <?php endforeach; ?>
                        </optgroup>
                     <?php endforeach; ?>
                   </select>
				</div>
                <script type="text/javascript">
                resetOption = null;
                function entercheck(e)
                {
                    // Find user and select it
                    if ($('find_user').value.length < 1) {
                        if (resetOption != null) {
                            resetOption.selected = false;
                        }
                    } else {
                        var options = $('rassigned_to').options;
                        for (var i = 0; i < options.length; i++) {
                            if (options[i].text.toLowerCase().indexOf($('find_user').value.toLowerCase()) >= 0) {
                                if (resetOption != null) {
                                    resetOption.selected = false;
                                }
                                if (options[i].selected == false) {
                                    resetOption = options[i];
                                }
                                options[i].selected=false; // focus
                                options[i].selected=true;
                                return true;
                            }
                        }
                    }
                }
                
                function unselectAll()
				{
					var options = $('rassigned_to').options;
                    for (var i = 0; i < options.length; i++) {
						options[i].selected=false;
					}
				}
                </script>