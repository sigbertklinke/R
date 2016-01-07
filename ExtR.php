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

/*

R/Octave Plugin for Mediawiki
(C) 2006- Sigbert Klinke (sigbert@wiwi.hu-berlin.de),

This program is free software; you can redistribute it and/or modify  
it under the terms of the GNU General Public License as published by  
the Free Software Foundation; either version 2 of the License, or (at  
your option) any later version.

This program is distributed in the hope that it will be useful, but  
WITHOUT ANY WARRANTY; without even the implied warranty of  
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU  
General Public License for more details.

You should have received a copy of the GNU General Public License  
along with this program; if not, write to the Free Software  
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110, USA

*/

class ExtR {

   public $R_EXT;
   public $R_URL;
   public $R_CGI;
   public $R_LOG;
   public $R_SOP;
   public $R_EOP;
   public $R_HASHMTD;
   public $JQ_CSS;

   public $creditExtension;
   public $creditSpecialPage;
   public $jQueryUI;
   public $jQueryVersion;
   public $engine;
   public $options;

   public $stdEngine = array(
     array('cmd'  => 'R',      'forbidden' => 'FC_RAL',
           'tag'  => 'R',      'category'  => 'R',
           'name' => 'R',      'class'     => 'REngine'),
     array('cmd'  => 'octave', 'forbidden' => 'FC_OAL',
           'tag'  => 'M',      'category'  => 'M',
           'name' => 'M',      'class'     => 'MEngine')
   );

   private $tags = array();

/* static functions */ 
   public static function getInstance($filename, $options) {
     return (file_exists($filename) ? unserialize(file_get_contents($filename)) : new ExtR($filename, $options));
   }

   public static function wfRParse() {
     global $wgParser;
     # register the extension with the WikiText parser
     $wgParser->setHook("Rform",   "Rform::renderRform" );
     $wgParser->setHook("Rinput",  "Rform::renderRinput");
     $wgParser->setHook("Rarea",   "Rform::renderRarea" );
     $wgParser->setHook("R",       "Engine::renderTag"  );
     $wgParser->setHook("M",       "Engine::renderMTag" );
     return true;
   }

/* non-static functions */ 
   public function hashmethod() {
     $arr = array_intersect(array('tiger160,3', // 40 length of result
			          'tiger192,3', // 48
			          'ripemd256',  // 64
			          'ripemd320',  // 80 
			          'sha384',     // 96
			          'sha512',     // 128
			          'sha1'),      // 40 the fallback(!)
			     hash_algos());
     return ($arr[0]);
   }

   public function hashtxt ($txt) {
     return (hash($this->R_HASHMTD, $txt));
   }    

   public function getVersion($filename, $pattern) {
     $fc = file_get_contents($filename);
     preg_match($pattern, $fc, $match);
     return($match[1]);
   }

   public function extension_log ($e) {
     return(error_log(date('[D M d H:i:s Y] ') . print_r($e, true) . "\n", 3, $this->R_LOG));
   }

   public function exception_log (Exception $e) {
     return(error_log(date('[D M d H:i:s Y] ') . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n", 3, $this->R_LOG));
  }

   private function jQueryCSS ($options) {
     global $wgVersion;
     if(version_compare($wgVersion, '1.22')<0) return('');
     $themepattern = $this->R_EXT . 'css/' . $options['uitheme'] . '/jquery-ui-*.custom.css';
     $themefile  = reset(glob($themepattern));
     if ($themefile===false) {
       $this->extension_log($themepattern . ' failed');
       $themepattern = $this->R_EXT . 'css/sunny/jquery-ui-*.custom.css';
       $themefile    = reset(glob($themepattern));
       if ($themefile===false) return('');
     }
     return(substr($themefile, strlen($this->R_EXT)));
   }

   private function jQueryTheme ($options) {
     if (!$this->JQ_CSS) return ('');
     if (strpos($this->JQ_CSS, $options['uitheme']) !== false) return ($options['uitheme']);
     if (strpos($this->JQ_CSS, 'sunny') !== false) return ('sunny');
     $this->extension_log('Unknown jQuery theme: ' . $this->JQ_CSS);
     return ('');
   }

   private function setupEngines ($options) {
     $userengines = isset($options['engines']) ? $options['engines'] : $this->stdEngine;
     foreach ($userengines as $current) {
       if (!isset($current['cmd']) || !isset($current['class'])) throw new Exception ('ExtR.php: for an engine "cmd" and "class" are required');
       foreach ($this->stdEngine as $default) {
         if ($current['class']==$default['class']) {
           $fullEngine     = array_merge($default, $current);
           $this->engine[] = new $current['class']($this, $fullEngine);  
           break;
         }
       }
     }
   }

   protected function __construct($filename, $options) {
     global $wgServer, $wgScriptPath, $wgVersion;
     $this->options   = $options;
     // set up constants
     $this->R_DTS     = date('r');
     $this->R_VER     = '0.14';
     $this->R_EXT     = getcwd() . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'R' . DIRECTORY_SEPARATOR;
     $this->R_URL     = $wgServer . $wgScriptPath . DIRECTORY_SEPARATOR . 'Rfiles' . DIRECTORY_SEPARATOR;
     $this->R_CGI     = $this->R_URL . 'Rcgi.php';
     $this->R_LOG     = R_DIR . 'ExtR.log';
     $this->R_SOP     = '<!--- Start of program --->';
     $this->R_EOP     = '<!--- End of program --->';
     $this->R_HASHMTD = ExtR::hashmethod();
     $this->JQ_CSS    = $this->jQueryCSS($options);
     $this->JQ_THEME  = $this->jQueryTheme($options);
     // setup credits
     $this->creditExtension = 
       array('path'         => __FILE__,
             'name'         => 'R extension',
             'author'       => 'Sigbert Klinke, Markus Cozowicz, Alex Browne, Michael Cassin',
             'version'      => 'v' . $this->R_VER,
             'url'          => 'http://mars.wiwi.hu-berlin.de/mediawiki/sk/index.php/R_Plugin_for_MediaWiki',
             'description'  => 'This extension allows to embed output of R and octave (graphics/text) into wiki pages');
     $this->creditSpecialPage = 
       array('path'         => __FILE__, 
             'name'         => 'R extension',
             'author'       => 'Sigbert Klinke, Markus Cozowicz, Alex Browne, Michael Cassin',
             'version'      => 'v' . $this->R_VER,
             'url'          => 'http://mars.wiwi.hu-berlin.de/mediawiki/sk/index.php/R_Plugin_for_MediaWiki',
             'description'  => 'Generates a [[Special:R|Special Page]] for information used by the extension.');
     // setup ressources
     $this->jQueryUI = 
       array('scripts'       => 'modules/ext.R.jQueryUI.js',
             'styles'        => $this->JQ_CSS,
	     'dependencies'  => array('jquery.ui.slider', 'jquery.ui.dialog'),
	     'localBasePath' => __DIR__,
	     'remoteExtPath' => 'R'
	    );
     $this->jQueryVersion = 
       array('scripts'       => 'modules/ext.R.jQueryVersion.js',
             'dependencies'  => 'jquery.ui.slider',
	     'localBasePath' => __DIR__,
	     'remoteExtPath' => 'R'
	    );
     // setup engines
     try {
       $this->setupEngines($options);
       if (count($this->engine)==0) throw new Exception('ExtR.php: no computing engine found');
     }
     catch (exception $e) {
       $this->exception_log($e);
     }
     // setup files
     if (!file_exists(R_DIR . 'Rcgi.php')) system ("cp -f " . $this->R_EXT . 'Rcgi.php ' . R_DIR);
     // save instance
     file_put_contents ($filename, serialize($this));
     // take care of logfile
     unlink ($this->R_LOG);
     $this->extension_log('OS : ' . php_uname());
     $this->extension_log('MW : ' . $wgVersion);
     $this->extension_log('PHP: ' . phpversion());
     $lines = explode("\n", `mysql --version`);
     $this->extension_log('SQL: ' . $lines[0]);
     $lines = explode("\n", `convert --version`);
     $this->extension_log('IM : ' . $lines[0]);
     $this->extension_log('SH : ' . $this->getVersion('extensions/SyntaxHighlight_GeSHi/geshi/geshi.php', "/'GESHI_VERSION',\s*'(.*?)'/"));
     $this->extension_log('VER: ' . $this->creditExtension['version']);
     foreach ($this->engine as $eng) {
       $this->extension_log('ENG: ' . $eng->desc);
     }
   }
}

?>
