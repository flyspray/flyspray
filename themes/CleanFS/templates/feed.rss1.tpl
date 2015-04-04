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
 <?php
    $data = $row['detailed_desc'];

    if ($conf['general']['syntax_plugin'] == 'dokuwiki') {
        $data = TextFormatter::render($data);
        // Convert most common html- but not xml-entities.
        $data = preg_replace('/&lsquo;/', '&#8216;', $data);
        $data = preg_replace('/&rsquo;/', '&#8217;', $data);
        $data = preg_replace('/&ldquo;/', '&#8220;', $data);
        $data = preg_replace('/&rdquo;/', '&#8221;', $data);
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
        $data = TextFormatter::render($data);
    }
 ?>
    <description><?php echo Filters::noXSS(strip_tags($data)); ?></description>
    <content:encoded><![CDATA[<?php echo $data; ?>]]></content:encoded>
  </item>
  <?php endforeach; ?>
</rdf:RDF>
