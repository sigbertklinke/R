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

class Convert {

  public $cmd;
  public $gin;
  public $gout;
  public $gopt;

  private function getCmd ($cmd, $post='') {
    $path = array('/usr/bin/', '/usr/local/bin/', '/bin/');
    $n    = count($path);  
    for ($i = 0; $i < $n; $i++) {
      $cmdf = $path[$i] . $cmd;
      if (file_exists($cmdf)) { return (trim($cmdf) . ' ' . $post); }
    }
    $cmdf = `which $cmd`;
    if ($cmdf!='') {
      $cmdf = trim($cmdf) . ' ' . $post;
      return $cmdf;
    }
    throw new Exception('Convert.php: ' . $cmd . ' command not found');
  }

  function __construct($in, $out, $option=' ') { 
    if (in_array($in, array('pdf')) && in_array($out, array('png', 'jpg'))) {
	$this->cmd  = $this->getCmd('convert');
	$this->gin  = $in;
	$this->gout = $out;
	$this->gopt = $option;
    }
    if (!isset($this->cmd)) { throw new Exception('Convert.php: Either graphic input or output is not supported: ' . $in . ' -> ' . $out); }
  } 

  public function convert($filename) { 
    $path = pathinfo($filename);
    if (strcmp($path['extension'], $this->gin)) throw new Exception('Convert.php: Can not convert "' . $filename . '"');
    $fn   = $path['dirname'] . DIRECTORY_SEPARATOR . $path['basename'] . '.' . $this->gout;
    $cmd = $this->cmd . ' ' . $filename . ' ' .$this->gopt . ' ' .$fn . ' 2>&1';

    $out = null;
    $val = -1;
    exec ($cmd, $out, $val);
    if ($val!=0) { throw new Exception("Convert.php: " . implode("\n", $out)); }
    if (!file_exists($fn)) { throw new Exception('Convert.php: File "' . $fn . '" does not exist'); }
    return (basename($fn));
  }

}

?>
