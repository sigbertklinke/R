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

class HTMLtag {

  static $attributes = 
    array('html5' => array ('accesskey', 
			    'class', 
			    // 'contenteditable', 
			    // 'contextmenu', 
			    'dir',
			    // 'draggable',
			    // 'dropzone',
			    // 'hidden',
			    // 'id',      // used by the extension 
			    'lang',
			    'spellcheck',
			    'style',
			    'tabindex',
			    'title'),
	  'form' => array('accept-charset', 
			  // 'action',    // used by the extension 
			  'autocomplete', 
			  // 'enctype',   // used by the extension 
			  // 'method',    // used by the extension 
			  // 'name',      // used by the extension 
			  'novalidate'
			  // 'target',    // used by the extension 
			  ),
	  'text' => array('autocomplete',
			  'autofocus',			  
			  'dirname',
			  // 'disabled',
			  // 'form',
			  // 'list',
			  'maxlength',
			  'name',
			  'pattern',
			  'placeholder',
			  'readonly',
			  'required',
			  'size',
			  'value'),
	  'area' => array('autofocus',
			  'cols',
			  // 'disabled',
			  'dirname',
			  // 'form',
			  'maxlength',
			  'name',
			  'placeholder',
			  'readonly',
			  'required',
			  'rows',
			  'wrap'),
	  'checkbox' => array('autofocus',
			      'checked',
			      // 'disabled',
			      // 'form',
			      'name',
			      'required',
			      'value'),
	  'radio' => array('autofocus',
			   'checked',
			   // 'disabled',
			   // 'form',
			   'name',
			   'required',
			   'value'),
	  'submit' => array('autofocus',
			    // 'disabled',
			    // 'form',
			    // 'formaction',
			    // 'formenctype',
			    // 'formmethod',
			    // 'formnovalidate',
			    // 'formtarget',
			    'name',
			    'value'),
	  'reset' => array('autofocus',
			    // 'disabled',
			    // 'form',
			    'name',
			    'value'),
	  'slider' => array('animate',
			    // 'disabled',
			    'max',
			    'min',
			    'name',
			    'orientation',
			    'range',
			    'step',
			    'value',
			    'values')
	  );

  public $tag;
  public $attr = array();
  public $body = null;

  public function __construct ($tag, $attr=null) {
    $this->tag = $tag;
    if (!is_null($attr)) $this->attr($attr); 
  }

  public function attr ($attr, $val=null) {
    if (is_null($val)) {
      foreach ($attr as $name => $value) 
	$this->attr($name, $value);
    }
    else {
      $lattr = strtolower($attr);
      switch($lattr) {
      case 'class':
	if (array_key_exists('class', $this->attr)) 
	  $this->attr['class'] .= ' ' .$val;
	else
	  $this->attr['class'] = $val;
	break;
      case 'style':
	if (array_key_exists('style', $this->attr)) 
	  $this->attr['style'] .= ' ' .$val;
	else
	  $this->attr['style'] = $val;
	break;
      default:
	$this->attr[$lattr] = $val;
      }
    }
  }

  public function copy ($keys, $arr, $data=false) {
    if (is_array($keys)) {
     foreach ($keys as $key) 
       $this->copy($key, $arr, $data);
    } elseif (array_key_exists($keys, $arr)) {
      $newkey = $keys;
      if ($data) { $newkey = 'data-' . $newkey; }
      $this->attr($newkey, $arr[$keys]);
    }
  }

  public function check() {
    foreach ($this->attr as $key => $value) {
      if (preg_match('/[^A-Za-z0-9\.\_\-\:]/', $key)) throw new Exception ('HTMLTag.php: Attribute name "' . htmlentities($key) . '" invalid');
      if (!is_null($value)) {
	if (preg_match('/[^A-Za-z0-9\ \%\.\_\-\:\;\/]/', $value)) throw new Exception ('HTMLTag.php: Attribute value "' . htmlentities($value) . '" invalid');
      }
    }
  }

  private function normalizeClass ($val) {
    return (trim(implode(' ', array_unique(explode(' ', $val)))));
  }

  private function normalizeStyle ($val) {
    $properties  = explode(';', $val);
    $propertyarr = [];
    $styleret    = '';        
    foreach ($properties as $property) {
      if (!empty($property)) {
        list($name, $value) = explode(':', $property, 2); 

        $lname = strtolower($name);
        if (!in_array($lname, $propertyarr)) {
	  $propertyarr[]= $lname;
	  if (!empty($styleret)) $styleret .= ';';
	  $styleret .= $property;
        } 
      }
    }
    return ($styleret);
  }

  public function __toString() {
    $ret = '<' . $this->tag;
    if (array_key_exists('class', $this->attr)) $this->attr['class'] = $this->normalizeClass($this->attr['class']);
    if (array_key_exists('style', $this->attr)) $this->attr['style'] = $this->normalizeStyle($this->attr['style']);
    foreach ($this->attr as $key => $value) {
      $ret .= ' ' . $key;
      if (!is_null($value)) {
	$ret .= '="' . $value . '"';
      }
    }
    if (is_null($this->body)) {
      $ret .= ' />';     
    } else {
      $ret .= ' >' . $this->body . '</' . $this->tag . '>'; 
    }
    return ($ret);
  }

}

?>
