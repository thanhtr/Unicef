<?php

// Connecting, selecting database
$link = mysql_connect('localhost', 'unicef', 'Xhn#ha8#o')
    or die('Could not connect: ' . mysql_error());
#echo 'Connected successfully';
mysql_select_db('unicef') or die('Could not select database');

// Performing SQL query
$query = "SELECT * FROM print_log WHERE timestamp > '2013-10' AND timestamp < '2014-03'";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

if ($_GET['f'] == 'json') {
  header('Content-Type: application/json');

  $r = array();
  while($line=mysql_fetch_assoc($result)) {
    $line = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $line);
    $line['parameters'] = unserialize($line['parameters']);
    if ($line['parameters']) $line = array_merge($line, $line['parameters']);
    unset($line['parameters']);
    if (true || !is_null($line)) $r[] = $line;
  }

  echo json_encode($r);

} else {
  if ($_GET['f'] == 'csv') {
    header('Content-Type: text/csv');
  } else {
    header('Content-Type: text/html');
  }

  if ($_GET['f'] != 'csv') echo "<pre>\n";
#  $fields = array();
#  for( $i = 0; $i < mysql_num_fields($result); $i++ ) {
#    $fields[] = mysql_fetch_field($result, $i)->name;
#  }
  $fields = array('print_log_id', 'template_id', 'user_id', 'timestamp', 'format', 'backgroundImage', 'logoImage', 'language', 'greeting', 'name', 'logoImage');

  echo var_dump($fields) . "\n";
  echo implode(',', $fields) . "\n";
  while($line=mysql_fetch_assoc($result)) {
    $line['parameters'] = unserialize($line['parameters']);
    $line = array_merge($line, $line['parameters']);
    #echo implode(',', $line) . "\n";
    foreach($fields as $f) {
      echo $line[$f] . ',';
    }
    echo "\n";
  }
  if ($_GET['f'] != 'csv') echo "<pre>\n";
}

die;

// Printing results in HTML
echo "<table>\n";
while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo "\t<tr>\n";
      $line['parameters'] = deserialize($line['parameters']);
    echo json_encode($line);
    foreach ($line as $col_value) {
        echo "\t\t<td>$col_value</td>\n";
    }
    echo "\t</tr>\n";
}
echo "</table>\n";

// Free resultset
mysql_free_result($result);

// Closing connection
mysql_close($link);
?>
