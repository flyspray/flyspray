<?php echo '<?xml version="1.0" ?>'; ?>

<feed xmlns="http://www.w3.org/2005/Atom">
  <title type="text"><?php echo Filters::noXSS($fs->prefs['page_title']); ?></title>
  <subtitle type="text">
    <?php echo Filters::noXSS($feed_description); ?>

  </subtitle>
  <id><?php echo Filters::noXSS($baseurl); ?></id>
  <?php if($feed_image): ?>
  <icon><?php echo Filters::noXSS($feed_image); ?></icon>
  <?php endif; ?>
  <updated><?php echo Filters::noXSS(date('Y-m-d\TH:i:s\Z',$most_recent)); ?></updated>
  <link rel="self" type="text/xml" href="feed.php?feed_type=atom"/>
  <link rel="alternate" type="text/html" hreflang="en" href="<?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?>"/>
  <?php foreach ($task_details as $row): ?>
  <entry>
    <title>FS#<?php echo Filters::noXSS($row['task_id']); ?>: <?php echo Filters::noXSS($row['item_summary']); ?></title>
    <link href="<?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?>" />    
    <updated><?php echo Filters::noXSS(date('Y-m-d\TH:i:s\Z',intval($row['last_edited_time']))); ?></updated>    
    <published><?php echo Filters::noXSS(date('Y-m-d\TH:i:s\Z',intval($row['date_opened']))); ?></published>
    <content type="xhtml" xml:lang="en" xml:base="http://diveintomark.org/">
      <div xmlns="http://www.w3.org/1999/xhtml"> <?php
    $data = $row['detailed_desc'];
        
    if ($conf['general']['syntax_plugin'] == 'dokuwiki') {
        $data = TextFormatter::render($data);
        // Convert most common html- but not xml-entities.
        $data = preg_replace('/&lsquo;/', '&#8216;', $data);
        $data = preg_replace('/&rsquo;/', '&#8217;', $data);
        $data = preg_replace('/&ldquo;/', '&#8220;', $data);
        $data = preg_replace('/&rdquo;/', '&#8221;', $data);
        echo $data;
    }
    else {
        if (preg_match('/^</', $data) === 0) {
            // Assume an old entry. Just can't rely on any tags to be valid.
            $data = strip_tags($data);
            $data = preg_replace('/&/', '&amp;', $data);
            $data = preg_replace('/</', '&lt;', $data);
            $data = preg_replace('/>/', '&gt;', $data);
            $data = preg_replace('/"/', '&quot;', $data);
            $data = '<p>' . nl2br($data) . '</p>';
        }
        else {
            // Assume a new entry. Problem cases when an old entry started with
            // < are just not handled well. Must draw the line somewhere, even if the
            // browser will not show it or has an error. Those cases should be quite few.
        }
        
        // Chrome complained loudly about this one. Firefox just didn't show anything...
        // Any more html entities produced by ckeditor that should be turned into
        // a numeric character reference? Add when found. Or check if we already have
        // somewhere an existing function to do that.
        $data = preg_replace('/&nbsp;/', '&#160;', $data);

        // Single case. Old entry that started with <. Can contain &'s too.
        // Convert to entity, without touching already existing entities.
        $data = preg_replace('/&(?!([a-z]+|#[0-9]+);)/', '&amp;', $data);
        
        // Still double quotes there? Convert any not appearing inside tags.
        // Not sure if ckeditor makes that kind of entries.
        $data = preg_replace('/"(?=[^>]*(<|$))/', '&quot;', $data);

        // Best alternative, although will strip some odd custom data from old entries.
        echo TextFormatter::render($data);
    } ?></div>
    </content>
    <author><name><?php echo Filters::noXSS($row['real_name']); ?></name></author>
    <id><?php echo Filters::noXSS($baseurl); ?>:<?php echo Filters::noXSS($row['task_id']); ?></id>
  </entry>
  <?php   endforeach; ?>
</feed>
