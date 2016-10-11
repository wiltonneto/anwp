var cfsForms = [];
// Make some fields types adaption for HTML5 input types support
var g_cfsFieldsAdapt = {
	date: function( $input ) {
		this._initDatePicker($input, {dateFormat: 'mm/dd/yy'});
	}
,	month: function( $input ) {
		this._initDatePicker($input, {dateFormat: 'MM, yy'});
	}
,	week: function( $input ) {
		this._initDatePicker($input, {
			dateFormat: 'mm/dd/yy'
		,	showWeek: true
		,	onSelect: function(dateText, inst) {
				var date = new Date(dateText);
				jQuery(this).val("Week " + jQuery.datepicker.iso8601Week( date )+ ', '+ date.getFullYear());
			}
		});
	}
,	time: function( $input ) {
		$input.timepicker();
	}
,	_initDatePicker: function( $input, params ) {
		params = params || {};
		$input.datepicker( params );
	}
};
function cfsForm(params) {
	params = params || {};
	this._data = params;
	this._$ = null;
	this.init();
}
cfsForm.prototype.init = function() {
	// Init base $shell object
	this.getShell();
	// Make HTML5 input types support
	this._bindHtml5Support();
	// Check custom error messages from form settings
	this._bindCustomErrorMsgs();
	// Make basic form preparations
	this._bindLove();
	this._checkUpdateRecaptcha();
	this._bindSubmit();
	// Remember that we showed this form
	this._setActionDone('show');
};
cfsForm.prototype.getHtmlViewId = function() {
	return this._data.view_html_id;
};
cfsForm.prototype.getFieldsByType = function( htmlType ) {
	var res = [];
	if(this._data.params.fields) {
		for(var i = 0; i < this._data.params.fields.length; i++) {
			if(this._data.params.fields[ i ].html == htmlType) {
				res.push( this._data.params.fields[ i ] );
			}
		}
	}
	return res && res.length ? res : false;
};
cfsForm.prototype._checkUpdateRecaptcha = function() {
	var reCaptchFields = this.getFieldsByType('recaptcha');
	if(reCaptchFields && reCaptchFields.length) {	// if reCapthca exists
		this._tryUpdateRecaptcha();
	}
};
cfsForm.prototype._tryUpdateRecaptcha = function() {
	//console.log('check!', this._$.find('.g-recaptcha'));
	cfsInitCaptcha( this._$.find('.g-recaptcha') );
	/*var $reCaptcha = this._$.find('.g-recaptcha');
	if($reCaptcha && $reCaptcha.size()) {
		$reCaptcha.each(function(){
			if(!jQuery(this).find('iframe').size()) {
				grecaptcha.reset();	// Reset them all on page - and just stop
				return false;
			}
		});
	}*/
};
cfsForm.prototype._bindHtml5Support = function() {
	var checkTypes = ['date', 'month', 'week', 'time'];
	for(var i = 0; i < checkTypes.length; i++) {
		var key = checkTypes[ i ];
		if(typeof(key) === 'string' && !ModernizrCfs.inputtypes[ key ]) {
			var $inputs = this._$.find('[type="'+ key+ '"]');
			if($inputs && $inputs.size()) {
				g_cfsFieldsAdapt[ key ]( $inputs );
			}
		}
	}
};
cfsForm.prototype._bindCustomErrorMsgs = function() {
	var invalidError = this._data.params.tpl.field_error_invalid;
	if(invalidError && invalidError != '' && this._data.params.fields) {
		var self = this;
		for(var i = 0; i < this._data.params.fields.length; i++) {
			if(parseInt(this._data.params.fields[ i ].mandatory)) {
				var $field = this.getFieldHtml( this._data.params.fields[ i ].name );
				if($field 
					&& $field.get(0) 
					&& $field.get(0).validity	// check HTML5 validation methods existing
					&& $field.get(0).setCustomValidity
				) {
					var label = this._data.params.fields[ i ].label 
						? this._data.params.fields[ i ].label 
						: this._data.params.fields[ i ].placeholder
					,	msg = cfs_str_replace(invalidError, '[label]', label);
					$field.data('cfs-invalid-msg', msg);
					$field.get(0).oninvalid = function() {
						self._setFieldInvalidMsg( this );
					};
					$field.change(function(){
						this.setCustomValidity('');	// Clear validation error, if it need - it will be set in "oninvalid" clb
					});
				}
			}
		}
	}
};
cfsForm.prototype._setFieldInvalidMsg = function( fieldHtml ) {
	fieldHtml.setCustomValidity( jQuery(fieldHtml).data('cfs-invalid-msg') );
};
cfsForm.prototype.getFieldHtml = function( name ) {
	var $field = this._$.find('[name="fields['+ name+ ']"]');
	return $field && $field.size() ? $field : false;
};
cfsForm.prototype._bindLove = function() {
	if(parseInt(toeOptionCfs('add_love_link'))) {
		this._$.append( toeOptionCfs('love_link_html') );
	}
};
cfsForm.prototype._addStat = function( action, isUnique ) {
	jQuery.sendFormCfs({
		msgElID: 'noMessages'
	,	data: {mod: 'statistics', action: 'add', id: this._data.id, type: action, is_unique: isUnique, 'connect_hash': this._data.connect_hash}
	});
};
cfsForm.prototype.getShell = function( checkExists ) {
	if(!this._$ || (checkExists && !this._$.size())) {
		this._$ = jQuery('#'+ this._data.view_html_id);
	}
	return this._$;
};
cfsForm.prototype.getStyle = function() {
	if(!this._$style) {
		this._$style = jQuery('#'+ this._data.view_html_id+ '_style');
	}
	return this._$style;
};
cfsForm.prototype._bindSubmit = function() {
	var self = this;
	this._$.find('.csfForm:not(.cfsSubmitBinded)').submit(function(){
		var $submitBtn = jQuery(this).find('input[type=submit]')
		,	$form = jQuery(this)
		,	$msgEl = jQuery(this).find('.cfsContactMsg');
		$submitBtn.attr('disabled', 'disabled');
		self._setActionDone('submit', true);
		jQuery(this).sendFormCfs({
			msgElID: $msgEl
		,	appendData: {url: window.location.href}
		,	onSuccess: function(res){
				$form.find('input[type=submit]').removeAttr('disabled');
				if(!res.error) {
					var $inPopup = $form.parents('.ppsPopupShell:first')
					,	afterRemoveClb = false;
					// If form is in PopUp - let's relocate it correctly after form html will be removed
					// so PopUp will be still in the center of the screen
					if($inPopup && $inPopup.size()) {
						afterRemoveClb = function() {
							if(typeof(ppsGetPopupByViewId) === 'function') {
								_ppsPositionPopup({
									popup: ppsGetPopupByViewId( $inPopup.data('view-id') )
								});
							}
						};
					}
					self._setActionDone('submit_success', true);
					var $parentShell = jQuery($form).parents('.cfsFormShell');
					$msgEl.appendTo( $parentShell );
					var docScrollTop = jQuery('html,body').scrollTop()
					,	formShellTop = self._$.offset().top;
					if(docScrollTop > formShellTop) {	// If message will appear outside of user vision - let's scroll to it
						var scrollTo = formShellTop - $form.scrollTop() - 30;
						jQuery('html,body').animate({
							scrollTop: scrollTo
						}, g_cfsAnimationSpeed);
					}
					$form.animateRemoveCfs( g_cfsAnimationSpeed, afterRemoveClb );
					
					if(res.data.redirect) {
						toeRedirect(res.data.redirect, parseInt(self._data.params.tpl.redirect_on_submit_new_wnd));
					}
				} else {
					self._setActionDone('submit_error', true);
				}
			}
		});
		return false;
	}).addClass('cfsSubmitBinded');
};
cfsForm.prototype._setActionDone = function( action, onlyClientSide ) {
	var actionsKey = 'cfs_actions_'+ this._data.id
	,	actions = getCookieCfs( actionsKey );
	if(!actions)
		actions = {};
	actions[ action ] = 1;
	var saveCookieTime = 30;
	saveCookieTime = isNaN(saveCookieTime) ? 30 : saveCookieTime;
	if(!saveCookieTime)
		saveCookieTime = null;	// Save for current session only
	setCookieCfs(actionsKey, actions, saveCookieTime);
	if(!onlyClientSide) {
		this._addStat( action );
	}
	jQuery(document).trigger('cfsAfterFormsActionDone', this);
};
cfsForm.prototype.getId = function() {
	return this._data ? this._data.id : false;
};
// Form printing methods - maybe we will add this in future to print forms
cfsForm.prototype.printForm = function() {
	var title = 'Form Content';
	var printWnd = window.open('', title, 'height=400,width=600');
	printWnd.document.write('<html><head><title>'+ title+ '</title>');
	printWnd.document.write('</head><body >');
	printWnd.document.write( this.extractFormData() );
	printWnd.document.write('</body></html>');

	printWnd.document.close(); // necessary for IE >= 10
	printWnd.focus(); // necessary for IE >= 10

	printWnd.print();
	printWnd.close();
};
cfsForm.prototype.extractFormData = function() {
	var $chatBlock = this._$.find('.cfsForm').clone()
	,	$style = this.getStyle().clone()
	,	remove = ['.cfsInputShell', '.cfsFormFooter', '.cfsMessagesExShell', '.cfsOptBtnsShell'];
	for(var i = 0; i < remove.length; i++) {
		$chatBlock.find( remove[ i ] ).remove();
	}
	return jQuery('<div />').append( jQuery('<div id="'+ this._data.tpl.view_html_id+ '" />').append( $chatBlock ).append( $style ) ).html();
};
cfsForm.prototype.refresh = function() {
	this.getShell( true );
	this._bindSubmit();
	this._checkUpdateRecaptcha();
};
// End of form printing methods
var g_cfsForms = {
	_list: []
,	add: function(params) {
		this._list.push( new cfsForm(params) );
	}
,	getById: function( id ) {
		if(this._list && this._list.length) {
			for(var i = 0; i < this._list.length; i++) {
				if(this._list[ i ].getId() == id) {
					return this._list[ i ];
				}
			}
		}
		return false;
	}
,	getByViewHtmlId: function( viewHtmlId ) {
		if(this._list && this._list.length) {
			for(var i = 0; i < this._list.length; i++) {
				if(this._list[ i ].getHtmlViewId() == viewHtmlId) {
					return this._list[ i ];
				}
			}
		}
		return false;
	}
,	getFormDataByViewHtmlId: function( viewHtmlId ) {
		if(typeof(cfsForms) !== 'undefined' && cfsForms && cfsForms.length) {
			for(var i = 0; i < cfsForms.length; i++) {
				if(cfsForms[ i ].view_html_id == viewHtmlId) {
					return cfsForms[ i ];
				}
			}
		}
		return false;
	}
};
jQuery(document).ready(function(){
	if(typeof(cfsFormsRenderFormIter) !== 'undefined') {
		for(var i = 0; i <= cfsFormsRenderFormIter.lastIter; i++) {
			if(typeof(window['cfsForms_'+ i]) !== 'undefined') {
				cfsForms.push( window['cfsForms_'+ i] );
			}
		}
	}
	if(typeof(cfsForms) !== 'undefined' && cfsForms && cfsForms.length) {
		jQuery(document).trigger('cfsBeforeFormsInit', cfsForms);
		for(var i = 0; i < cfsForms.length; i++) {
			g_cfsForms.add( cfsForms[ i ] );
		}
		jQuery(document).trigger('cfsAfterFormsInit', cfsForms);
	}
});