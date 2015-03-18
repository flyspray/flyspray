<?php
// We can't include this script as part of index.php?do= etc,
// as that would introduce html code into it.  HTML != Valid XML
// So, include the headerfile to set up database access etc

define('IN_FS', true);
define('IN_FEED', true);

require_once(dirname(__FILE__).'/header.php');
$page = new FSTpl();

// Set up the basic XML head
header ('Content-type: text/html; charset=utf-8');

$max_items  = (Req::num('num', 10) == 10) ? 10 : 20;
$sql_project = ' 1=1 ';
if ($proj->id) {
    $sql_project = sprintf(' p.project_id = %d', $proj->id);
}

$feed_type  = Req::val('feed_type', 'rss2');
if ($feed_type != 'rss1' && $feed_type != 'rss2') {
    $feed_type = 'atom';
}

switch (Req::val('topic')) {
    case 'clo': $orderby = 'date_closed'; $closed = 't.is_closed = 1 ';
                $topic = 1;
                $title   = 'Recently closed tasks';
    break;

    case 'edit':$orderby = 'last_edited_time'; $closed = '1=1';
                $topic = 2;
                $title   = 'Recently edited tasks';
    break;

    default:    $orderby = 'date_opened'; $closed = '1=1';
                $topic = 3;
                $title   = 'Recently opened tasks';
    break;
}

$filename = md5(sprintf('%s-%s-%d-%d', $feed_type, $orderby, $proj->id, $max_items) . $conf['general']['cookiesalt']);
$cachefile = sprintf('%s/%s', FS_CACHE_DIR, $filename);

// Get the time when a task has been changed last
$sql = $db->Query("SELECT  t.date_opened, t.date_closed, t.last_edited_time, t.item_summary
                     FROM  {tasks}    t
               INNER JOIN  {projects} p ON t.project_id = p.project_id AND p.project_is_active = '1'
                    WHERE  $closed AND $sql_project AND t.mark_private <> '1'
                           AND p.others_view = '1'
                 ORDER BY  $orderby DESC", 0, $max_items);
$most_recent = 0;
while ($row = $db->fetchRow($sql)) {
    $most_recent = max($most_recent, $row['date_opened'], $row['date_closed'], $row['last_edited_time']);
}

if ($fs->prefs['cache_feeds']) {
    if ($fs->prefs['cache_feeds'] == '1') {
        if (!is_link($cachefile) && is_file($cachefile) && $most_recent <= filemtime($cachefile)) {
            readfile($cachefile);
            exit;
        }
    }
    else {
        $sql = $db->Query("SELECT  content
                             FROM  {cache} p
                            WHERE  type = ? AND topic = ? AND $sql_project
                                   AND max_items = ?  AND last_updated >= ?",
                        array($feed_type, $topic, $max_items, $most_recent));
        if ($content = $db->FetchOne($sql)) {
            echo $content;
            exit;
        }
    }
}

/* build a new feed if cache didn't work */
$sql = $db->Query("SELECT  t.task_id, t.item_summary, t.detailed_desc, t.date_opened, t.date_closed,
                           t.last_edited_time, t.opened_by, COALESCE(u.real_name, t.anon_email) AS real_name, COALESCE(u.email_address, t.anon_email) AS email_address
                     FROM  {tasks}    t
                LEFT JOIN  {users}    u ON t.opened_by = u.user_id
               INNER JOIN  {projects} p ON t.project_id = p.project_id AND p.project_is_active = '1'
                    WHERE  $closed AND $sql_project AND t.mark_private <> '1'
                           AND p.others_view = '1'
                 ORDER BY  $orderby DESC", 0, $max_items);

$task_details     = $db->fetchAllArray($sql);
$feed_description = $proj->prefs['feed_description'] ? $proj->prefs['feed_description'] : $fs->prefs['page_title'] . $proj->prefs['project_title'].': '.$title;
$feed_image       = false;
if ($proj->prefs['feed_img_url']
        && !strncmp($proj->prefs['feed_img_url'], 'http://', 7))
{
    $feed_image   = $proj->prefs['feed_img_url'];
}

$page->uses('most_recent', 'feed_description', 'feed_image', 'task_details');
$content = $page->fetch('feed.'.$feed_type.'.tpl');

// cache feed
if ($fs->prefs['cache_feeds'])
{
    if ($fs->prefs['cache_feeds'] == '1') {
        // Remove old cached files
        if(!is_link($cachefile) && ($handle = @fopen($cachefile, 'w+b'))) {
            if (flock($handle, LOCK_EX)) {
                fwrite($handle, $content);
                flock($handle, LOCK_UN);
            }
            fclose($handle);
            chmod($cachefile, 0600);
        }
    }
    else {
       /**
        * See http://phplens.com/adodb/reference.functions.replace.html
        *
        * " Try to update a record, and if the record is not found,
        *   an insert statement is generated and executed "
        */

        $fields = array('content'=> $content , 'type'=> $feed_type , 'topic'=> $topic ,
                        'project_id'=> $proj->id ,'max_items'=> $max_items , 'last_updated'=> time() );

        $keys = array('type','topic','project_id','max_items');

        $db->Replace('{cache}', $fields, $keys) or die ('error updating the database cache');
    }
}

header('Content-Type: application/xml; charset=utf-8');
echo $content;
?>
