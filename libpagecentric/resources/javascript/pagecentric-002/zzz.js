
function activate()
{
	setup_checklimit();
	setup_checkshow();
	setup_creditcard();
	setup_hideparent();
	setup_limitwords();
	setup_multiselect();
	setup_placeholder();
	setup_setcookie();

	setup_showmodal();
	
	setup_clickchange();
	setup_clickhome();
	setup_openpage_clearform();
	setup_clickhome();
	setup_closevideo();
	setup_formchange();
	setup_onscroll();
	setup_openpage_popup();
	setup_selectvideo();
	setup_validate();
	setup_toggle();

	pagecentric.scrollboardSetup();
}

window.onload = activate;

show();
