jQuery(document).ready(function(){
	jQuery('#cfsSettingsSaveBtn').click(function(){
		jQuery('#cfsSettingsForm').submit();
		return false;
	});
	jQuery('#cfsSettingsForm').submit(function(){
		jQuery(this).sendFormCfs({
			btn: jQuery('#cfsSettingsSaveBtn')
		});
		return false;
	});
});