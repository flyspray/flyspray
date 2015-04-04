<?php echo '<?xml version="1.0" ?>'; ?>

<rss version="2.0">
  <channel>
    <title><?php echo Filters::noXSS($fs->prefs['page_title']); ?></title>
    <lastBuildDate><?php echo Filters::noXSS(date('r',$most_recent)); ?></lastBuildDate>
    <description><?php echo Filters::noXSS($feed_description); ?></description>
    <link><?php echo Filters::noXSS($baseurl); ?></link>
    <?php if($feed_image): ?>
    <image>
      <url><?php echo Filters::noXSS($feed_image); ?></url>
      <link><?php echo Filters::noXSS($baseurl); ?></link>
      <title>[Logo]</title>
    </image>
    <?php endif;
    foreach($task_details as $row):?>
    <item>
      <title>FS#<?php echo Filters::noXSS($row['task_id']); ?>: <?php echo Filters::noXSS($row['item_summary']); ?></title>
      <author><?php echo Filters::noXSS($row['real_name']); ?></author>
      <pubDate><?php echo Filters::noXSS(date('r',intval($row['date_opened']))); ?></pubDate>
      <description><![CDATA[<?php
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
                $data = nl2br($data);
            }
            else {
                // Assume a new entry. Problem cases when old entry started with
                // < are just not handled. Must draw the line somewhere, even if the
                // browser will not show it or has an error. Those cases should be quite few.
            }

            // Single case. Old entry that started with <. Can contain &'s too.
            // Convert to entity, without touching already existing entities.
            $data = preg_replace('/&(?!([a-z]+|#[0-9]+);)/', '&amp;', $data);
        
            // Still double quotes there? Convert any not appearing inside tags.
            // Not sure if ckeditor makes that kind of entries.
            $data = preg_replace('/"(?=[^>]*(<|$))/', '&quot;', $data);
            // Best alternative, although will strip some odd custom data from old entries.
            echo TextFormatter::render($data);
        }
        ?>]]></description>
      <link><?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?></link>
      <guid><?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?></guid>
    </item>
    <?php endforeach; ?>
  </channel>
</rss>
