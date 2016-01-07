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

class MEngine extends Engine {

  static function forbiddenCommands() {
    return("ForbiddenCommandsOctave");
  } 

  static function assign ($var, $val) {
    return ($var . '=' . $val . ";\n");
  }

  static function text ($val) {
    return ("\"" . $val . "\"");
  }

  static function cmd ($cmd /* ... */) {
    $params = func_get_args();
    array_shift( $params );
    return ($cmd . '(' . implode(',', $params) . ");\n");
  }

  function __construct(&$extr, $params) {  //($name, $cmd, $banfile, $category=null) { 
    parent::__construct($extr, $params['name'], Engine::getCmd($params['cmd']) . ' -qfH --no-window-system',
                        new Convert('pdf', 'png', '-trim'), $params['forbidden'], (isset($params['category']) ? 'M' : $params['category']));
    $pre = $this->dir('@.m');
    file_exists($pre) || copy ($this->extr->R_EXT . '@.m', $pre);
    $output = shell_exec(Engine::getCmd($params['cmd']) . ' --version');
    $pos    = strpos($output, "\n");
    $this->desc = substr($output, 0, $pos);
    $this->lang = 'matlab';
  }

  function render ($params, $cmdplus='') {
    try { 
      $htm  = $this->dir($params['sha1'] . '.html');
      $echo = array_key_exists('echo', $params);

      if (!file_exists($htm) || array_key_exists('onsave', $params)) {

        // create and write program
        $prg = ''; 
        $rws = (array_key_exists('ws', $params) ? $this->dir($params['ws']) : null);
        if (!is_null($rws)) $prg .= MEngine::cmd('load', MEngine::text($rws));
      
        $pdf  = $this->dir($params['sha1'] . '_%i.pdf');
        $prg .= MEngine::assign('global rpdf',    MEngine::text($pdf));
        $prg .= MEngine::assign('global rpdfno', '0');
        $prg .= MEngine::assign('global rhtml',   MEngine::text(''));
        $prg .= MEngine::assign('global rfiles',  MEngine::text($this->dir('')));
        $prg .= MEngine::assign('global rout',  MEngine::text($params['output']));
        $prg .= MEngine::cmd('source', MEngine::text($this->dir('@.m')));
	$prg .= MEngine::cmd('addpath', MEngine::text($this->extr->R_EXT), MEngine::text("-end"));

	if ($echo) $prg .= MEngine::cmd ('echo_executing_commands', 1);
        $prg .= MEngine::cmd ('disp', MEngine::text($this->extr->R_SOP));

        $prg .= $cmdplus . $params['input'] . "\n";

        $prg .= MEngine::cmd('disp', MEngine::text($this->extr->R_EOP));
	if ($echo) $prg .= MEngine::cmd ('echo_executing_commands', 0);

//	$prg .= "while (dev.cur()>1) dev.off()\n";
	
        if (!is_null($rws)) $prg .= MEngine::cmd('save', MEngine::text($rws));
	$prg .= MEngine::cmd('quit', 0);
	
        $fn = $this->dir($params['sha1'] . '.m');
	file_put_contents ($fn , $prg);
	
        // run program
        $rcmd = $this->cmd . ' ' .$fn . ' 2>&1';
        if ($this->security>1) $rcmd = $this->sudo . $rcmd;
        $out = array();
        $val = -1;
        exec ($rcmd, $out, $val);
        if ($val!=0) { throw new Exception("MEngine.php: " . implode("\n", $out)); }
        $out = implode("\n", $out); 
        // delete in output everything before R_SOP 
        $pos = strpos($out, $this->extr->R_SOP);
        if ($echo) $pos = strpos($out, $this->extr->R_SOP, $pos+1);
	$pos = strpos($out, "\n", $pos+1);
        $out = substr($out, $pos);

        // delete in output everything after R_EOP 
        $pos = strrpos($out, $this->extr->R_EOP);
        $eoo = substr($out, $pos+1);
        if ($echo) $pos = strrpos($out, $this->extr->R_EOP, $pos-strlen($out)-1);
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
