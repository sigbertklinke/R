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

abstract class Engine {

  public $cmd;
  public $desc;
  public $lang;
  public $name;
  public $convert;
  public $category;
  public $security;
  public $sudo;
  public $banned;
  public $extr;

  private static $langpattern = array (
    'rsplus' => '/^(<pre>>\s|>\s|\+\s)(.*)$/',
    'matlab' => '/^(<pre>octave>\s|octave>\s)(.*)$/'
  );

  /* abstract functions, must be defined in derived classes */
  abstract function render($params, $cmd='');  

  /* static functions */
  static $allowedAttr = array('output'    => '',
			      'echo'      => '', 
			      'style'     => '', 
			      'convert'   => '',
			      'alt'       => '',
			      'onsave'    => '',
			      'iframe'    => '',
			      'name'      => '',
			      'label'     => '',
			      'workspace' => '',
			      'category'  => '',
			      'engine'    => ''
			      );
  static $allowedOutput = array('text', 'html', 'display', 'wiki');
  
  static function check($forbiddenCommands, $input) {
    foreach ($forbiddenCommands as $key => $value) {
      if (strpos($input, $key)!== false) { // found something suspicious
        if (preg_match($value, $input, $match)) 
          throw new Exception ('Engine.php: found forbidden command "' . $match[0] . '"');   
      }
    } 
  }

  static function makeStyle ($param, $style=array()) {
    $list = explode (';', $param);
    $n    = count($list);
    for ($i=0; $i<$n; $i++) {
      $pos = strpos($list[$i], ':');
      if ($pos!==false) {
        $key = substr($list[$i], 0, $pos);
        $val = substr($list[$i], $pos+1);
        $style[$key] = $val;
      }
    }
    $ret = '';
    foreach ($style as $key => $val) $ret .= $key . ':' . $val . ';';
    return $ret;
  }

  static function makeHTML ($input, $style, $pre=1) {
    $ret = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $ret = $ret . '<html><head><meta http-equiv="expires"  
content="0"></head><body>';
    $ret = $ret . ($pre ? '<pre' . $style . '>' : '') . $input . ($pre ? '</pre>' : '');
    //$ret = $ret . '<hr>' . date("d.m.Y") . ' ' . date("H:i:s");
    $ret = $ret . '</body></html>';
    return $ret;
  }

  static function formatOutput ($out, $params) {
    $output = (array_key_exists('output', $params) ? $params['output'] : 'text');
    switch ($output) {
    case 'wiki':
      return ($out);
    case 'html':
    case 'display':
    default: // text or anything else
      $style = (array_key_exists('style', $params) ? $params['style'] : '');
      if (!empty($style)) $style= ' style="' . $style .'"';
      if (array_key_exists('iframe', $params)) return(Engine::makeHTML($out, $style));
      return ('<pre' . $style . '>' . trim($out) . '</pre>');
    }
    throw new Exception ('Engine.php: you will hopefully never see this!');
  }

  static function getCmd ($cmd) {
    if (file_exists($cmd)) return (trim($cmd));
    $path = array('/usr/bin/', '/usr/local/bin/', '/bin/');
    $n    = count($path);  
    for ($i = 0; $i < $n; $i++) {
      $cmdf = $path[$i] . $cmd;
      if (file_exists($cmdf)) return (trim($cmdf)); 
    }
    $cmdf = `which $cmd`;
    if (empty($cmdf)) throw new Exception ('Engine.php: "' . $cmd . '" command not found');
    return ($cmdf);
  }

  static function engineIndex ($enginename) {
    global $extr;
    foreach ($extr->engine as $key => $value) {
      if (strcmp($value->name, $enginename)==0) return ($key);
    }
    throw new Exception('Engine.php: Engine "' . $enginename . '" not found');
  }

  static function doHighlight ($lang, $match, $parser, $frame) {
    $code = '<syntaxhighlight lang="' . $lang . '" enclose="none">' . $match[2] . '</syntaxhighlight>'; 
     return ($match[1] .	
            $parser->recursiveTagParse($code, $frame));
  }

  static function highlightSyntax ($lang, $code, $parser, $frame) {    
    if (array_key_exists($lang, self::$langpattern)) {
      $callback = function ($match) use ($lang, $parser, $frame) {
	return(Engine::doHighlight($lang, $match, $parser, $frame));
      };
      $lines = explode("\n", $code);      
      foreach ($lines as &$line) {
	$line = preg_replace_callback (self::$langpattern[$lang], $callback, $line, -1, $count);
      }
      $hgcode = implode("\n", $lines); 
    } else {
      $hgcode = $code;
    }
    return($hgcode);
  }
 
  static function checkJavaScriptAndPHP ($engine, $code) {
    if (preg_match('/<\s*\?\s*php/i', $code)>0) { 
      throw new Exception ('Engine.php: security check failed: found &lt;?php ...'); 
    }
    if (preg_match('/<\s*script/i', $code)>0) { 
      throw new Exception ('Engine.php: security check failed: found &lt;script ...');
    } 
  }  

  static function renderMTag ($input, $params, $parser, $frame) {
    if (!array_key_exists('engine', $params)) { $params['engine'] = 'M'; }
    return(Engine::renderTag ($input, $params, $parser, $frame));
  }

  static function renderTag ($input, $params, $parser, $frame) {
    global $extr;
    try {
      $darr   = array_diff_key($params, self::$allowedAttr);
      if (!empty($darr)) throw new Exception('Engine.php: unknown attribute(s) "' . implode('", "', array_keys($darr)) . '"');
      if (!array_key_exists('output', $params)) $params['output'] = 'text';
      if (!in_array($params['output'], Engine::$allowedOutput)) throw new Exception('Engine.php: unknown value "' . $params['output'] . '" for attribute "output"');

      $wiki   = array_key_exists('wiki', $params) || (strcmp($params['output'], 'wiki')==0);
      $sha1   = $extr->hashtxt($extr->R_DTS . $input . serialize($params)); 
      $name   = array_key_exists('name', $params) ? $params['name'] : 
                (array_key_exists('label', $params) ? $params['label'] : $sha1);
      $direct = !array_key_exists('iframe', $params);
      if ($wiki && (!$direct || array_key_exists('echo', $params))) throw new Exception ('Engine.php: You can not use output="wiki" and use "echo" or "iframe"');
      $params['geshi']  = $extr->options['usegeshi'] && array_key_exists('echo', $params) && strcmp($params['echo'], 'nogeshi');
      $workhorse = (array_key_exists('engine', $params) ? Engine::engineIndex($params['engine']) : 0);
      Engine::check($extr->engine[$workhorse]->banned, $input);
      $params['engine'] = $extr->engine[$workhorse]->name;
      $params['sha1']   = $sha1;
      $params['input']  = trim($input);
      if (array_key_exists('name', $params) && !$direct) { 
        $params['name'] = rawurlencode($parser->getTitle() . '_' . $params['name']);
        file_put_contents(R_DIR . $params['name'] . '.param', serialize($params));
      }
      $fn = $extr->engine[$workhorse]->render($params);
      if (array_key_exists('iframe', $params)) {
	$iframe = Engine::makeStyle ($params['iframe'], array('width' => '100%', 'height' => '250px'));
	$ret = '<iframe name="' . $name . '" style="' . $iframe . '" src="' . $extr->engine[$workhorse]->url($fn) . '">Sorry, your browser does not support &lt;iframe...&gt;... &lt;/iframe&gt; !</iframe>';
      } else {
	$ret = file_get_contents ($fn);
      }
      
      if ($wiki) $ret = $parser->recursiveTagParse($ret, $frame);
      
      $category = array_key_exists('category', $params) ? $params['category'] : $extr->engine[$workhorse]->category;
      if (!empty($category)) {
	$ret .= $parser->recursiveTagParse(sprintf('[[Category:%s]]', $category), $frame);
      } 

      if ($extr->engine[$workhorse]->security>0) Engine::checkJavaScriptAndPHP ($extr->engine[$workhorse], $ret);
      if ($params['geshi']) $ret = Engine::highlightSyntax($extr->engine[$workhorse]->lang, $ret, $parser, $frame);
      
      return ($ret);
    } catch (Exception $e) {
      $extr->exception_log($e);
      return ('<pre style="color:red">' .  htmlentities($e->getMessage()) . '</pre>in<pre>' . htmlentities($input) . '</pre>');
    }
  }

  static function renderCGI ($post) {
    global $extr;
    try {
      $name     = $post['R'];
      unset($post['R']);

      $fn = R_DIR . $name . '.param';
      if (!file_exists($fn)) throw new Exception ('Engine.php: file "' . $fn . '" not found');

      $params = unserialize(file_get_contents($fn));
      if (!array_key_exists('iframe', $params)) throw new Exception ('Engine.php: iframe attribute required in corresponding &lt;R...&gt; tag');
      if (!array_key_exists('sha1', $params)) throw new Exception ('Engine.php: $params["sha1"] not found');
      if (!array_key_exists('engine', $params)) throw new Exception ('Engine.php: $params["engine"] not found');

      $workhorse = Engine::engineIndex($params['engine']);
      $cmd = '';
      foreach ($post as $key => $val) $cmd .= $extr->engine[$workhorse]->assign ($key, '"' . $val . '"'); 
      $params['sha1'] .= ( '_' . $extr->hashtxt(microtime()));
      $fn  = $extr->engine[$workhorse]->render($params, $cmd);

      $ret = file_get_contents($fn); 
      if ($extr->engine[$workhorse]->security>0) Engine::checkJavaScriptAndPHP ($extr->engine[$workhorse], $ret);
    }
    catch(Exception $e) {
      $extr->exception_log($e);
      return (Engine::makeHTML(htmlentities($e->getMessage()), ' style="color:red"'));
    }
    return ($ret);
  }

/* non-static functions */
  function __construct(&$extr, $name, $cmd, $convert, $banfile, $category, $security=null, $sudo=null) {
    $this->extr     =& $extr;
    $this->name     = $name;
    system("mkdir -p " . R_DIR . $this->name);
    $this->cmd      = $cmd;
    $this->convert  = $convert;
    $this->category = $category;
    $this->security = (is_null($security) ? 1 : $security);
    $extr->extension_log($this->extr->R_EXT . $banfile);
    if (!file_exists($this->extr->R_EXT . $banfile)) {
      throw new Exception("Engine.php: file \"" . $banfile . "\" not found");
    }
    $this->banned   = unserialize(file_get_contents($extr->R_EXT . $banfile));
    $this->sudo     = (is_null($sudo) ? Engine::getCmd('sudo') . ' -u rd ' : $sudo);
    $this->lang     = 'text';
  }

  function dir ($filename) {
    return (R_DIR . $this->name . DIRECTORY_SEPARATOR . basename($filename));
  }

  function url ($filename) {
    global $extr;
    return ($extr->R_URL . $this->name . DIRECTORY_SEPARATOR . basename($filename));
  }

}

?>
