<?php echo '<?xml version="1.0" ?>'; ?>

<rdf:RDF xmlns:dc="http://purl.org/dc/elements/1.1/" 
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
  xmlns="http://purl.org/rss/1.0/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <channel rdf:about="<?php echo Filters::noXSS($baseurl); ?>">
    <title><?php echo Filters::noXSS($fs->prefs['page_title']); ?></title>
    <link><?php echo Filters::noXSS($baseurl); ?></link>
    <description><?php echo Filters::noXSS($feed_description); ?></description>
    <dc:date><?php echo Filters::noXSS(date('Y-m-d\TH:i:s\Z',$most_recent)); ?></dc:date>
    <items>
      <rdf:Seq>
        <?php foreach($task_details as $row): ?>
        <rdf:li rdf:resource="<?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?>" />
        <?php endforeach; ?>
      </rdf:Seq>
    </items>
    <?php if($feed_image): ?>
    <image rdf:resource="<?php echo Filters::noXSS($feed_image); ?>" />
    <?php endif; ?>		
  </channel>
  <?php foreach($task_details as $row): ?>
  <item rdf:about="<?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?>">
    <title>FS#<?php echo Filters::noXSS($row['task_id']); ?>: <?php echo Filters::noXSS($row['item_summary']); ?></title>
    <link><?php echo Filters::noXSS(CreateURL('details', $row['task_id'])); ?></link>
    <dc:date><?php echo Filters::noXSS(date('Y-m-d\TH:i:s\Z',intval($row['last_edited_time']))); ?></dc:date>
    <dc:creator><?php echo Filters::noXSS($row['real_name']); ?></dc:creator>
    <description><?php echo Filters::noXSS(strip_tags(TextFormatter::render($row['detailed_desc']))); ?></description>
    <content:encoded><![CDATA[<?php echo TextFormatter::render($row['detailed_desc']); ?>]]></content:encoded>
  </item>
  <?php endforeach; ?>
</rdf:RDF>
