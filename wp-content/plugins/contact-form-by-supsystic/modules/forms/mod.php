<?php
class formsCfs extends moduleCfs {
	private $_assetsUrl = '';
	private $_fieldTypes = array();

	public function init() {
		dispatcherCfs::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_shortcode(CFS_SHORTCODE, array($this, 'showForm'));
		// Add to admin bar new item
		add_action('admin_bar_menu', array($this, 'addAdminBarNewItem'), 300);
	}
	public function addAdminTab($tabs) {
		$tabs[ $this->getCode(). '_add_new' ] = array(
			'label' => __('Add New Form', CFS_LANG_CODE), 'callback' => array($this, 'getAddNewTabContent'), 'fa_icon' => 'fa-plus-circle', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode(). '_edit' ] = array(
			'label' => __('Edit', CFS_LANG_CODE), 'callback' => array($this, 'getEditTabContent'), 'sort_order' => 20, 'child_of' => $this->getCode(), 'hidden' => 1, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode() ] = array(
			'label' => __('Show All Forms', CFS_LANG_CODE), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-list', 'sort_order' => 20, //'is_main' => true,
		);
		return $tabs;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getAddNewTabContent() {
		return $this->getView()->getAddNewTabContent();
	}
	public function getEditTabContent() {
		$id = (int) reqCfs::getVar('id', 'get');
		return $this->getView()->getEditTabContent( $id );
	}
	public function getEditLink($id, $formsTab = '') {
		$link = frameCfs::_()->getModule('options')->getTabUrl( $this->getCode(). '_edit' );
		$link .= '&id='. $id;
		if(!empty($formsTab)) {
			$link .= '#'. $formsTab;
		}
		return $link;
	}
	public function getAssetsUrl() {
		if(empty($this->_assetsUrl)) {
			$this->_assetsUrl = frameCfs::_()->getModule('templates')->getCdnUrl(). '_assets/forms/';
		}
		return $this->_assetsUrl;
	}
	public function addAdminBarNewItem( $wp_admin_bar ) {
		$mainCap = frameCfs::_()->getModule('adminmenu')->getMainCap();
		if(!current_user_can( $mainCap) || !$wp_admin_bar || !is_object($wp_admin_bar)) {
			return;
		}
		$wp_admin_bar->add_menu(array(
			'parent'    => 'new-content',
			'id'        => CFS_CODE. '-admin-bar-new-item',
			'title'     => __('Form', CFS_LANG_CODE),
			'href'      => frameCfs::_()->getModule('options')->getTabUrl( $this->getCode(). '_add_new' ),
		));
	}
	public function getFieldTypes() {
		if(empty($this->_fieldTypes)) {
			$this->_fieldTypes = dispatcherCfs::applyFilters('fieldTypes', array(
				'text' => array('label' => __('Text', CFS_LANG_CODE), 'icon' => 'fa-font'),
				'email' => array('label' => __('Email', CFS_LANG_CODE), 'icon' => 'fa-envelope-o'),
				'selectbox' => array('label' => __('Select Box', CFS_LANG_CODE), 'icon' => 'fa-list-ul'),
				'selectlist' => array('label' => __('Select List', CFS_LANG_CODE), 'icon' => 'fa-th-list'),
				'textarea' => array('label' => __('Textarea', CFS_LANG_CODE), 'icon' => 'fa-font'),
				'radiobutton' => array('label' => __('Radiobutton', CFS_LANG_CODE), 'icon' => 'fa-dot-circle-o'),
				'radiobuttons' => array('label' => __('Radiobuttons List', CFS_LANG_CODE), 'icon' => 'fa-dot-circle-o'),
				'checkbox' => array('label' => __('Checkbox', CFS_LANG_CODE), 'icon' => 'fa-check-square-o'),
				'checkboxlist' => array('label' => __('Checkbox List', CFS_LANG_CODE), 'icon' => 'fa-check-square-o'),
				'checkboxsubscribe' => array('label' => __('Subscribe Checkbox', CFS_LANG_CODE), 'icon' => 'fa-user-plus', 'pro' => ''),
				'countryList' => array('label' => __('Country List', CFS_LANG_CODE), 'icon' => 'fa-globe'),
				'countryListMultiple' => array('label' => __('Country List Multiple', CFS_LANG_CODE), 'icon' => 'fa-globe'),

				'number' => array('label' => __('Number', CFS_LANG_CODE), 'icon' => 'fa-sort-numeric-asc'),

				'date' => array('label' => __('Date', CFS_LANG_CODE), 'icon' => 'fa-calendar'),
				'month' => array('label' => __('Month', CFS_LANG_CODE), 'icon' => 'fa-calendar'),
				'week' => array('label' => __('Week', CFS_LANG_CODE), 'icon' => 'fa-calendar'),
				'time' => array('label' => __('Time', CFS_LANG_CODE), 'icon' => 'fa-clock-o'),

				'color' => array('label' => __('Color', CFS_LANG_CODE), 'icon' => 'fa-paint-brush'),
				'range' => array('label' => __('Range', CFS_LANG_CODE), 'icon' => 'fa-magic'),
				'url' => array('label' => __('URL', CFS_LANG_CODE), 'icon' => 'fa-link'),

				'file' => array('label' => __('File Upload', CFS_LANG_CODE), 'icon' => 'fa-upload', 'pro' => ''),
				'recaptcha' => array('label' => __('reCaptcha', CFS_LANG_CODE), 'icon' => 'fa-unlock-alt'),
				
				'hidden' => array('label' => __('Hidden Field', CFS_LANG_CODE), 'icon' => 'fa-eye-slash'),
				'submit' => array('label' => __('Submit Button', CFS_LANG_CODE), 'icon' => 'fa-paper-plane-o'),
				'reset' => array('label' => __('Reset Button', CFS_LANG_CODE), 'icon' => 'fa-repeat'),
				
				'htmldelim' => array('label' => __('HTML / Text Delimiter', CFS_LANG_CODE), 'icon' => 'fa-code'),
				
				'googlemap' => array('label' => __('Google Map', CFS_LANG_CODE), 'icon' => 'fa-globe'),
			));
			$isPro = frameCfs::_()->getModule('supsystic_promo')->isPro();
			foreach($this->_fieldTypes as $code => $f) {
				if(isset($f['pro']) && !$isPro) {
					$this->_fieldTypes[ $code ]['pro'] = frameCfs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium=field_'. $code. '&utm_campaign=forms');;
				}
			}
		}
		return $this->_fieldTypes;
	}
	public function getFieldTypeByCode( $htmlCode ) {
		$this->getFieldTypes();
		return isset( $this->_fieldTypes[ $htmlCode ] ) ? $this->_fieldTypes[ $htmlCode ] : false;
	}
	public function isFieldListSupported( $htmlCode ) {
		return $htmlCode && in_array($htmlCode, array('selectbox', 'selectlist', 'radiobuttons', 'checkboxlist'));
	}
	public function showForm($params) {
		$id = isset($params['id']) ? (int) $params['id'] : 0;
		if(!$id && isset($params[0]) && !empty($params[0])) {	// For some reason - for some cases it convert space in shortcode - to %20 im this place
			$id = explode('=', $params[0]);
			$id = isset($id[1]) ? (int) $id[1] : 0;
		}
		if($id) {
			$params['id'] = $id;
			return $this->getView()->showForm( $params );
		}
	}
	public function getAssetsforPrevStr() {
		$frontendStyles = $this->getView()->getFrontendStyles();
		$stylesStr = '';
		foreach($frontendStyles as $sKey => $sUrl) {
			$stylesStr .= '<link rel="stylesheet" href="'. $sUrl. '" type="text/css" media="all" />';
		}
		$stylesStr .= '<style type="text/css">
				.cfsFormPreloadImg {
					width: 1px !important;
					height: 1px !important;
					position: absolute !important;
					top: -9999px !important;
					left: -9999px !important;
					opacity: 0 !important;
				}
			</style>';
		return $stylesStr;
	}
}

