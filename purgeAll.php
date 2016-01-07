<?php
/* 
(C) 2006- Sigbert Klinke (sigbert@wiwi.hu-berlin.de), 

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

if (count($argv)!=4) {
  print "\nUsage: php purgeAll.php http://mywiki/ Loginname Password\n\n";
  die(1);
}

$wwwpre   = $argv[1];
$allpages = array();
$apfrom   = '';
do {
  $www    = $wwwpre . 'api.php?action=query&list=allpages&apfrom=' . urlencode($apfrom) . '&aplimit=500&format=php';
  print "Get  : $www\n";
  $pages  = unserialize(file_get_contents($www)); 
  $pages  = $pages['query']['allpages']; 
  foreach ($pages as $page) { $allpages[] = $page['title']; }
  $allpages = array_unique($allpages);
  sort($allpages, SORT_STRING);
  $apfrom   = end($allpages);
} while (count($pages)>1);
sort($allpages, SORT_STRING);

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// login
$postfields = 'action=login&lgname=' . $argv[2] . '&lgpassword=' . $argv[3];
curl_setopt($ch, CURLOPT_URL, $wwwpre . 'api.php'); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); 
curl_exec($ch);
// purge all pages
foreach($allpages as $page) {
  print "Purge: $page\n";
  $postfields = 'action=purge&titles=' . urlencode($page);
  curl_setopt($ch, CURLOPT_URL, $wwwpre . 'api.php'); 
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); 
  $pages = curl_exec($ch);
}
// logout
$postfields = 'action=logout';
curl_setopt($ch, CURLOPT_URL, $wwwpre . 'api.php'); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields); 
curl_exec($ch);
curl_close($ch);
?>
