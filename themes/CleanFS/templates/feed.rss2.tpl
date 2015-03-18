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
      <description><![CDATA[<?php echo str_replace(chr(13), "<br />", Filters::noXSS(strip_tags($row['detailed_desc']))); ?>]]></description>
      <link><?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?></link>
      <guid><?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?></guid>
    </item>
    <?php endforeach; ?>
  </channel>
</rss>
