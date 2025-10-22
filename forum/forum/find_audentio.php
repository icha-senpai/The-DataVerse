<?php
$mysqli = new mysqli('localhost', 'root', '', 'x2'); // change creds as needed
$term = 'Audentio';

$res = $mysqli->query("
  SELECT TABLE_NAME, COLUMN_NAME 
  FROM INFORMATION_SCHEMA.COLUMNS 
  WHERE TABLE_SCHEMA = DATABASE()
  AND DATA_TYPE IN ('varchar', 'text', 'mediumtext', 'longtext')
");

while ($row = $res->fetch_assoc()) {
    $table = $row['TABLE_NAME'];
    $col   = $row['COLUMN_NAME'];

    $check = $mysqli->query("SELECT COUNT(*) AS c FROM `$table` WHERE `$col` LIKE '%$term%'");
    $count = $check->fetch_assoc()['c'] ?? 0;
    if ($count > 0) {
        echo "[$table.$col] → $count matches\n";
    }
}
