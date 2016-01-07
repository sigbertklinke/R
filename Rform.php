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

class Rform {

  static function renderRform($input, $params, $parser, $frame) {
    global $extr;
    try {
      if (!property_exists($parser, 'extR')) {
        $parser->extR = array('depth' => 1,
                              'id'    => array());
      } else {
        $parser->extR['depth'] = $parser->extR['depth']+1;
      }
      if ($parser->extR['depth']>1) throw new Exception ('Rform.php: Rform can not be nested');

      $name  = null;
      if (array_key_exists('label', $params)) $name  = $params['label'];
      if (array_key_exists('name', $params))  $name  = $params['name'];
      if (is_null($name)) throw new Exception('Rform.php: Attribute "name" in Rform required');
      $id     = Rform::getID($params, $parser, $name);
      $title  = (array_key_exists('title', $params) ? $params['title'] : 'Submit form "' . $name . '" ?');
      $submit = (array_key_exists('submit', $params) ? $params['submit'] : 'submit');

      $inp = new HTMLtag('input',
			  array('type'=> 'hidden', 
			        'name'=> 'R', 
				'value'=> rawurlencode($parser->getTitle() . '_' .$name)));
      $inp->check();

      $form = new HTMLtag('form',
			  array('id'          => $id,
                                'action'      => $extr->R_CGI,
				'method'      => 'post',
				'target'      => $name,
				'class'       => 'extrform',
                                'data-submit' => $submit));

      if (array_key_exists('debug', $params)) $form->attr('class', 'extrdebug');
      $form->body = (string) $inp;
      $parser->extR['form']      = $name;
      $parser->extR['idcount']   = 1;
      $parser->extR['usejquery'] = $extr->JQ_CSS && (array_key_exists('jquery', $params) ? $params['jquery'] : $extr->options['usejquery']);
      $parser->extR['submit']    = $submit;
      // $extr->JQ_CSS:                 is the use of jQuery possible at all?
      // $params['jquery']:             has the user forced the use or disuse of jQuery
      // $$extr->options['usejquery']): what has been the general setting for jQuery
      if ($parser->extR['usejquery']) {
	$parser->mOutput->addModules('ext.R.jQueryUI');	
	$form->attr('class', 'ui-widget');
	$form->attr('class', $extr->JQ_THEME);
	if (array_key_exists('debug', $params)) $form->attr('class', 'RextDebug');
	$dlg = new HTMLtag('div',
			   array('class' => 'extrdialog',
				 'data-form' => $name));
	$dlg->body = '<p class="ui-dialog-content">' . $title . '</p>';   
	$dlg->check();
	$form->body .= (string) $dlg;
      }

      $form->body .= $parser->recursiveTagParse($input, $frame); 

      $parser->extR['depth'] = 0;

      $form->check();

      $div = new HTMLtag('div');
      $div->attr('class', 'sunny');
      $div->body = (string) $form;
      $div->check();
    
      return ((string) $div);
    } catch (Exception $e) {
      $extr->exception_log($e);
      return ('<pre style="color:red">' .  htmlentities($e->getMessage()) . '</pre>in<pre>' . htmlentities(new HTMLtag('Rform', $params)) . '</pre>');
    } 
  }

  static function addjQueryClasses  ($params, $other='') {
    $class = '';
    if (array_key_exists('disabled', $params)) { 
      $class = 'ui-state-disabled';      
    } elseif (array_key_exists('readonly', $params)) {
      $class = 'ui-state-default ui-state-disabled';      
    } else {
      $class = 'ui-state-default';  
    }
    $class .= ' ui-corner-all ' . $other;
    return(trim($class));
  }
  
  static function addExtRAttributes ($params, $parser) {
    $attr = array('data-form' => $parser->extR['form']);
    if (array_key_exists('name', $params)) {
      if (preg_match('/^(.*)\_([0-9]+)$/', $params['name'], $match)) {
	$attr['data-name'] = $match[1];
	$attr['data-item'] = $match[2];
      } else {
	$attr['data-name'] = $params['name'];
      }
    }
    $attr['data-submit'] = (array_key_exists('submit', $params) ? $params['submit'] : $parser->extR['submit']);
    return($attr);
  }

  static function getID ($params, $parser, $givenid=NULL) {
    if (isset($givenid)) {
      $id = 'extr_' . $givenid;
      if (in_array($id,  $parser->extR['id'])) throw new Exception('Rform.php: Attribute "id" can not be used twice');
    } elseif(isset($params['id'])) {
      $id = $params['id'];
      if (in_array($id,  $parser->extR['id'])) throw new Exception('Rform.php: Attribute "id" can not be used twice');
    } else {
      if (isset($params['name'])) {
        $id = 'extr_' . $parser->extR['form'] . '_' . $params['name'];
      } else {
        $id = 'extr_' . $parser->extR['form'] . '_' . $parser->extR['idcount'];
        $parser->extR['idcount']++;
      }
      while (in_array($id,  $parser->extR['id'])) {
        $id = $parser->extR['form'] . '_' . $params['name'] . $parser->extR['idcount'];
        $parser->extR['idcount']++;
      }
    } 
    if (isset($id)) $parser->extR['id'][] = $id;
    return ($id);
  }

  static function renderArea ($input, $params, $parser, $frame) {
    if (!array_key_exists('name', $params)) throw new Exception('Rform.php: Attribute "name" is required');      

    $tag = new HTMLtag('textarea',
                       array('id'    => Rform::getID($params, $parser),
                             'class' => 'extrtextarea'));
    $tag->copy(HTMLtag::$attributes['html5'], $params);
    $tag->copy(HTMLtag::$attributes['area'], $params);

    if ($parser->extR['usejquery']) {
      $tag->attr(Rform::addExtRAttributes($params, $parser));
      $tag->attr('class', Rform::addJqueryClasses($params));
    }

    $tag->body = htmlentities($input);

    $tag->check();
    return((string) $tag);
  }

  static function renderText ($input, $params, $parser, $frame) {
    if (!array_key_exists('name', $params)) throw new Exception('Rform.php: Attribute "name" is required');      

    $tag = new HTMLtag('input',
                       array('id'    => Rform::getID($params, $parser),
                             'type'  => 'text',
                             'class' => 'extrtext'));
    $tag->copy(HTMLtag::$attributes['html5'], $params);
    $tag->copy(HTMLtag::$attributes['text'], $params);

    if ($parser->extR['usejquery']) {
      $tag->attr(Rform::addExtRAttributes($params, $parser));
      $tag->attr('class', Rform::addJqueryClasses($params));
    }

    $tag->check();

    return((string) $tag);
  }

  static function renderCheckbox ($input, $params, $parser, $frame) {
    if (!array_key_exists('name', $params)) throw new Exception('Rform.php: Attribute "name" is required');      

    $tag = new HTMLtag('input',
                       array('id'    => Rform::getID($params, $parser),
                             'type'  => 'checkbox',
                             'class' => 'extrcheckbox'));
    $tag->copy(HTMLtag::$attributes['html5'], $params);
    $tag->copy(HTMLtag::$attributes['checkbox'], $params);

    if ($parser->extR['usejquery']) {
      $tag->attr(Rform::addExtRAttributes($params, $parser));
      $tag->attr('class', Rform::addJqueryClasses($params));
    }

    $tag->check(); 
    return((string) $tag);
  }

  static function renderRadio ($input, $params, $parser, $frame) {
    if (!array_key_exists('name', $params)) throw new Exception('Rform.php: Attribute "name" is required');      

    $tag = new HTMLtag('input',
                       array('id'    => Rform::getID($params, $parser),
                             'type'  => 'radio',
                             'class' => 'extrradio'));
    $tag->copy(HTMLtag::$attributes['html5'], $params);
    $tag->copy(HTMLtag::$attributes['radio'], $params);

    if ($parser->extR['usejquery']) {
      $tag->attr(Rform::addExtRAttributes($params, $parser));
      $tag->attr('class', Rform::addJqueryClasses($params));
    }
 
    $tag->check();
    return((string) $tag);
  }

  static function renderSubmit ($input, $params, $parser, $frame) {
    $tag = new HTMLtag('input',
                       array('id'    => Rform::getID($params, $parser),
                             'type'  => 'submit',
                             'class' => 'extrsubmit'));
    $tag->copy(HTMLtag::$attributes['html5'], $params);
    $tag->copy(HTMLtag::$attributes['submit'], $params);

    if ($parser->extR['usejquery']) {
      $tag->attr(Rform::addExtRAttributes($params, $parser));
      $tag->attr('data-name', 'submit');
      if (array_key_exists('disabled', $params)) unset($params['disabled']);
      if (array_key_exists('readonly', $params)) unset($params['readonly']);
      $tag->attr('class', Rform::addJqueryClasses($params));
    }
    $tag->check();
    return((string) $tag);
  }

  static function renderReset ($input, $params, $parser, $frame) {
    $tag = new HTMLtag('input',
                       array('id'    => Rform::getID($params, $parser),
                             'type'  => 'reset',
                             'class' => 'extrreset'));
    $tag->copy(HTMLtag::$attributes['html5'], $params);
    $tag->copy(HTMLtag::$attributes['reset'], $params);

    if ($parser->extR['usejquery']) {
      $tag->attr(Rform::addExtRAttributes($params, $parser));
      $tag->attr('data-name', 'reset');
      if (array_key_exists('disabled', $params)) unset($params['disabled']);
      if (array_key_exists('readonly', $params)) unset($params['readonly']);
      $tag->attr('class', Rform::addJqueryClasses($params));
    }
    $tag->check();
    return((string) $tag);
  }

  public function copies ($keys, $arr, $data=false) {
    foreach ($keys as $key => $value) {
      if (is_numeric($key)) {
	$this->copy ($value, $arr, $data);
      } else {
	$this->copy ($key, $arr, $value);
      }
    }
  }

  static function renderSlider ($input, $params, $parser, $frame) {
    global $wgVersion;
    //    if (version_compare($wgVersion, '1.16')<0) throw new Exception ('Rform.php: type="slider" can only be used for MediaWiki 1.16 or later');
    if(!$parser->extR['usejquery']) throw new Exception ('Rform.php: type="slider" can only be used if jQuery is available and the MediaWiki version is 1.22.x or higher');
    if (!array_key_exists('name', $params)) throw new Exception ('Rform.php: Attribute "name" for slider is required');
    if (array_key_exists('value', $params) && array_key_exists('values', $params)) throw new Exception ('Rform.php: Attributes "value" and "values" for a slider can not be used at the same time');

    $nhandle = 1;
    if (array_key_exists('range', $params) && (strcasecmp($params['range'], 'true')==0)) { // more than one handle
      if (!array_key_exists('values', $params)) throw new Exception ('Rform.php: Attribute "values" for slider is required for several handles');
      $nhandle = count(explode(' ', trim($params['values'])));
    }
    
    $div = new HTMLtag('div',
                       array('id'    => Rform::getID($params, $parser),
                             'class' => 'extrslider'));
    $div->copy(HTMLtag::$attributes['html5'], $params);
    $div->copy(HTMLtag::$attributes['slider'], $params, true);

    if ($parser->extR['usejquery']) {
      $div->attr(Rform::addExtRAttributes($params, $parser));
      $div->attr('class', Rform::addJqueryClasses($params));
    }
    $div->check();

    $div->body = '';
    for ($i=0; $i<$nhandle; $i++) {
      $inp = new HTMLtag('input');
      $inp->attr(array('type'      => 'hidden',
		       'data-form' => $parser->extR['form'],
		       'data-name' => $params['name'],
		       'data-item' => (string) $i));;
      if ($nhandle==1) 
	$inp->attr('name', $params['name']);
      else 
	$inp->attr('name', $params['name'] . '_' . $i);
      $inp->check();

      $div->body .= (string) $inp;
    } 

    return((string) $div);
  }

  static function renderRinput ($input, $params, $parser, $frame) {
    global $extr;
    try {
      // type="text" size="Länge" maxlength="MaxLänge" name="Name"  value="Wert"
      if (!property_exists($parser, 'extR')) throw new Exception ('Rform.php: Rform is missing');

      $type = array_key_exists('type', $params) ? $params['type'] : 'text';
      switch ($type) {
      case 'area':
	return (Rform::renderArea($input, $params, $parser, $frame));
      case 'text':
	return (Rform::renderText($input, $params, $parser, $frame));
      case 'checkbox':
	return (Rform::renderCheckbox($input, $params, $parser, $frame));
      case 'radio':
	return (Rform::renderRadio($input, $params, $parser, $frame));
      case 'submit':
	return (Rform::renderSubmit($input, $params, $parser, $frame));
      case 'reset':
	return (Rform::renderReset($input, $params, $parser, $frame));
      case 'slider':
	return (Rform::renderSlider($input, $params, $parser, $frame));
      }
      throw new Exception ('Rform.php: \'Rinput\' invalid type attribute');
    }
    catch(Exception $e) {
      $extr->exception_log($e);
      return ('<pre style="color:red">' .  htmlentities($e->getMessage()) . '</pre>in<pre>' . htmlentities(new HTMLtag('Rinput', $params)) . '</pre>');
    }
  }

  // deprecated
  static function renderRarea ($input, $params, $parser, $frame) {
    global $extr;
    try {
      if (!property_exists($parser, 'extR')) throw new Exception ('Rform.php: Rform is missing');
      return(renderArea ($input, $params, $parser, $frame));
    }
    catch (Exception $e) {
      $extr->exception_log($e);
      return ('<pre style="color:red">' .  htmlentities($e->getMessage()) . '</pre>in<pre>' . htmlentities(new HTMLtag('Rarea', $params)) . '</pre>');
    }
  }

}
?>
