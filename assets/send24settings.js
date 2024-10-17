jQuery(document).ready(function(){
	const getMode = jQuery('.send24_mode').val();
	const url = window.location.href.toString();
	console.log('URL', url);
	if (url.includes('zone')){
		return;
	}

		if(getMode == 'live'){
			jQuery('#')
			jQuery('.send24_test').css('display','none');
			jQuery('.send24_test').css('opacity','0');
			jQuery('.send24_live').css('display','block');
			jQuery('.send24_live').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(2) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(2) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','0');

			jQuery('#mainform table tbody tr:nth-child(4)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(2)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','none');
		}else{
			jQuery('.send24_live').css('display','none');
			jQuery('.send24_live').css('opacity','0');
			jQuery('.send24_test').css('display','block');
			jQuery('.send24_test').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','0');

			jQuery('#mainform table tbody tr:nth-child(2) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(2) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(4)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(2)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','table-row');

		}



	jQuery('.send24_mode').change(function(){
		const getChangeVal = jQuery(this).val();

		if(getChangeVal == 'live'){
			jQuery('.send24_test').css('display','none');
			jQuery('.send24_test').css('opacity','0');
			jQuery('.send24_live').css('display','block');
			jQuery('.send24_live').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(2) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(2) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','0');

			jQuery('#mainform table tbody tr:nth-child(4)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(2)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','none');

		}else{
			jQuery('.send24_live').css('display','none');
			jQuery('.send24_live').css('opacity','0');
			jQuery('.send24_test').css('display','block');
			jQuery('.send24_test').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(4) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(4) td fieldset p').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) th label').css('opacity','0');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5) td fieldset p').css('opacity','0');

			jQuery('#mainform table tbody tr:nth-child(2) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(2) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(2) td fieldset p').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) th label').css('opacity','1');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('display','block');
			jQuery('#mainform table tbody tr:nth-child(3) td fieldset p').css('opacity','1');

			jQuery('#mainform table tbody tr:nth-child(4)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(5)').css('display','none');
			jQuery('#mainform table tbody tr:nth-child(2)').css('display','table-row');
			jQuery('#mainform table tbody tr:nth-child(3)').css('display','table-row');
		}
	});
});



