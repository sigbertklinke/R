<?php

/* 
(C) 2006- Sigbert Klinke (sigbert@wiwi.hu-berlin.de)

This file is part of R/Octave Extension for Mediawiki.

The R/Octave Extension is free software: you can 
redistribute it and/or modify it under the terms of 
the GNU General Public License as published by the
Free Software Foundation, either version 3 of the 
License, or (at your option) any later version.

The R/Octave Extension is distributed in the hope that 
It will be useful, but WITHOUT ANY WARRANTY; without 
even the implied warranty of MERCHANTABILITY or 
FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
Public License for more details.

You should have received a copy of the GNU General 
Public License along with the R/Octave Extension. 
If not, see http://www.gnu.org/licenses/.
*/

defined('R_CMD_ALL')  || define('R_CMD_ALL',  -1);
defined('R_CMD_NONE') || define('R_CMD_NONE',  0);
defined('R_CMD_IO')   || define('R_CMD_IO',    1);
defined('R_CMD_UI')   || define('R_CMD_UI',    2);
defined('R_CMD_EXEC') || define('R_CMD_EXEC',  4);
defined('R_CMD_INFO') || define('R_CMD_INFO',  8);

include("ForbiddenCommands" . $argv[1] . ".php");

function regexp_escape ($txt) {
  $ret = str_replace('.', '\.', $txt);
  return ($ret);
}

$banned = array();
$n      = 0;
foreach ($commands as $key => $value) {
  for ($i = 2; $i < count($argv); $i++) {
    if ($value[0] & constant('R_CMD_' . $argv[$i])) {
      echo "$key\n";
      if (empty($value[1])) {
        $banned[$key] = $patpre . regexp_escape($key) . $patpost;
      } else {
        $banned[$key] = $value[1];
      }
      $n++;
      break;
    }
  }
}
$file = "FC_" . substr($argv[1], 0, 1);
for ($i = 2; $i < count($argv); $i++) $file .= substr($argv[$i], 0, 2);
file_put_contents($file, serialize($banned));
echo "Wrote $file with $n entries\n";

?>
