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

// R_DIR: getcwd() must point to the root directory of the wiki  
defined('R_DIR') || define('R_DIR', getcwd() . DIRECTORY_SEPARATOR . 'Rfiles' . DIRECTORY_SEPARATOR);
defined('R_OBJ') || define('R_OBJ', R_DIR . 'ExtR.dat');

// autoloading the extension classes
function Rextension_autoloader($class) {
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'ExtR.php');
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'Convert.php');
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'HTMLtag.php');
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'Engine.php');
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'REngine.php');
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'MEngine.php');
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'Rform.php');
  require_once (dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'SpecialR.php');
}
spl_autoload_register('Rextension_autoloader');
$wgExtensionMessagesFiles['R'] = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'R.i18n.php';

// setup options
$wgROptions = array();
$wgROptions['errlog']          = '/var/log/apache2/error.log';
$wgROptions['maxloglines']     = 100;
$wgROptions['maxprograms']     = 10;
$wgROptions['usejquery']       = true;
$wgROptions['uitheme']         = 'sunny';
$wgROptions['usegeshi']        = array_key_exists('wgSyntaxHighlightDefaultLang', $GLOBALS);    
// if SyntaxHighlight_Geshi is installed and loaded then the global variable
// $wgSyntaxHighlightDefaultLang should exist (maybe set to null)

// cmd:       command to call, with full path (Required)
// class:     REngine or MEngine (Required)
// tag:       tag to use in MediaWiki
// engine:    name of the engine
// category:  category page to link to
//            not setting gives the std category page (according to 'class')
//            empty value gives no category page
// forbidden: list of forbidden commands

// default
/* 
  $wgROptions['engines'] = array (
    array('cmd'    => 'R',      'forbidden' => 'FC_RAL',
          'tag'    => 'R',      'category'  => 'R',
          'engine' => 'R',      'class'     => 'REngine'),
    array('cmd'    => 'octave', 'forbidden' => 'FC_OAL',
          'tag'    => 'M',      'category'  => 'M',
          'engine' => 'M',      'class'     => 'MEngine')
  );
*/

// example using two R engines: first system default, second locally installed
/*
  $wgROptions['engines'] = array ( 
    array('cmd'    => 'R',      'forbidden' => 'FC_RAL',
          'tag'    => 'R',      'category'  => 'R',
          'engine' => 'R',      'class'     => 'REngine'),
    array('cmd'    => '/var/www/mediawiki/extension/R/R-2.15.3/bin/R', 
                                'forbidden' => 'FC_RAL',
          'tag'    => 'R',      'category'  => 'R2',
          'engine' => 'R2153',  'class'     => 'REngine')   
  );
*/

$extr = ExtR::getInstance(R_OBJ, $wgROptions);

if (defined('MEDIAWIKI')) { // set up all MW stuff
  $wgAvailableRights[] = 'see-specialr';
  $wgGroupPermissions['sysop']['see-specialr'] = true;

  $wgExtensionCredits['parserhook'][]  = $extr->creditExtension;
  $wgExtensionCredits['specialpage'][] = $extr->creditSpecialPage;
  $wgHooks['ParserFirstCallInit'][]    = 'ExtR::wfRParse';
  $wgSpecialPageGroups['R']            = 'other'; 
  $wgSpecialPages['R']                 = 'SpecialR';
  if ($extr->JQ_CSS) { // jquery is available
   $wgResourceModules['ext.R.jQueryUI']      = $extr->jQueryUI;
   $wgResourceModules['ext.R.jQueryVersion'] = $extr->jQueryVersion;
  }
}

?>
