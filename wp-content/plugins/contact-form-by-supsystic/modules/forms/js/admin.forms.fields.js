var g_cfsFieldsFrame = {
	_$addWnd: null
,	_$editWnd: null
,	_$htmlEditorWnd: null
,	_htmlEditorId: 'cfs_html_field_editor'
,	_$googleMapsWnd: null
,	_googleMapsId: '#cfsFieldGoogleMapsSel'
,	_$editFieldShell: null
,	_$mainShell: null
,	_$addFieldNextTo: null
,	_addFieldNextToPos: ''
,	_$listOptsShell: null
,	_sortInProgress: false
,	_$patternInput: null
,	_fields: {
		name: {html: 'text', mandatory: 1}
	,	label: {html: 'text', mandatory: 1}
	,	placeholder: {html: 'text', mandatory: 1}
	,	value: {html: 'text'}
	,	value_preset: {html: 'selectbox'}
	,	html: {html: 'text'}	// TODO: Will be selectbox for PRO version
	,	def_checked: {html: 'checkbox'}
	,	mandatory: {html: 'checkbox'}
	,	display: {html: 'selectbox'}
	
	,	min_size: {html: 'text'}
	,	max_size: {html: 'text'}
	,	add_classes: {html: 'text'}
	,	add_styles: {html: 'text'}
	,	add_attr: {html: 'text'}
	
	,	vn_only_number: {html: 'checkbox'}
	,	vn_only_letters: {html: 'checkbox'}
	,	vn_pattern: {html: 'text'}
	}
,	_recapFields: {
		'recap-sitekey': {html: 'text', mandatory: 1}
	,	'recap-secret': {html: 'text', mandatory: 1}
	,	'recap-theme': {html: 'selectbox'}
	,	'recap-type': {html: 'selectbox'}
	,	'recap-size': {html: 'selectbox'}
	}
,	init: function() {
		var self = this;
		this._$mainShell = jQuery('#cfsFieldsEditShell');
		// Add field window - where user will select Field HTML Type
		this._$addWnd = jQuery('#cfsFieldsAddWnd').dialog({
			modal:    true
		,	autoOpen: false
		,	width: 560
		,	buttons:  {
				Cancel: function() {
					self._clearBindToField();
					self.closeAddWnd();
				}
			}
		});
		// Main edit fields window
		this._$editWnd = jQuery('#cfsFieldsEditWnd').dialog({
			modal:    true
		,	autoOpen: false
		,	width: 600
		,	buttons:  {
				Ok: function() {
					if(self.saveFieldWnd()) {
						self.closeEditWnd();
					}
				}
			,	Cancel: function() {
					self._clearBindToField();
					self.closeEditWnd();
				}
			}
		});
		// Html delim edit field window
		this._$htmlEditorWnd = jQuery('#cfsFormFieldHtmlInpWnd').dialog({
			modal:    true
		,	autoOpen: false
		,	width: 600
		,	buttons:  {
				Ok: function() {
					if(self.saveHtmlDelimFieldWnd()) {
						self.closeHtmlDelimEditWnd();
					}
				}
			,	Cancel: function() {
					self._clearBindToField();
					self.closeHtmlDelimEditWnd();
				}
			}
		});
		// Google maps field window
		this._$googleMapsWnd = jQuery('#cfsFormFieldGoogleMapsWnd').dialog({
			modal:    true
		,	autoOpen: false
		,	width: 600
		,	buttons:  {
				Ok: function() {
					if(self.saveGoogleMapFieldWnd()) {
						self.closeGoogleMapsWnd();
					}
				}
			,	Cancel: function() {
					self._clearBindToField();
					self.closeGoogleMapsWnd();
				}
			}
		});
		// In edit window - options will be separated into several tabs
		this._$editWnd.wpTabs({
			uniqId: 'cfsFieldsEditWnd'
		});
		// Init field validate pattern builder for edit window
		this._bindPatternBuilder();
		// Select element type from Add wnd list
		jQuery('.cfsFieldWndElement').click(function(){
			var ftHtml = jQuery(this).data('html')
			,	isPro = parseInt(jQuery(this).data('pro'));
			self.closeAddWnd();
			if(isPro) {
				cfsFillAndShowMainPromoWnd( jQuery(this) );
			} else {
				self.showEditWnd({html: ftHtml}, true);
			}
			return false;
		});
		// Add list opts
		jQuery('.cfsFieldsAddListOpt').click(function(){
			self._addListOpt();
			return false;
		});
		// Add field
		jQuery('.cfsAddFieldBtn').click(function(){
			// Add new fields - right before last submit or button, as this should be desired by users.
			// In any case - there are always possibility to drag field in any other place
			var $shells = self._$mainShell.find('.cfsFieldShell');
			if($shells && $shells.size()) {
				$shells.each(function(){
					var html = jQuery(this).data('html');
					if(toeInArrayCfs(html, ['submit', 'button'])) {
						self._$addFieldNextTo = jQuery(this);
					}
				});
			}
			if(self._$addFieldNextTo) {
				self._addFieldNextToPos = 'top';
			}
			self.showAddWnd();
			return false;
		});
		// Make fields - sortable
		jQuery('#cfsFieldsEditShell').sortable({
			items: '.cfsFieldRow'
		,	handle: '.cfsMoveVFieldHandle'
		,	axis: 'y'
		,	start: function() {
				self._sortInProgress = true;
			}
		,	update: function() {
				self.updateSortOrder();
			}
		});
		// Field list type options - sortable too
		jQuery('#cfsFieldsListOptsShell').sortable({
			items: '.cfsFieldListOptShell:not(#cfsFieldListOptShellExl)'
		,	axis: 'y'
		,	update: function() {
				self._updateOptsListSortOrder();
			}
		});
		// Init parameters to html types relationship
		this._initParamToHtmlTypeRelation();
		if(!CFS_DATA.isPro) {
			this._$editWnd.find('[name="value_preset"]').change(function(){
				if(jQuery(this).val())
					cfsFillAndShowMainPromoWnd( jQuery(this).parents('td:first') );
			});
		}
		jQuery(document).trigger('cfsAfterFieldsEditInit');
	}
,	_initParamToHtmlTypeRelation: function() {
		var $paramRows = this._$editWnd.find('.cfsFieldParamRow');
		$paramRows.each(function(){
			var $this = jQuery(this)
			,	forStr = $this.data('for')
			,	notForStr = $this.data('not-for');
			if(forStr && forStr != '') {
				this._cfsFor = forStr.split(',');
			}
			if(notForStr && notForStr != '') {
				this._cfsNotFor = notForStr.split(',');
			}
		});
	}
,	_checkParamToHtmlRelation: function( htmlCode ) {
		var $paramRows = this._$editWnd.find('.cfsFieldParamRow');
		$paramRows.show().attr('data-show-for-field', 1);
		$paramRows.each(function(){
			var forArr = this._cfsFor
			,	notForArr = this._cfsNotFor
			,	hide = false
			,	$this = jQuery(this);
			if(forArr && !toeInArrayCfs(htmlCode, forArr)) {
				hide = true;
			}
			if(notForArr && toeInArrayCfs(htmlCode, notForArr)) {
				hide = true;
			}
			if(hide) {
				jQuery(this).hide().attr('data-show-for-field', 0);
			}
		});
		// Hide fully empty tabs
		var tabsIds = ['cfsFormFieldBaseSettings', 'cfsFormFieldAdvancedSettings', 'cfsFormFieldValidation'];
		this._$editWnd.find('.nav-tab').show();
		for(var i = 0; i < tabsIds.length; i++) {
			var $tab = this._$editWnd.find('#'+ tabsIds[ i ]);
			if(!$tab.find('.cfsFieldParamRow[data-show-for-field=1]').size()) {
				$tab.hide();
				this._$editWnd.find('.nav-tab[href="#'+ tabsIds[ i ]+ '"]').hide();
			}
		}
	}
,	_bindPatternBuilder: function() {
		var self = this;
		this._$patternInput = this._$editWnd.find('[name="vn_pattern"]');
		this._$editWnd.find('[name="vn_only_number"]').change(function(){
			self._setPattern('\\d+', !jQuery(this).prop('checked'));
		});
		this._$editWnd.find('[name="vn_only_letters"]').change(function(){
			self._setPattern('\\w+', !jQuery(this).prop('checked'));
		});
	}
,	_setPattern: function( pattern, unset ) {
		var currPattern = this._$patternInput.val()
		,	newPattern = currPattern;
		if(unset) {
			newPattern = currPattern ? str_replace(currPattern, pattern, '') : '';
		} else if(!currPattern || strpos(currPattern, pattern) === false) {
			newPattern = (currPattern ? (currPattern+ '|') : '')+ pattern;
		}
		if(newPattern !== currPattern) {
			if(newPattern.indexOf('|') == 0) {
				newPattern = newPattern.substr(1, newPattern.length);
			}
			if(newPattern.lastIndexOf('|') == newPattern.length - 1) {
				newPattern = newPattern.substr(0, newPattern.length - 1);
			}
			this._$patternInput.val( newPattern );
		}
	}
,	showAddWnd: function() {
		this._$addWnd.dialog('open');
	}
,	closeAddWnd: function() {
		this._$addWnd.dialog('close');
	}
,	showEditWnd: function( field, isCreate ) {
		if(field && field.html == 'htmldelim') {
			cfsSetTxtEditorVal( this._htmlEditorId, (!isCreate && field.value ? field.value : '') );
			this._$htmlEditorWnd.dialog('open');
		} else if(field && field.html == 'googlemap') {
			jQuery(this._googleMapsId).val((!isCreate && field.value ? field.value : ''));
			this._$googleMapsWnd.dialog('open');
		} else {
			this.clearEditWnd();
			if(field) {
				this.fillInEditWnd( field, isCreate );
			}
			this._$editWnd.dialog('open');
			this._$editWnd.find('.wnd-chosen').chosen({
				disable_search_threshold: 10
			}).trigger('chosen:updated');
		}
	}
,	closeEditWnd: function() {
		this._$editWnd.dialog('close');
	}
,	closeHtmlDelimEditWnd: function() {
		this._$htmlEditorWnd.dialog('close');
	}
,	closeGoogleMapsWnd: function() {
		this._$googleMapsWnd.dialog('close');
	}
,	_clearBindToField: function() {
		this._$addFieldNextTo = null;
		this._addFieldNextToPos = '';
		this._$editFieldShell = null;
	}
,	clearEditWnd: function() {
		this._$editWnd.find('input:not([type="checkbox"])').val('');
		this._$editWnd.find('select').each(function(){
			this.selectedIndex = 0;
		}).trigger('change');
		cfsCheckUpdate(this._$editWnd.find('input[type=checkbox]').removeProp('checked'));
		this._$editWnd.find('.cfsFieldListOptShell:not(#cfsFieldListOptShellExl)').remove();
		this._$editWnd.find('.cfsFieldsEditForLists').hide();
		this._$editWnd.find('.cfsFieldsEditForCheckRadioLists').hide();
		this._hideEditFieldErrors();
		// Open first - Basic - tab by default
		this._$editWnd.wpTabs('activate', '#cfsFormFieldBaseSettings')
	}
,	fillInEditWnd: function( field, isCreate ) {
		for(var key in field) {
			var $input = this._$editWnd.find('[name="'+ key+ '"]');
			if($input && $input.size()) {
				if(this._fields[ key ]) {
					switch(this._fields[ key ].html) {
						case 'checkbox':
							parseInt(field[ key ])
								? $input.prop('checked', 'checked')
								: $input.removeProp('checked');
							break;
						default:
							$input.val( field[ key ] );
							break;
					}
				} else
					$input.val( field[ key ] );
			}
		}
		if(field.html == 'checkboxsubscribe') {
			this._$editWnd.find('[name="name"]').val('checkboxsubscribe');
			if(isCreate) {
				this._$editWnd.find('[name="def_checked"]').prop('checked', 'checked');
			}
		}
		cfsCheckUpdate( this._$editWnd.find('input[type=checkbox]') );
		this._checkParamToHtmlRelation( field.html );
		if(this.isFieldListSupported( field.html )) {
			this._$editWnd.find('.cfsFieldsEditForLists').show();
			this._fillInListsOpts( field );
		} else {
			this._$editWnd.find('.cfsFieldsEditForLists').hide();
		}
		if(toeInArrayCfs(field.html, ['radiobuttons', 'checkboxlist'])) {
			var $checkListShell = this._$editWnd.find('.cfsFieldsEditForCheckRadioLists');
			if(!field.display) {	// Set default value
				$checkListShell.find('[name="display"]').val('row');
			}
		}
	}
,	_fillInListsOpts: function( field ) {
		var options = this._listOptsToArr( field );
		if(options && options.length) {
			var $optExRow = jQuery('#cfsFieldListOptShellExl');
			for(var i = 0; i < options.length; i++) {
				this._addListOpt(options[ i ], {
					$optExRow: $optExRow
				});
			}
			this._updateOptsListSortOrder();
		}
	}
,	_addListOpt: function( opt, params ) {
		// Lazy-load - yeah?:)
		if(!this._$listOptsShell) {
			this._$listOptsShell = jQuery('#cfsFieldsListOptsShell');
		}
		params = params || {};
		var $optShell = (params.$optExRow ? params.$optExRow : jQuery('#cfsFieldListOptShellExl')).clone().removeAttr('id')
		,	self = this;
		$optShell.appendTo( this._$listOptsShell );
		if(opt) {
			$optShell.find('[name="options[][name]"]').val( opt.name );
			$optShell.find('[name="options[][label]"]').val( opt.label );
		}
		$optShell.find('input').removeAttr('disabled');
		// Remove opt
		$optShell.find('.cfsFieldsListOptRemoveBtn').click(function(){
			self._removeListOpt( $optShell );
			return false;
		});
		if(!params.$optExRow) {	// This mea that we add only one opt, not adding batch of them
			this._updateOptsListSortOrder()
		}
	}
,	_removeListOpt: function( $optShell ) {
		if(confirm('Are you sure want to remove this option?')) {
			var self = this;
			$optShell.animateRemoveCfs(g_cfsAnimationSpeed, function(){
				self._updateOptsListSortOrder();
			});
		}
	}
,	_updateOptsListSortOrder: function() {
		var $shells = this._$listOptsShell.find('.cfsFieldListOptShell:not(#cfsFieldListOptShellExl)')
		,	i = 0;
		$shells.each(function(){
			var $inputs = jQuery(this).find('[name^="options["]');
			$inputs.each(function(){
				var name = jQuery(this).attr('name');
				jQuery(this).attr('name', name.replace(/(options\[\]|options\[\d+\])/g, 'options['+ i+ ']'));
			});
			i++;
		});
	}
,	storeField: function( params ) {
		var update = params.update && this._$editFieldShell
		,	data = params.data ? params.data : false
		,	$shell = null
		,	baseInit = params.baseInit;

		if(update) {
			$shell = this._$editFieldShell;
		} else {
			var $fieldsExRow = params.$fieldsExRow ? params.$fieldsExRow : jQuery('#cfsFieldShellEx');
			cfsCheckDestroyArea( $fieldsExRow );
			$shell = $fieldsExRow.clone().removeAttr('id');
			$shell.find('input').removeAttr('disabled');
		}
		if(data && data.html) {
			var htmlCode = data.html
			,	isListSupported = this.isFieldListSupported( htmlCode );
			if( isListSupported ) {
				this._clearListOptsFromShell( $shell );
			}
			// Update input fields - to save data on server
			var fieldFields = this._getFieldFields( htmlCode );
			for(var k in fieldFields) {
				if(typeof(data[ k ]) === 'undefined' && fieldFields[ k ].html !== 'checkbox') continue;
				var $input = $shell.find('[name*="['+ k+ ']"]')
				,	value = data[ k ];
				if(!$input || !$input.size()) {
					$input = this._createShellFieldField( $shell, k );
				}
				switch(fieldFields[ k ].html) {
					case 'checkbox':
						value = parseInt(value) ? 1 : 0;
						break;
				}
				$input.val( value );
			}
			// Update HTML labels - to show user what he is editing now
			var showLabel = data.label ? data.label : data.placeholder
			,	showHtmlType = cfsFormTypes[ htmlCode ].label;
			$shell.find('.csfFieldIcon').html( '<i class="fa '+ cfsFormTypes[ htmlCode ].icon+ '"></i>' );
			$shell.find('.csfFieldLabel').html( showLabel );
			$shell.find('.csfFieldType').html( showHtmlType );
			$shell.attr('title', showLabel+ ' ['+ showHtmlType+ ']');
			if(isListSupported) {
				this._storeListOpts( $shell, data );
			}
			$shell.data('html', data.html);
		}
		if(!update) {
			$shell.find('input,select').removeAttr('disabled');
			var $row = null;
			if(baseInit) {
				var bsClassId = parseInt(data.bs_class_id)
				,	$prevFieldShell = params.$prevFieldShell
				,	bsClassIdCounter = params.bsClassIdCounter;
				if(bsClassId && bsClassId < 12 && $prevFieldShell && bsClassIdCounter && bsClassIdCounter % 12) {
					this._addFieldNextToPos = 'right';
					this._$addFieldNextTo = $prevFieldShell;
				}
			}
			switch(this._addFieldNextToPos) {
				case 'left': case 'right':
					$row = this._getParentRow( this._$addFieldNextTo );
					break;
				case 'top': case 'bottom': default:
					$row = this._wrapRow( $shell );
					break;
			}
			if(this._$addFieldNextTo) {
				switch(this._addFieldNextToPos) {
					case 'top':
						$row.insertBefore( this._getParentRow( this._$addFieldNextTo ) );
						break;
					case 'right':
						$shell.insertAfter( this._$addFieldNextTo );
						break;
					case 'bottom':
						$row.insertAfter( this._getParentRow( this._$addFieldNextTo ) );
						break;
					case 'left':
						$shell.insertBefore( this._$addFieldNextTo );
						break;
					default:	// Add it in any case
						$row.appendTo( this._$mainShell );
						break;
				}
			} else {
				$row.appendTo( this._$mainShell );
			}
			this._assignRowShellsClasses( $row );
			this._initShellActions( $shell, data );
		}
		this._clearBindToField();
		return $shell;
	}
,	_getFieldFields: function( htmlCode ) {
		var res = {};
		res = jQuery.extend(res, this._fields);
		if(htmlCode == 'recaptcha') {
			res = jQuery.extend(res, this._recapFields);
		}
		return res;
	}
,	_createShellFieldField: function( $shell, name ) {
		return jQuery('<input type="hidden" name="params[fields][]['+ name+ ']" />').appendTo( $shell );
	}
,	_clearListOptsFromShell: function( $row ) {
		$row.find('input[name*="[options]["]').remove();
	}
,	_getParentRow: function( $shell ) {
		return $shell.parents('.cfsFieldRow:first');
	} 
,	_wrapRow: function( $shell ) {
		var $row = jQuery('<div class="row cfsFieldRow" />').append( jQuery('#cfsMoveVFieldHandleExl').clone().removeAttr('id') ).append( $shell )
		,	self = this;
		$row.sortable({
			items: '.cfsFieldShell'
		,	handle: '.cfsMoveHFieldHandle'
		,	axis: 'x'
		,	start: function() {
				self._sortInProgress = true;
			}
		,	update: function() {
				self.updateSortOrder();
			}
		});
		return $row;
	}
,	_assignRowShellsClasses: function( $row, newBsClassId ) {
		var $shells = $row.find('.cfsFieldShell')
		,	shellsNum = $shells.size();
		if(!shellsNum) {	// No fields in this row - we don't need this anymore. Cruel world.........
			$row.remove();
			return;
		}
		if(!newBsClassId) {
			var currBsClasses = this._extractBootstrapColsClasses( $shells.first() )
			,	newBsClassId = Math.floor( 12 / shellsNum );
			$shells.removeClass( currBsClasses.join(',') )
		}
		$shells
			.addClass('col-sm-'+ newBsClassId)
			.data('bs_class_id', newBsClassId);
		$shells.find('[name*="[bs_class_id]"]').val( newBsClassId );
		if(newBsClassId < 12) {
			$shells.find('.cfsMoveHFieldHandle').show();
		} else {
			$shells.find('.cfsMoveHFieldHandle').hide();
		}
	}
,	_extractBootstrapColsClasses: function( $shell ) {
		var	currClasses = jQuery.map($shell.attr('class').split(' '), jQuery.trim)
		,	newClasses = [];
		for(var i = 0; i < currClasses.length; i++) {
			if(currClasses[ i ] == 'col' || currClasses[ i ].match(/col\-\w{2}\-\d{1,2}/)) {
				newClasses.push( currClasses[ i ] );
			}
		}
		return newClasses;
	}
,	_storeListOpts: function( $row, field ) {
		var options = this._listOptsToArr( field );
		if(options.length) {
			var j = 0;
			for(i = 0; i < options.length; i++) {
				if(options[ i ].name && options[ i ].name != '') {
					$row.append('<input type="hidden" name="params[fields][][options]['+ j+ '][name]" value="'+ options[ i ].name+ '" />');
					$row.append('<input type="hidden" name="params[fields][][options]['+ j+ '][label]" value="'+ options[ i ].label+ '" />');
					j++;
				}
			}
		}
	}
,	_listOptsToArr: function( field ) {
		var options = []
		,	i = 0;
		if(field && field.options) {	// This will be triggered when we add field - from it's DB settings - where it is already as array
			options = field.options;
		} else {	// This will be triggered when we add it from edit form
			for(var key in field) {
				if(typeof(key) === 'string' && key.indexOf('options[') !== -1) {
					if(i % 2 == 0) {
						options.push({name: field[ key ]});
					} else {
						options[ options.length - 1 ].label = field[ key ];
					}
					i++;
				}
			}
		}
		return options;
	}
,	isFieldListSupported: function( htmlCode ) {
		return htmlCode && toeInArrayCfs(htmlCode, ['selectbox', 'selectlist', 'radiobuttons', 'checkboxlist']);
	}
,	_rowToData: function( $row ) {
		var res = {}
		,	fData = $row.serializeAnythingCfs(false, true);
		for(var key in fData) {
			var name = key.replace(/params\[fields\](\[\d+\]|\[\])\[/, '').replace(/\]/, '');
			res[ name ] = fData[ key ]; 
		}
		return res;
	} 
,	_initShellActions: function( $shell, data ) {
		var self = this
		,	$panel = $shell.find('.cfsFieldPanel');
		// Edit field
		$shell.click(function(){
			if(self._sortInProgress) {	// Sorting was just stopped - this is not a click
				self._sortInProgress = false;
				return false;
			}
			self.editField( $shell, self._rowToData($shell) );
			return false;
		});
		// Move menu to current cursor post by X axis - I think this will be pretty UI solution
		$shell.hover(function(e){
			if(e.type == 'mouseenter') {
				self._moveFieldPanelToCursor( $panel, e.offsetX );
			}
		});
		// Remove field
		$panel.find('.cfsFieldRemoveBtn').click(function(){
			self.removeField( $shell, data );
			return false;
		});
		// Add fields next to current
		$panel.find('.cfsAddTopBtn').click(function(){
			self._addFieldNextToClb( $shell, 'top' );
			return false;
		});
		$panel.find('.cfsAddRightBtn').click(function(){
			self._addFieldNextToClb( $shell, 'right' );
			return false;
		});
		$panel.find('.cfsAddBottomBtn').click(function(){
			self._addFieldNextToClb( $shell, 'bottom' );
			return false;
		});
		$panel.find('.cfsAddLeftBtn').click(function(){
			self._addFieldNextToClb( $shell, 'left' );
			return false;
		});
	}
,	_moveFieldPanelToCursor: function( $panel, x ) {
		$panel.css('left', x);
	}
,	_addFieldNextToClb: function( $shell, pos ) {
		this._$addFieldNextTo = $shell;
		this._addFieldNextToPos = pos;
		this.showAddWnd();
	}
,	editField: function( $shell, data ) {
		this._$editFieldShell = $shell;
		this.showEditWnd( data );
	}
,	removeField: function( $shell, data ) {
		// data here can contain old data values - so need to make update label value from current shell
		if(confirm('Are you sure want to remove "'+ data.label+ '" field?')) {
			var self = this
			,	$parentRow = this._getParentRow( $shell );
			$shell.animateRemoveCfs( g_cfsAnimationSpeed, function(){
				self.updateSortOrder();
				self._assignRowShellsClasses( $parentRow );
			});
		}
	}
,	saveFieldWnd: function() {
		this._hideEditFieldErrors();
		var fieldData = this._$editWnd.serializeAnythingCfs(false, true);
		fieldData = this._prepareFieldData( fieldData );
		if(!this.validateFieldData( fieldData ))
			return false;
		this.storeField({
			data: fieldData
		,	update: true
		});
		this.updateSortOrder();
		return true;	// TODO: Add validation and false result here
	}
,	saveHtmlDelimFieldWnd: function() {
		this.storeField({
			data: {html: 'htmldelim', value: cfsGetTxtEditorVal(this._htmlEditorId)}
		,	update: true
		});
		this.updateSortOrder();
		return true;
	}
,	saveGoogleMapFieldWnd: function() {
		var mapId = parseInt(jQuery(this._googleMapsId).val());
		if(mapId) {
			var mapLabel = jQuery(this._googleMapsId).find('option[value="'+ mapId+ '"]').text();
			this.storeField({
				data: {html: 'googlemap', value: mapId, label: mapLabel}
			,	update: true
			});
			this.updateSortOrder();
		}
		return true;
	}
,	_prepareFieldData: function( fieldData ) {
		fieldData.name = fieldData.name ? jQuery.trim( fieldData.name ) : '';
		fieldData.label = fieldData.label ? jQuery.trim( fieldData.label ) : '';
		fieldData.placeholder = fieldData.placeholder ? jQuery.trim( fieldData.placeholder ) : '';
		return fieldData;
	}
,	validateFieldData: function( fieldData ) {
		var errors = []
		,	nameRegExp = /^[a-z0-9\-_]+$/i;
		if(!fieldData.name || !nameRegExp.test( fieldData.name )) {
			errors.push('name');
		}
		if((!fieldData.label || fieldData.label == '') 
			&& (!fieldData.placeholder || fieldData.placeholder == '')
		) {
			errors.push('label-placeholder');
		}
		if(errors.length) {
			this._showEditFieldErrors( errors );
			return false;
		}
		return true;
	}
,	_showEditFieldErrors: function( errors ) {
		for(var i = 0; i < errors.length; i++) {
			this._$editWnd.find('[name="'+ errors[ i ]+ '"]').addClass('cfsInputError');
			this._$editWnd.find('.cfsFieldEditErrorRow[data-for="'+ errors[ i ]+ '"]').slideDown( g_cfsAnimationSpeed );
		}
	}
,	_hideEditFieldErrors: function() {
		this._$editWnd.find('input,select,textarea').removeClass('cfsInputError');
		this._$editWnd.find('.cfsFieldEditErrorRow').hide();
	}
,	updateSortOrder: function() {
		var $rows = this._$mainShell.find('.cfsFieldShell:not(#cfsFieldShellEx)')
		,	i = 0;
		$rows.each(function(){
			var $inputs = jQuery(this).find('[name^="params[fields]"]');
			$inputs.each(function(){
				var name = jQuery(this).attr('name');
				jQuery(this).attr('name', name.replace(/(\[fields\]\[\]|\[fields\]\[\d+\])/g, '[fields]['+ i+ ']'));
			});
			i++;
		});
	}
,	haveSubmitField: function() {
		return (this._$mainShell.find('input[name*="[html]"][value="submit"]').size() 
			|| this._$mainShell.find('input[name*="[html]"][value="button"]').size());
	}
};
jQuery(document).ready(function(){
	// Set all exampled inputs as disabled
	jQuery('#cfsFieldShellEx').find('input').attr('disabled', 'disabled');
	// Init fields frame with it's basic features
	g_cfsFieldsFrame.init();
	if(typeof(cfsForm) !== 'undefined' 
		&& cfsForm.params 
		&& cfsForm.params.fields 
		&& cfsForm.params.fields.length
	) {
		var $fieldsExRow = jQuery('#cfsFieldShellEx')
		,	$prevFieldShell = null
		,	bsClassIdCounter = 0;
		for(var i = 0; i < cfsForm.params.fields.length; i++) {
			$prevFieldShell = g_cfsFieldsFrame.storeField({
				data: cfsForm.params.fields[ i ]
			,	$fieldsExRow: $fieldsExRow
			,	baseInit: true
			,	$prevFieldShell: $prevFieldShell
			,	bsClassIdCounter: bsClassIdCounter
			});
			bsClassIdCounter += parseInt(cfsForm.params.fields[ i ].bs_class_id);
		}
		g_cfsFieldsFrame.updateSortOrder();
		g_cfsFieldsFrame._clearBindToField();
	}
});
