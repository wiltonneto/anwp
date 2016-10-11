<table class="form-table cfsFormSubmitOptsTbl" style="width: 100%">
	<tr>
		<th scope="row">
			<?php _e('Form sent message', CFS_LANG_CODE)?>
			<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Message, that your users will see after success form submition.', CFS_LANG_CODE))?>"></i>
		</th>
		<td>
			<?php echo htmlCfs::text('params[tpl][form_sent_msg]', array('value' => $this->form['params']['tpl']['form_sent_msg']))?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('Redirect after submit', CFS_LANG_CODE)?>
			<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('If you want - you can redirect user after Form was submitted. Just enter required Redirect URL here - and each time after Form will be submitted - user will be redirected to that URL. Just leave this field empty - if you don\'t need this functionality in your Form.', CFS_LANG_CODE))?>"></i>
		</th>
		<td>
			<?php echo htmlCfs::text('params[tpl][redirect_on_submit]', array(
				'value' => (isset($this->form['params']['tpl']['redirect_on_submit']) ? esc_url( $this->form['params']['tpl']['redirect_on_submit'] ) : ''),
				'attrs' => 'placeholder="http://example.com" style="width: 100%;"',
			))?><br />
			<label>
				<?php echo htmlCfs::checkbox('params[tpl][redirect_on_submit_new_wnd]', array(
					'checked' => htmlCfs::checkedOpt($this->form['params']['tpl'], 'redirect_on_submit_new_wnd')))?>
				<?php _e('Open in a new window (tab)', CFS_LANG_CODE)?>
			</label>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('Test Email Function', CFS_LANG_CODE)?>
			<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Email delivery depends from your server configuration. For some cases - you and your subscribers can not receive emails just because email on your server is not working correctly. You can easy test it here - by sending test email. If you receive it - then it means that email functionality on your server works well. If not - this means that it is not working correctly and you should contact your hosting provider with this issue and ask them to setup email functionality for you on your server.', CFS_LANG_CODE))?>"></i>
		</th>
		<td>
			<?php echo htmlCfs::email('params[tpl][test_email]', array(
				'value' => (isset($this->form['params']['tpl']['test_email']) ? $this->form['params']['tpl']['test_email'] : $this->adminEmail),
			))?>
			<a href="#" class="cfsTestEmailFuncBtn button">
				<i class="fa fa-paper-plane"></i>
				<?php _e('Send Test Email', CFS_LANG_CODE)?>
			</a>
			<div class="cfsTestEmailWasSent" style="display: none;">
				<?php _e('Email was sent. Now check your email inbox / spam folders for test mail. If you don’t find it - it means that your server can\'t send emails - and you need to contact your hosting provider with this issue.', CFS_LANG_CODE)?>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('Save contacts data', CFS_LANG_CODE)?>
			<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Store each contact form submission - into database, so you will be able to check all your form submit data.', CFS_LANG_CODE))?>"></i>
		</th>
		<td>
			<?php echo htmlCfs::checkbox('params[tpl][save_contacts]', array(
				'checked' => htmlCfs::checkedOpt($this->form['params']['tpl'], 'save_contacts')))?>
			<span class="cfsContactExportCfsBtnShell">
				<a href="<?php echo $this->csvExportUrl;?>" class="button cfsContactsExportBtn"><?php _e('Export to CSV', CFS_LANG_CODE)?></a>
			</span>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('Send only Field values', CFS_LANG_CODE)?>
			<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('By default - we will send field labels + values like:<br /><b>Field Label</b>: Field Value<br />But if you need to receive only values - you can disable sending Field Values here - just check this checkbox and it will done.', CFS_LANG_CODE))?>"></i>
		</th>
		<td>
			<?php echo htmlCfs::checkbox('params[tpl][dsbl_send_labels]', array(
				'checked' => htmlCfs::checkedOpt($this->form['params']['tpl'], 'dsbl_send_labels')))?>
		</td>
	</tr>
</table>
<div style="clear: both;"></div>
<a href="" class="cfsFormSubmitAddOpt button">
	<i class="fa fa-plus"></i>
	<?php _e('Add additional data for submit', CFS_LANG_CODE)?>
</a>
<div id="cfsFormSubmitToListShell"></div>
<div id="cfsFormSubmitToShellEx" class="cfsFormSubmitToShell">
	<table width="100%" class="cfsSubmitOptsHelpTbl" cellspacing="0" cellpadding="0">
		<tr>
			<td style="min-width: 150px; vertical-align: top;"><a href="#" class="cfsFormSubmitToAddCcBtn button" 
					data-on-txt="<?php _e('Add Copy', CFS_LANG_CODE)?>"
					data-off-txt="<?php _e('Remove Copy', CFS_LANG_CODE)?>"
				 >
					 <i class="fa fa-plus"></i>
					 <span class="cfsOnOffBtnLabel"></span>
				 </a>
				 <a href="#" class="cfsFormSubmitToRemoveBtn button" title="<?php _e('Remove', CFS_LANG_CODE)?>">
					 <i class="fa fa-trash-o"></i>
				 </a>
			</td>
			<td><span class="description" style="display: block;">
				<?php _e('You can use next variables in any field bellow: [sitename] - name of your site, [siteurl] - URL address of your site, [user_FIELD_NAME] - any user field, entered by user, where FIELD_NAME - is name of required field, for example insert in subject [user_email] - and there will be user email field data, or [user_first_name] - and there will be inserted user First Name - if such field exists in your form fields list, and variable [form_data] - only for Message field - it will contans full generated input form data.', CFS_LANG_CODE)?>
			</span></td>
		</tr>
	</table>
	<table class="form-table cfsFormSubmitToTbl" style="width: 100%">
		<tr>
			<th scope="row">
				<?php _e('To', CFS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip sup-no-init" title="<?php echo esc_html(__('Email where we need to send contact form info. Can enter several email addresses, separated by comma ",".', CFS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlCfs::text('params[submit][][to]', array(
					'value' => $this->adminEmail
				))?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php _e('From', CFS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip sup-no-init" title="<?php echo esc_html(__('"From" parameter in your emails. Usually - this is your main admin WP email address.', CFS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlCfs::text('params[submit][][from]', array(
					'value' => $this->adminEmail
				))?>
			</td>
		</tr>
		<tr class="cfsFormSubmitToCcShell">
			<th scope="row">
				<?php _e('Copy', CFS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip sup-no-init" title="<?php echo esc_html(__('Add recipients to copy email addresses. Can enter several email addresses, separated by comma ",".', CFS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlCfs::text('params[submit][][cc]')?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php _e('Reply To', CFS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip sup-no-init" title="<?php echo esc_html(__('Reply To parameter in your email', CFS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlCfs::text('params[submit][][reply]')?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php _e('Subject', CFS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip sup-no-init" title="<?php echo esc_html(__('Email subject', CFS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlCfs::text('params[submit][][subject]')?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php _e('Message', CFS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip sup-no-init" title="<?php echo esc_html(__('Email message content', CFS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlCfs::textarea('params[submit][][msg]')?>
			</td>
		</tr>
	</table>
	<?php echo htmlCfs::hidden('params[submit][][enb_cc]');?>
</div>
