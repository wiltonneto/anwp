<?php
class mailCfs extends moduleCfs {
	public function init() {
		parent::init();
		//dispatcherCfs::addFilter('optionsDefine', array($this, 'addOptions'));
	}
	public function send($to, $subject, $message, $fromName = '', $fromEmail = '', $replyToName = '', $replyToEmail = '', $additionalHeaders = array(), $additionalParameters = array()) {
		$headersArr = array();
		$eol = "\r\n";
		$replyToExists = $fromNameExists = false;
		if(!empty($additionalHeaders)) {
			foreach($additionalHeaders as $addH) {
				if(strpos($addH, 'From:') !== false) {
					$fromNameExists = true;
				} elseif(strpos($addH, 'Reply-To:') !== false) {
					$replyToExists = true;
				}
			}
		}
        if(!empty($fromName) && !empty($fromEmail) && !$fromNameExists) {
            $headersArr[] = 'From: '. $fromName. ' <'. $fromEmail. '>';
        }
		if(!empty($replyToName) && !empty($replyToEmail) && !$replyToExists) {
            $headersArr[] = 'Reply-To: '. $replyToName. ' <'. $replyToEmail. '>';
        }
		if(!empty($additionalHeaders)) {
			$headersArr = array_merge($headersArr, $additionalHeaders);
		}
		if(!function_exists('wp_mail'))
			frameCfs::_()->loadPlugins();
		if(!frameCfs::_()->getModule('options')->get('disable_email_html_type')) {
			add_filter('wp_mail_content_type', array($this, 'mailContentType'));
		}
		
		$attach = null;
		if(isset($additionalParameters['attach']) && !empty($additionalParameters['attach'])) {
			$attach = $additionalParameters['attach'];
		}
		if(empty($attach)) {
			$result = wp_mail($to, $subject, $message, implode($eol, $headersArr));
		} else {
			$result = wp_mail($to, $subject, $message, implode($eol, $headersArr), $attach);
		}
		if(!frameCfs::_()->getModule('options')->get('disable_email_html_type')) {
			remove_filter('wp_mail_content_type', array($this, 'mailContentType'));
		}

		return $result;
	}
	public function getMailErrors() {
		global $ts_mail_errors;
		global $phpmailer;
		// Clear prev. send errors at first
		$ts_mail_errors = array();

		// Let's try to get errors about mail sending from WP
		if (!isset($ts_mail_errors)) $ts_mail_errors = array();
		if (isset($phpmailer)) {
			$ts_mail_errors[] = $phpmailer->ErrorInfo;
		}
		if(empty($ts_mail_errors)) {
			$ts_mail_errors[] = __('Cannot send email - problem with send server', CFS_LANG_CODE);
		}
		return $ts_mail_errors;
	}
	public function mailContentType($contentType) {
		$contentType = 'text/html';
        return $contentType;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function addOptions($opts) {
		$opts[ $this->getCode() ] = array(
			'label' => __('Mail', CFS_LANG_CODE),
			'opts' => array(
				'mail_function_work' => array('label' => __('Mail function tested and work', CFS_LANG_CODE), 'desc' => ''),
				'notify_email' => array('label' => __('Notify Email', CFS_LANG_CODE), 'desc' => __('Email address used for all email notifications from plugin', CFS_LANG_CODE), 'html' => 'text', 'def' => get_option('admin_email')),
			),
		);
		return $opts;
	}
}