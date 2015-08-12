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
      <div xmlns="http://www.w3.org/1999/xhtml">
        <?php echo TextFormatter::render($row['detailed_desc']); ?>

      </div>
    </content>
    <author><name><?php echo Filters::noXSS($row['real_name']); ?></name></author>
    <id><?php echo Filters::noXSS($baseurl); ?>:<?php echo Filters::noXSS($row['task_id']); ?></id>
  </entry>
  <?php   endforeach; ?>
</feed>
