    </div>
    <p id="footer">
       <!-- Please don't remove this line - it helps promote Flyspray -->
       <a href="http://flyspray.org/" class="offsite">{L('poweredby')}<?php if ($user->perms('is_admin')): ?> {$fs->version}  {$fs->getSvnRev()}<?php endif; ?></a><br/>
       <i><a href="http://www.thevelozgroup.com">{L('sponsoredby')} The Veloz Group</a></i>
    </p>
  </div>
  </body>
</html>
