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

$max_items  = (intval(Req::val('num', 10)) == 10) ? 10 : 20;
$sql_project = '';
if ($proj->id) {
    $sql_project = ' AND p.project_id = ' . $proj->id;
}

$feed_type  = Req::val('feed_type', 'rss2');
if ($feed_type != 'rss1' && $feed_type != 'rss2') {
    $feed_type = 'atom';
}

switch (Req::val('topic')) {
    case 'clo': $orderby = 'date_closed'; $closed = 0;
                $title   = 'Recently closed tasks';
    break;

    case 'edit':$orderby = 'last_edited_time'; $closed = 1;
                $title   = 'Recently edited tasks';
    break;

    default:    $orderby = 'date_opened'; $closed = 1;
                $title   = 'Recently opened tasks';
    break;
}

$filename = $feed_type.'-'.$orderby.'-'.$proj->id.'-'.$max_items;

// FIXME : avoid that DB call ...  by making the whole project invalidates cache himself
// {{{
// Get the time when a task has been changed last

$sql = $db->Query("SELECT  MAX(t.date_opened), MAX(t.date_closed), MAX(t.last_edited_time)
                     FROM  {tasks}    t
               INNER JOIN  {projects} p ON t.project_id = p.project_id AND p.project_is_active = '1'
                    WHERE  t.is_closed <> ? $sql_project AND t.mark_private <> '1'
                           AND p.others_view = '1' ", array($closed));
$most_recent = intval(max($db->fetchRow($sql)));

// }}}

/* test cache */
if ($fs->prefs['cache_feeds']) {
    if ($fs->prefs['cache_feeds'] == '1') {
        if (is_file('cache/'.$filename) && $most_recent <= filemtime('cache/'.$filename)) {
            readfile('cache/'.$filename);
            exit;
        }
    }
    else {
        $sql = $db->Query("SELECT  content
                             FROM  {cache}
                            WHERE  type = ? AND topic = ? $sql_project
                                   AND max_items = ?  AND last_updated >= ?",
                        array($feed_type, $orderby, $max_items, $most_recent));
        if ($content = $db->FetchOne($sql)) {
            echo $content;
            exit;
        }
    }
}

/* build a new feed if cache didn't work */
$sql = $db->Query("SELECT  t.task_id, t.item_summary, t.detailed_desc, t.date_opened, t.date_closed,
                           t.last_edited_time, t.opened_by, u.real_name, u.email_address
                     FROM  {tasks}    t
               INNER JOIN  {users}    u ON t.opened_by = u.user_id
               INNER JOIN  {projects} p ON t.project_id = p.project_id AND p.project_is_active = '1'
                    WHERE  t.is_closed <> ? $sql_project AND t.mark_private <> '1'
                           AND p.others_view = '1'
                 ORDER BY  $orderby DESC", array($closed), $max_items);

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
        if (!is_writeable('cache') && !@chmod('cache', 0777))
        {
            die('Error when caching the feed: cache/ is not writeable.');
        }

        // Remove old cached files
        $handle = fopen('cache/'.$filename, 'w+b');
        fwrite($handle, $content);
        fclose($handle);
    }
    else {
       /**
        * See http://phplens.com/adodb/reference.functions.replace.html
        *
        * " Try to update a record, and if the record is not found,
        *   an insert statement is generated and executed "
        */

        $fields = array('content'=> $content , 'type'=> $feed_type , 'topic'=> $orderby ,
                        'project_id'=> $proj->id ,'max_items'=> $max_items , 'last_updated'=> time() );

        $keys = array('type','topic','project_id','max_items');

        $db->Replace('{cache}', $fields, $keys);
    }
}

header('Content-Type: application/xml; charset=utf-8');
echo $content;
?>
