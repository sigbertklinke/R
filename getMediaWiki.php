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

print "Downloading: http://www.mediawiki.org\n";
$mainpage = file_get_contents("http://www.mediawiki.org");
if (preg_match_all('/title="Release notes\/(.*?)">(.*?)<\/a>/', $mainpage, $matches)) {
  foreach($matches[0] as $key => $value) {
    $fn = 'mediawiki-' . $matches[2][$key] . '.tar.gz';
    if (!file_exists($fn)) {
      print "Downloading: $fn\n";
      $tgn   = 'http://releases.wikimedia.org/mediawiki/' . $matches[1][$key] . '/' . $fn;
      $targz = file_get_contents($tgn);
      file_put_contents($fn, $targz);
    } else {
      print "File exists: $fn\n";
    }
  }
}

?>
