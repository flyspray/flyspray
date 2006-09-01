#!/usr/bin/php
<?php

$lines = file("flyspray-0.9.9-devel.pgsql");

foreach ($lines as $l)
{
  if (preg_match('|^ALTER TABLE ([^ ]+) ADD COLUMN ([^ ]+) ([^ ]+) NOT NULL DEFAULT ([^ ]+);$|', $l, $p))
  {
    echo "-- GENERATED CODE: add column '{$p[2]}' to the table '{$p[1]}'\n";
    echo "ALTER TABLE {$p[1]} ADD {$p[2]} {$p[3]};\n";
    echo "UPDATE {$p[1]} SET {$p[2]} = {$p[4]};\n";
    echo "ALTER TABLE {$p[1]} ALTER COLUMN {$p[2]} SET NOT NULL;\n";
    echo "ALTER TABLE {$p[1]} ALTER COLUMN {$p[2]} SET DEFAULT {$p[4]};\n";
    echo "-- GENERATED CODE END\n";
  }
  // not used, anymore
  else if (preg_match('|^ALTER TABLE ([^ ]+) ALTER COLUMN ([^ ]+) TYPE ([^ ]+);$|', $l, $p))
  {
    echo "-- GENERATED CODE: change column '{$p[1]}.{$p[2]}' type to '{$p[3]}'\n";
    echo "ALTER TABLE {$p[1]} ADD tmp_{$p[2]} {$p[3]};\n";
    echo "UPDATE {$p[1]} SET tmp_{$p[2]} = CAST({$p[2]} AS {$p[3]});\n";
    echo "ALTER TABLE {$p[1]} DROP COLUMN {$p[2]};\n";
    echo "ALTER TABLE {$p[1]} RENAME tmp_{$p[2]} TO {$p[2]};\n";
    echo "ALTER TABLE {$p[1]} ALTER COLUMN {$p[2]} SET NOT NULL;\n";
    echo "ALTER TABLE {$p[1]} ALTER COLUMN {$p[2]} SET DEFAULT '';\n";
    echo "-- GENERATED CODE END\n";
  }
  else
    echo $l;
}

?>

