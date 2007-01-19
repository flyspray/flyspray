<?php
   /**********************************************************\
   | This script converts the categories table to the new      |
   | format.                                                   |
   \***********************************************************/
   
function rebuild_tree($parent, $left, $pr) {
    global $db;
   // the right value of this node is the left value + 1
   $right = $left+1;

   // get all children of this node
   $result = $db->Query('SELECT category_id FROM {list_category} WHERE parent_id = ? AND project_id = ?', array($parent, $pr));

   while ($row = $db->FetchRow($result)) {
       // recursive execution of this function for each
       // child of this node
       // $right is the current right value, which is
       // incremented by the rebuild_tree function
       $right = rebuild_tree($row['category_id'], $right, $pr);
   }

   // we've got the left value, and now that we've processed
   // the children of this node we also know the right value
   $db->Query('UPDATE {list_category} SET lft= ?, rgt= ? WHERE category_id = ?', array($left, $right, $parent));
   $sql = $db->Query('SELECT * FROM {list_category} WHERE category_id = ? OR project_id=? AND parent_id=-1', array($parent, $pr));
   if (!$db->CountRows($sql)) {
       $db->Query('INSERT INTO {list_category} (project_id, lft, rgt, category_name, parent_id) VALUES(?,?,?,?,-1)',
                  array($pr,$left,$right,'root'));
   }
   // return the right value of this node + 1
   return $right+1;
} 

$projects = $db->Query('SELECT project_id FROM {projects}');

// Global project
rebuild_tree(0, 1, 0);
while ($pr = $db->FetchRow($projects)) {
    rebuild_tree(0, 1, $pr['project_id']);
}

?>