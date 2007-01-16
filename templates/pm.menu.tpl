<div id="toolboxmenu">
  <a id="projprefslink" href="{CreateURL('pm', 'prefs',      $proj->id)}">{L('preferences')}</a>
  <a id="projuglink"    href="{CreateURL('pm', 'groups',     $proj->id)}">{L('usergroups')}</a>
  <a id="projttlink"    href="{CreateURL('pm', 'tasktype',         $proj->id)}">{L('tasktypes')}</a>
  <a id="projstatuslink" href="{CreateURL('pm', 'status',     $proj->id)}">{L('taskstatuses')}</a>
  <a id="projreslink"   href="{CreateURL('pm', 'resolution',        $proj->id)}">{L('resolutions')}</a>
  <a id="projcatlink"   href="{CreateURL('pm', 'cat',        $proj->id)}">{L('categories')}</a>
  <a id="projoslink"    href="{CreateURL('pm', 'os',         $proj->id)}">{L('operatingsystems')}</a>
  <a id="projverlink"   href="{CreateURL('pm', 'version',        $proj->id)}">{L('versions')}</a>
  <a id="projreqlink"   href="{CreateURL('pm', 'pendingreq', $proj->id)}">{L('pendingrequests')}</a>
</div>
