{!'<?xml version="1.0" ?>'}
<rdf:RDF xmlns:dc="http://purl.org/dc/elements/1.1/" 
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
  xmlns="http://purl.org/rss/1.0/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <channel rdf:about="{$baseurl}">
    <title>{$fs->prefs['page_title']}</title>
    <link>{$baseurl}</link>
    <description>{$feed_description}</description>
    <dc:date>{date('Y-m-d\TH:i:s\Z',$most_recent)}</dc:date>
    <items>
      <rdf:Seq>
        <?php foreach($task_details as $row): ?>
        <rdf:li rdf:resource="{CreateURL('details', $row['task_id'])}" />
        <?php endforeach; ?>
      </rdf:Seq>
    </items>
    <?php if($feed_image): ?>
    <image rdf:resource="{$feed_image}" />
    <?php endif; ?>		
  </channel>
  <?php foreach($task_details as $row): ?>
  <item rdf:about="{CreateURL('details', $row['task_id'])}">
    <title>FS#{$row['task_id']}: {$row['item_summary']}</title>
    <link>{CreateURL('details', $row['task_id'])}</link>
    <dc:date>{date('Y-m-d\TH:i:s\Z',intval($row['last_edited_time']))}</dc:date>
    <dc:creator>{$row['real_name']}</dc:creator>
    <description>{strip_tags(TextFormatter::render($row['detailed_desc']))}</description>
    <content:encoded><![CDATA[{!TextFormatter::render($row['detailed_desc'])}]]></content:encoded>
  </item>
  <?php endforeach; ?>
</rdf:RDF>
