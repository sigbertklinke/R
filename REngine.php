<?php
/* 
(C) 2006- Sigbert Klinke (sigbert@wiwi.hu-berlin.de), 
 Markus Cozowicz, Alex Browne, Michael Cassin

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

class REngine extends Engine {

  static function forbiddenCommands() {
    return("ForbiddenCommandsR");
  }
  
  static function assign ($var, $val) {
    return ($var . '<-' . $val . "\n");
  }

  static function text ($val) {
    return ("'" . $val . "'");
  }

  static function cmd ($cmd /* ... */) {
    $params = func_get_args();
    array_shift( $params );
    return ($cmd . '(' . implode(',', $params) . ")\n");
  }

  function __construct(&$extr, $params) {
    parent::__construct($extr, $params['name'], Engine::getCmd($params['cmd']) . ' --vanilla --quiet',
                        new Convert('pdf', 'png'), $params['forbidden'], (isset($params['category']) ? 'R' : $params['category']));
    $pre = $this->dir('@.R');
    file_exists($pre) || copy($extr->R_EXT . '@.R', $pre);
    $output = shell_exec(Engine::getCmd($params['cmd']) . ' --version');
    $pos    = strpos($output, "\n");
    $this->desc = substr($output, 0, $pos);
    $this->lang = 'rsplus';
  }

  function render ($params, $cmdplus='') {
    try { 
      $htm = $this->dir($params['sha1'] . '.html');
      if (!file_exists($htm) || array_key_exists('onsave', $params)) {

        // create and write program
        $prg = ''; 
        $rws = (array_key_exists('workspace', $params) ? $this->dir($params['workspace']) : null);
        if (!is_null($rws)) $prg .= REngine::cmd('sys.load.image', REngine::text($rws), 'TRUE');
      
        $pdf  = $this->dir($params['sha1'] . '_%i.pdf');
        $prg .= REngine::assign('rpdf',    REngine::text($pdf));
        $prg .= REngine::assign('rpdfno', '0');
        $prg .= REngine::assign('rhtml',   REngine::text(''));
        $prg .= REngine::assign('rfiles',  REngine::text($this->dir('')));
        $prg .= REngine::cmd ('source', REngine::text($this->dir('@.R')));
        $prg .= REngine::assign('rout',    REngine::text($params['output']));
        $prg .= REngine::cmd ('cat', REngine::text($this->extr->R_SOP . '\n'));
	
        $prg .= $cmdplus . $params['input'] . "\n";
	
        $prg .= REngine::cmd('cat', REngine::text($this->extr->R_EOP . '\n'));
	$prg .= "while (dev.cur()>1) dev.off()\n";
	
        if (!is_null($rws)) $prg .= REngine::cmd('sys.save.image', REngine::text($rws));
	$prg .= REngine::cmd('q');
	
        $fn = $this->dir($params['sha1'] . '.R');
        file_put_contents ($fn , $prg);
	
        // run program
        $rcmd = $this->cmd . (array_key_exists('echo', $params) ? ' < ' : ' --slave < ') . $fn . ' 2>&1';
        if ($this->security>1) $rcmd = $this->sudo . $rcmd;
        $out = array();
        $val = -1;
        exec ($rcmd, $out, $val);
        if ($val!=0) { throw new Exception("REngine.php: " . implode("\n", $out)); }

        $out = implode("\n", $out); 
        // delete in output everything before R_SOP 
        $pos = strpos($out, $this->extr->R_SOP);
        if (array_key_exists('echo', $params)) $pos = strpos($out, $this->extr->R_SOP, $pos+1);
	$pos = strpos($out, "\n", $pos+1);
        $out = substr($out, $pos);

        // delete in output everything after R_EOP 
        $pos = strrpos($out, $this->extr->R_EOP);
        $eoo = substr($out, $pos+1);
        if (array_key_exists('echo', $params)) $pos = strrpos($out, $this->extr->R_EOP, $pos-strlen($out)-1);
	$pos = strrpos($out, "\n", $pos-strlen($out)-1);
        $out = substr($out, 0, $pos);
        // collect all images in $eoo
        $matches = null;
        $nmatch  = preg_match_all ('(<img src="(.*?)">)', $eoo, $matches);
        for ($i = 0; $i<$nmatch; $i++) $out .= "\n" . $matches[0][$i];
        // convert all images
        $matches = null;
        $nmatch  = preg_match_all ('(<img src="(.*?)">)', $out, $matches);
        for ($i = 0; $i<$nmatch; $i++) {
	  $fn  = $this->convert->convert ($matches[1][$i]);
          $img = '<a href="' . $this->url($matches[1][$i]) . '"><img src="' . $this->url($fn) . '"></a>';
          $out = str_replace($matches[0][$i], $img, $out); 
        }

        file_put_contents ($htm , Engine::formatOutput($out, $params));
      }
    }
    catch (Exception $e) {
      if (file_exists($htm)) unlink($htm); 
      throw $e;
    }
    return ($htm);
  }

}

?>
