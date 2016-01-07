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

class SpecialR extends SpecialPage {
  function __construct() {
    parent::__construct( 'R' );
  }

  function extractFromExtLogfile () {
    global $extr;

    if (file_exists($extr->R_LOG)) {
      $cmd = 'tail -n ' . $extr->options['maxloglines'] . ' ' . $extr->R_LOG;
      $lines = array();
      exec ($cmd, $lines);
      $line = implode(array_reverse($lines), "\n");
      return ('<pre>' . $line . '</pre>');
    }
    return ('File not found or not accessible: <tt>'. $extr->R_LOG . '</tt>');
  }
 
  function extractFromWebLogfile () {
    global $extr;

    if (file_exists($extr->options['errlog'])) {
      $cmd = 'tail -n ' . $extr->options['maxloglines'] . ' ' . $extr->options['errlog'];
      $lines = array();
      exec ($cmd, $lines);
      foreach ($lines as &$line) {
	if ((strpos($line, '/extensions/R/')===false) &&
            (strpos($line, '/Rcgi.php')===false)) {
          $line = '';
        }
      }
      $line = implode(array_reverse(array_filter($lines)), "\n");
      return ('<pre>' . $line . '</pre>');
    }
    return ('File not found or not accessible: <tt>'. $extr->options['errlog'] . '</tt>');
  }

  function rglob ($dir, $pattern, $flags=0) {
    $files = glob ($dir . $pattern, $flags); 
     foreach (glob($dir . '*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
       $files = array_merge($files, $this->rglob($dir . '/', $pattern, $flags));
    }
    return ($files);
  }

  function lastRfiles () {
    global $wgOut, $extr;
    $exts   = array ('.R' => 'rsplus', 
                     '.m' => 'matlab');
    $files  = $this->rglob(R_DIR, '*.html'); 
    $mtimes = array();
    foreach ($files as $file) {
      $mtimes[$file] = filemtime($file);
    }
    arsort($mtimes);
    $wgOut->addHTML('<ul>');
    $cnt = $extr->options['maxprograms'];
    foreach ($mtimes as $file => $mtime) {
      $fnp = pathinfo($file);
      $wgOut->addHTML('<li><b><tt>' . $fnp['filename'] . '</tt></b>');
      foreach ($exts as $ext => $lang) {
        $cfn = $fnp['dirname'] . '/' . $fnp['filename'] . $ext;
        if (file_exists($cfn)) {
          if ($extr->options['usegeshi']) {
  	    $wiki = '<syntaxhighlight lang="' . $lang . '" enclose line>'; 
            $wiki .= file_get_contents($cfn);
            $wiki .= '</syntaxhighlight>';
            $wgOut->addWikiText($wiki);
          } else {
  	    $wgOut->addHTML('<pre>');
	    $wgOut->addHTML(htmlspecialchars(file_get_contents($cfn)));
	    $wgOut->addHTML('</pre>');
          }
          $wgOut->addHTML('<p>' . file_get_contents($file) . '</p></li>');
	}
      }
      $cnt = $cnt-1;
      if ($cnt<0) break;
    }
    $wgOut->addHTML('</ul>');
  }

  function execute( $par ) {
    global $wgOut, $wgServer, $wgScriptPath, $wgUser, $wgArticlePath, $extr;

    $this->setHeaders();
    
    $wgOut->addHTML('<h2>Installed engines</h2>');
    $wgOut->addHTML('<dl>');
    foreach ($extr->engine as $key => $value) {
      $wgOut->addHTML('<dt>' . $value->name . '</dt>');
      $wgOut->addHTML('<dd><table class="wikitable zebra">');
      $wgOut->addHTML('<tr><td>Description</td><td><tt>' . $value->desc . '</tt></td></tr>');
      $wgOut->addHTML('<tr><td>Category</td><td><tt>' . $value->category . '</tt></td></tr>');
      if ($wgUser->isAllowed('see-specialr')) {
        $wgOut->addHTML('<tr><td>Call</td><td><tt>' . $value->cmd . '</tt></td></tr>');
        $wgOut->addHTML('<tr><td>Security level</td><td><tt>' . $value->security . '</tt></td></tr>');
        $wgOut->addHTML('<tr><td><tt>sudo</tt> call</td><td><tt>' . $value->sudo . '</tt></td></tr>');
        $wgOut->addHTML('<tr><td>Graphic conversion call</td><td><tt>' . $value->convert->cmd  . '</tt></td></tr>');
        $wgOut->addHTML('<tr><td>Graphic conversion</td><td><tt>' . $value->convert->gin  . '</tt> -&gt; <tt>' . $value->convert->gout . '</tt></td></tr>');
        $wgOut->addHTML('<tr><td>Forbidden commands</td><td>');
	foreach ($value->banned as $fctxt => $fcregex) {
	  $wgOut->addHTML('<tt>' . $fctxt . '</tt>, ');
	}
        $wgOut->addHTML('</td><td>');
      }
      $wgOut->addHTML('</table></dd>');
    }
    $wgOut->addHTML('</dl>');
    $wgOut->addHTML('<h2>Further informations</h2>');
    if ($wgUser->isAllowed('see-specialr')) {
      $wgOut->addHTML('<h3>General</h3>');
      $wgOut->addHTML('<table class="wikitable zebra">');
      $wgOut->addHTML('<tr><td><a href="http://www.mediawiki.org/wiki/Extension:R">R extension</a> (installed)</td><td><tt>' . $extr->R_VER . '</tt></a></td></tr>');
      $wgOut->addHTML('<tr><td>R extension (<a href="http://mars.wiwi.hu-berlin.de/mediawiki/sk/index.php/R_Extension_for_MediaWiki">download</a>)</td><td><tt>' . file_get_contents('http://mars.wiwi.hu-berlin.de/mediawiki/sk/extensions/R/ExtR.ver') . '</tt></a></td></tr>');
      $wgOut->addHTML('<tr><td><a href="http://jquery.com">jQuery</a> / <a href="http://jqueryui.com">jQuery UI</a></td><td>');
      $wgOut->addModules('ext.R.jQueryVersion');
      $wgOut->addHTML('<span id="jQueryVersion">--</span> / <span id="jQueryUIVersion">--</span>'); 
      $wgOut->addHTML('</td></tr>');; 
      $wgOut->addHTML('<tr><td>Temporary directory</td><td><a href="' . $extr->R_URL . '"><tt>' . R_DIR . '</tt></a></td></tr>');
      $wgOut->addHTML('<tr><td>Extension directory</td><td><a href="' . $wgServer . $wgScriptPath . DIRECTORY_SEPARATOR . 'extensions/R"><tt>' . $extr->R_EXT . '</tt></td></tr>');
      $wgOut->addHTML('<tr><td>CGI script</td><td><tt>'. $extr->R_CGI . '</tt></td></tr>');
      $wgOut->addHTML('<tr><td>Hash method</td><td><tt>' . $extr->R_HASHMTD . '</tt></td></tr>');
      $wgOut->addHTML('<tr><td>Start of program</td><td><tt>' . htmlentities($extr->R_SOP) . '</tt></td></tr>');
      $wgOut->addHTML('<tr><td>End of program</td><td><tt>' . htmlentities($extr->R_EOP) . '</tt></td></tr>');
      $wgOut->addHTML('</table>');
      $wgOut->addModules('ext.R.jQueryVersion');

      $wgOut->addHTML('<h3>Options</h3>');
      $wgOut->addHTML('<table class="wikitable zebra">');
      foreach ($extr->options as $key => $val) {
	$wgOut->addHTML('<tr><td>');
	$wgOut->addHTML(htmlentities($key));
	$wgOut->addHTML('</td><td>');
	$wgOut->addHTML(htmlentities($val));
	$wgOut->addHTML('</td></tr>');

      }
      $wgOut->addHTML('</table>');

      $wgOut->addHTML('<h3>Extension log</h3>');
      $wgOut->addHTML($this->extractFromExtLogfile());

      $wgOut->addHTML('<h3>Webserver log</h3>');
      $wgOut->addHTML($this->extractFromWebLogfile());

      $wgOut->addHTML('<h3>Last programs</h3>');
      $this->lastRfiles();
    }
    $wgOut->addHTML('<h3>Alphabetical list of named programs</h3>');
    $files = glob(R_DIR . '*.param');
    $wgOut->addHTML('<ul>');
    foreach ($files as $fn) {
      $fnb = basename($fn, '.param');
      $fnl = explode('_', $fnb);
      
      $wgOut->addHTML('<li><tt><a href="' . str_replace('$1', rawurldecode($fnl[0]), $wgArticlePath) . '">' . rawurldecode($fnb) . '</a></tt></li>');
    }
    $wgOut->addHTML('</ul>');

    return true;
  }
}

?>
