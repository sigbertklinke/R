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
mw.hook ('wikipage.content').add(
  function ($content) {

// begin of casting
      function castAll (elem, arr) {
	  if (extR.debug) console.log('castAll: ');
          var i, val, key, opt = {};
	  for (key in arr) {
	      val = elem.attr('data-'+key);
	      if (val!=null) opt[key] = arr[key]($.trim(val));
	  }
	  return (opt);
      }

      function cast (val) {
	  if (extR.debug) console.log('cast: ' + val);
	  var cv = $.trim(val);
	  if (isNaN(cv)) { // val is not a number
	      if (cv.toLowerCase()=='true')  return (true);
	      if (cv.toLowerCase()=='false') return (false);
	      return(cv);
	  }
	  var vint = parseInt(val, 10);
	  var vdbl = parseFloat(val); 
	  if (vdbl==vint) return (vint); 
	  return (vdbl);
      }

      function castArray (val) {
	  if (extR.debug) console.log('castArray: ' + val);
	  var i, vals;
	  var arr = [];
	  vals = val.split(' ');
	  for (i=0; i<vals.length; i++) arr[i] = cast(vals[i]);
	  return (arr);
      }

// end of casting

// helper function

/*
      var defaultsubmit = ['submit', 'change'];

      function pmatch(arr, elem) {
        garr = jQuery.grep(arr, function(elemi, i) {
            return (elemi.indexOf(elem)==0);
          });
        if (garr.length==1) return(garr[0]);
        return;
      }
*/
      function hasValue (opt) {
	  return ((!('values' in opt)) || (opt.values==null));
      }

      function normalizeSliderValues(opt) {
	  if (hasValue(opt)) {
	      if (extR.debug) console.log('normalizeSliderValues: ' + opt.value);
	      if (opt.value<opt.min) opt.value = opt.min;
	      if (opt.value>opt.max) opt.value = opt.max;
	  } else {
	      if (extR.debug) console.log('normalizeSliderValues: ' + opt.values.toSource());
	      if (opt.values[0]<opt.min) opt.values[0] = opt.min;
	      for (var i=1; i<opt.values.length; i++) {
		  if (opt.values[i]<opt.values[i-1]) opt.values[i]=opt.values[i-1];
		  if (opt.values[i]>opt.max) opt.values[i]=opt.max;
	      }
	  }
      }

// end of helper function
      
      function updateChildren (event, ui) {
	  var form = $(this).attr('data-form');
	  var name = $(this).attr('data-name');
	  if (extR.debug) console.log('updateChildren: ' + name);
	  var vals = [];
	  if (hasValue(ui))
	      vals[0] = ui.value; 
	  else
	      vals = ui.values; 
	  onChangeSliderUpdateText(form, name, vals);
/* fires too much events ...
	  if ($(this).attr('data-submit')=='change') {
	      extR.submitbyclick = true;
	      $content.find('.extrform[target="' + form + '"]').submit();
	  }
*/
      }

      function onStopSlider (event, ui) {
	var form = $(this).attr('data-form');
	if (extR.debug) console.log('onstopSlider: ' + form);
        extR.submitbyclick = true;
	if (extR.debug) console.log('onstopSlider: ' + $(this).slider('option').toSource());
	$content.find('.extrform[target="' + form + '"]').submit();
      }

// init slider

      // if text field associated with a slider changes then change slider too
      function onChangeTextUpdateSlider () {
	  var form = $(this).attr('data-form');
	  var name = $(this).attr('data-name');
	  var item = $(this).attr('data-item');
	  if (extR.debug) console.log('onChangeTextUpdateSlider: ' + name);
	  var val  = $(this).val();

	  // make sure that entry is only numeric
	  val = val.replace(/[^0-9]+/g, '');
	  $(this).val(val);

	  // update slider
	  var i, elem, opt, elems = $content.find('.extrslider[data-form="' + form + '"][data-name="' + name + '"]');
	  for (i=0; i<elems.length; i++) {
	      elem = elems.eq(i);
	      opt  = elem.slider('option');
	      if (hasValue(opt)) {
		  opt.value = val;
		  normalizeSliderValues(opt);
		  elem.slider('option', 'value', opt.value);
	      } else {
		  if (item==null) item = 0;
		  if (item<opt.values.length) opt.values[item] = val;
		  normalizeSliderValues(opt);
		  elem.slider('option', 'values', opt.values);
	      }
	  }
      }

      function initSliderText (index, value) {
	  if (extR.debug) console.log('initSliderText: ' + $(this).attr('data-name'));
	  var type = $(this).attr('type');
	  var read = $(this).attr('readonly');
	  if ((type=='text') && (read==null)) {
	      $(this).on('change', onChangeTextUpdateSlider);
	      $(this).on('keyup',  onChangeTextUpdateSlider);
	  }
      }

      // if slider changes then update all text input fields 
      function onChangeSliderUpdateText (form, name, vals) {
	  if (extR.debug) console.log('onChangeSliderUpdateText: ' + name);
	  var i, j, elem;
	  var elems = $content.find('input[data-form="' + form + '"][data-name="' + name + '"]');
	  for (i=0; i<elems.length; i++) {
	      elem = elems.eq(i);
	      j = elem.attr('data-item');
	      if (typeof j == "undefined") j = 0;
	      elem.val(vals[j]);
	  }
      }

      function onChangeSlider (event, ui) {
	  var form = $(this).attr('data-form');
	  var name = $(this).attr('data-name');
	  if (extR.debug) console.log('updateChildren: ' + name);
	  var vals = [];
	  if (hasValue(ui))
	      vals[0] = ui.value; 
	  else
	      vals = ui.values; 
	  onChangeSliderUpdateText(form, name, vals);
/* fires too much events ...
	  if ($(this).attr('data-submit')=='change') {
	      extR.submitbyclick = true;
	      if (extR.debug) console.log('onChangeSlider: ' + $(this).slider('option').toSource());
	      $content.find('.extrform[target="' + form + '"]').submit();
	  }
*/ 
      }

      function initSlider(index, value) {
	  var form   = $(this).attr('data-form');
	  var name   = $(this).attr('data-name');
	  if (extR.debug) console.log('initSlider: ' + name);
	  var sliderattr = {
              animate    : cast,
	      min        : cast, 
              max        : cast,
              step       : cast,
              orientation: cast,
              range      : cast,
              value      : cast,
              values     : castArray};
	  // copy slider parameters
	  var opt = castAll($(this), sliderattr); 	  
	  // set default values
	  if (!('min' in opt)) opt.min = 0;
	  if (!('max' in opt)) opt.max = 100;
	  if ('range' in opt) { 
            opt.range = Boolean(opt.range); 
          } else { 
            opt.range = false; 
          }
	  normalizeSliderValues(opt);
	  // set functions
          // if ($(this).attr('data-submit')=='change') opt.stop = onStopSlider;  
	  // create slider
	  $(this).slider(opt);
	  // set functions
	  $(this).on('slide', onChangeSlider);
	  $(this).on('slidechange', onChangeSlider);
           if ($(this).attr('data-submit')=='change') $(this).on('mouseup', onStopSlider);  

	  // set up children functions
	  $content.find('input[data-form="' + form + '"][data-name="' + name + '"]').each(initSliderText);

	  // initialize values
	  var vals = [];
	  if (hasValue(opt)) 
	      vals[0] = opt.value;
	  else
	      vals = opt.values;
	  onChangeSliderUpdateText(form, name, vals);
      }

// init reset buttons
      function resetCheckbox (index, value) {
	if (extR.debug) console.log('resetCheckbox');
        $(this).attr('checked', this.getAttribute('checked'));
      }

      function resetRadio (index, value) {
	if (extR.debug) console.log('resetRadio');
        $(this).attr('checked', this.getAttribute('checked'));
      }

      function resetText (index, value) {
	if (extR.debug) console.log('resetText');
        $(this).val(this.getAttribute('value'));
      }

      function resetTextArea (index, value) {
	if (extR.debug) console.log('resetTextArea');
        $(this).val(this.getAttribute('value'));
      }

      function resetSlider (index, value) {
	  if (extR.debug) console.log('resetSlider');
          var opt = $(this).slider('option');
	  if (hasValue(opt)) {
            opt.value = cast(this.getAttribute('data-value'));
            normalizeSliderValues(opt);
            $(this).slider('option', 'value', opt.value);
	  } else {
	    opt.values = castArray(this.getAttribute('data-values'));
            normalizeSliderValues(opt);
            $(this).slider('option', 'values', opt.values);
	  }
      }

      function onClickReset (event) {
          event.preventDefault();

 	  var elams, form, name = $(this).attr('data-form');
	  if (extR.debug) console.log('onClickReset: ' + name);
          var form = $content.find('.extrform[target="' + name + '"]');

          // reset submit button
	  // reset reset button
          // on buttons nothing to do...

	  // reset checkbox
	  elems = form.find('.extrcheckbox');
	  elems.each(resetCheckbox);

	  // reset radio
	  elems = form.find('.extrradio');
	  elems.each(resetRadio);

	  // reset input 
	  elems = form.find('.extrtext');
	  elems.each(resetText);

	  // reset textarea - nothing to do
	  elems = form.find('.extrarea');
	  elems.each(resetTextArea);

	  // reset sliders
	  elems = form.find('.extrslider');
	  elems.each(resetSlider);

	  // get form submit option
	  if ($(this).attr('data-submit')=='change') {
	    extR.submitbyclick = true;
            if (extR.debug) console.log('onClickReset');
            form.submit();
          }

          return(false);
      }

      function initReset (index, value) {
	  if (extR.debug) console.log('initReset: ' + $(this).attr('data-form'));
	  $(this).on('click', onClickReset);
      }

// handle form submission
      var dialog  = [];

      function onSubmitForm (event) {
	  if (extR.debug) console.log('onSubmitForm: ' + extR.submitbyclick);
	  if (extR.submitbyclick) {
	      extR.submitbyclick = false;
	      return (true);
	  }
	  var form  = $(this).closest('form').attr('target');
	  dialog[form].dialog('open');
	  return (false);
      }

      function initDialog (index, value) {
	  if (extR.debug) console.log('initDialog');
	  var form  = $(this).attr('data-form');

	  // set up dialog
	  var ok    = 'Submit';
	  var elems = $content.find('input[type="submit"][data-form="' + form + '"]');
	  if (elems.length>0) ok = elems.eq(0).attr('value');
	  var buttons = {};
	  buttons[ok] =  function() {
	      var form = $(this).attr('data-form');
	      $(this).dialog('close'); 
	      extR.submitbyclick = true;
   	      if (extR.debug) console.log('dialog ok');
	      $content.find('.extrform[target="' + form + '"]').submit();
	  }
	  buttons['Cancel'] = function() {
	      $(this).dialog('close'); 
	      extR.submitbyclick = false;
	  }
	  var opt     = {
	      autoOpen: false, 
	      modal: true,
	      buttons: buttons,
//	      dialogClass: 'extrtheme',
	      open: function () {
		  // hide titlebar
		  $(this).closest('.ui-dialog').find('.ui-dialog-titlebar:first').hide();
//		  $(this).text($(this).dialog('option', 'title'));
              }
	  };
	  // create dialog
	  dialog[form] = $(this).dialog(opt);
	  dialog[form].closest('.ui-dialog').addClass('sunny');
	  // take titlebar text and make it the dialog text
//	  dialog[form].text(dialog[form].dialog('option', 'title'));
      }

// init submit
      function onMouseUpSubmit (event) {
	  extR.submitbyclick = true;
      }

      function initSubmit (index, value) {
	  if (extR.debug) console.log('initSubmit: ' + $(this).attr('data-form'));
	  $(this).on('mouseup', onMouseUpSubmit);
      }

// init checkbox 
      function onChangeCheckbox () {
	  var form = $(this).attr('data-form');
	  extR.submitbyclick = true;
	  if (extR.debug) console.log('onChangeCheckbox');
          $content.find('.extrform[target="' + form + '"]').submit();
      }

      function initCheckbox (index, value) {
	  if (extR.debug) console.log('initCheckbox: ' + $(this).attr('data-form'));
	  if ($(this).attr('data-submit') ? ($(this).attr('data-submit')=='change') : extR.formsubmit) {
	    $(this).on('change', onChangeCheckbox);
	  }
      }

// init radio
      function onChangeRadio () {
	  var form = $(this).attr('data-form');
	  extR.submitbyclick = true;
	  if (extR.debug) console.log('onChangeRadio');
          $content.find('.extrform[target="' + form + '"]').submit();
      }

      function initRadio (index, value) {
	  if (extR.debug) console.log('initRadio: ' + $(this).attr('data-form'));
	  if ($(this).attr('data-submit') ? ($(this).attr('data-submit')=='change') : extR.formsubmit) {
	    $(this).on('change', onChangeRadio);
	  }
      }

// init text
      function onChangeText () {
	  var form = $(this).attr('data-form');
	  extR.submitbyclick = true;
	  if (extR.debug) console.log('onChangeText');
          $content.find('.extrform[target="' + form + '"]').submit();
      }

      function onKeypressEnter (event) {
	  code = event.keyCode || event.which;
          if (code==13) {
 	    var form = $(this).attr('data-form');
	    extR.submitbyclick = true;
   	    if (extR.debug) console.log('onKeypressEnter');
            $content.find('.extrform[target="' + form + '"]').submit();
	  }
	  return (false);
      }

      function initText (index, value) {
	  if (extR.debug) console.log('initText: ' + $(this).attr('data-form'));
          
	  if ((typeof($(this).attr('readonly'))=='undefined') &&  ($(this).attr('data-submit')=='change')) $(this).on('change', onChangeText);
	  if ($(this).attr('data-submit') ? ($(this).attr('data-submit')=='change') : extR.formsubmit) {
	    $(this).on("keypress", onKeypressEnter);
	  }
      }

// set up one form 
      function handleForm (index, value) {
	  var form = $(this).attr('target');
	  if (extR.debug) console.log('handleForm: ' + form);
	  var i, name, elems;

	  // get form submit option
	  extR.formsubmit = ($(this).attr('data-submit')=='change');
	  $(this).on('submit', onSubmitForm);

          // set up submit button
	  elems  = $(this).find('.extrsubmit');
	  elems.each(initSubmit);

	  // set up reset button
	  elems  = $(this).find('.extrreset');
	  elems.each(initReset);

	  // set up checkbox
	  elems  = $(this).find('.extrcheckbox');
	  elems.each(initCheckbox);

	  // set up radio
	  elems  = $(this).find('.extrradio');
	  elems.each(initRadio);

	  // set up input 
	  elems  = $(this).find('.extrtext');
	  elems.each(initText);

	  // set up textarea - nothing to do

	  // set up sliders
	  elems  = $(this).find('.extrslider');
	  elems.each(initSlider);

	  // set up dialogs
	  elems  = $(this).find('.extrdialog');
	  elems.each(initDialog);
      }

// global variable
      var extR  = { 
	  formsubmit    : false,
	  submitbyclick : false,
          debug         : ($content.find('.extrdebug').length>0),
	  
      };
      console.log(extR.toSource());
// set up all forms
      $content.find('.extrform').each(handleForm);
      return(1);
  });
