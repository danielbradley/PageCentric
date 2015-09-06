<?php

spl_autoload_register( function ( $classname )
{
	$classname = str_replace( "\\", ".", $classname );

	switch ( $classname )
	{
	case 'AccountPage':
		include( "pagecentric.payments.page/AccountPage.php" );
		break;

	case 'AutoFormElement':
		include( "pagecentric.html.autoform/AutoFormElement.php" );
		break;

	case 'AutoFormTextArea':
		include( "pagecentric.html.autoform/AutoFormTextArea.php" );
		break;

	case 'AutoFormInput':
		include( "pagecentric.html.autoform/AutoFormInput.php" );
		break;

	case 'AutoFormTextInput':
		include( "pagecentric.html.autoform/AutoFormTextInput.php" );
		break;

	case 'AutoFormButton':
		include( "pagecentric.html.autoform/AutoFormButton.php" );
		break;

	case 'AutoFormFreeForm':
		include( "pagecentric.html.autoform/AutoFormFreeForm.php" );
		break;

	case 'AutoFormFile':
		include( "pagecentric.html.autoform/AutoFormFile.php" );
		break;

	case 'CheckboxGroup':
		include( "pagecentric.html.forms/CheckboxGroup.php" );
		break;

	case 'RadioGroup':
		include( "pagecentric.html.forms/RadioGroup.php" );
		break;

	case 'SelectGroup':
		include( "pagecentric.html.forms/SelectGroup.php" );
		break;

	case 'Logs':
		include( "pagecentric.logs.models/Logs.php" );
		break;

	case 'APIPage':
		include( "pagecentric.api.page/APIPage.php" );
		break;

	case 'ContentView':
		include( "pagecentric.content.views/ContentView.php" );
		break;

	case 'ArticlesView':
		include( "pagecentric.content.views/ArticlesView.php" );
		break;

	case 'ArticleView':
		include( "pagecentric.content.views/ArticleView.php" );
		break;

	case 'DownloadPage':
		include( "pagecentric.files.page/DownloadPage.php" );
		break;

	case 'AccountDetailsForm':
		include( "pagecentric.payments.forms/AccountDetailsForm.php" );
		break;

	case 'CreditCardForm':
		include( "pagecentric.payments.forms/CreditCardForm.php" );
		break;

	case 'Content':
		include( "pagecentric.content.models/Content.php" );
		break;

	case 'Articles':
		include( "pagecentric.content.models/Articles.php" );
		break;

	case 'LoginForm':
		include( "pagecentric.accounts.forms/LoginForm.php" );
		break;

	case 'CreateAccountForm':
		include( "pagecentric.accounts.forms/CreateAccountForm.php" );
		break;

	case 'TransactionsTable':
		include( "pagecentric.payments.tables/TransactionsTable.php" );
		break;

	case 'YesNoForm':
		include( "pagecentric.forms/YesNoForm.php" );
		break;

	case 'PostBox':
		include( "pagecentric.email/PostBox.php" );
		break;

	case 'AdminUsers':
		include( "pagecentric.admin.models/AdminUsers.php" );
		break;

	case 'Accounts':
		include( "pagecentric.accounts.models/Accounts.php" );
		break;

	case 'FormModal':
		include( "pagecentric.modals/FormModal.php" );
		break;

	case 'Preregistrations':
		include( "pagecentric.preregistrations.models/Preregistrations.php" );
		break;

	case 'JSON':
		include( "pagecentric.util/JSON.php" );
		break;

	case 'MVC':
		include( "pagecentric.util/MVC.php" );
		break;

	case 'HelperFunctions':
		include( "pagecentric.util/HelperFunctions.php" );
		break;

	case 'JSON2':
		include( "pagecentric.util/JSON2.php" );
		break;

	case 'CSV':
		include( "pagecentric.util/CSV.php" );
		break;

	case 'Files':
		include( "pagecentric.util/Files.php" );
		break;

	case 'Printer':
		include( "pagecentric.util/Printer.php" );
		break;

	case 'Input':
		include( "pagecentric.util/Input.php" );
		break;

	case 'DBi':
		include( "pagecentric.util/DBi.php" );
		break;

	case 'User':
		include( "pagecentric.util/User.php" );
		break;

	case 'SessionSP':
		include( "pagecentric.util/SessionSP.php" );
		break;

	case 'HTML':
		include( "pagecentric.util/HTML.php" );
		break;

	case 'AdminUsersView':
		include( "pagecentric.admin.views/AdminUsersView.php" );
		break;

	case 'TextArea':
		include( "pagecentric.html/TextArea.php" );
		break;

	case 'PassInput':
		include( "pagecentric.html/PassInput.php" );
		break;

	case 'Accordion':
		include( "pagecentric.html/Accordion.php" );
		break;

	case 'Menu':
		include( "pagecentric.html/Menu.php" );
		break;

	case 'TextInput':
		include( "pagecentric.html/TextInput.php" );
		break;

	case 'Breadcrumbs':
		include( "pagecentric.html/Breadcrumbs.php" );
		break;

	case 'TableMenu':
		include( "pagecentric.html/TableMenu.php" );
		break;

	case 'AutoForm':
		include( "pagecentric.html/AutoForm.php" );
		break;

	case 'HTMLUtils':
		include( "pagecentric.html/HTMLUtils.php" );
		break;

	case 'LoginControl':
		include( "pagecentric.accounts.controls/LoginControl.php" );
		break;

	case 'CreateAccountControl':
		include( "pagecentric.accounts.controls/CreateAccountControl.php" );
		break;

	case 'DBCredentialsView':
		include( "pagecentric.initialisation.views/DBCredentialsView.php" );
		break;

	case 'Visits':
		include( "pagecentric.statistics.models/Visits.php" );
		break;

	case 'Impressions':
		include( "pagecentric.statistics.models/Impressions.php" );
		break;

	case 'InitialisationControl':
		include( "pagecentric.initialisation.controls/InitialisationControl.php" );
		break;

	case 'InvoicesView':
		include( "pagecentric.payments.views/InvoicesView.php" );
		break;

	case 'ArticleSummaryFlow':
		include( "pagecentric.content.flows/ArticleSummaryFlow.php" );
		break;

	case 'Page':
		include( "pagecentric.page/Page.php" );
		break;

	case 'ModalView':
		include( "pagecentric.html.modals/ModalView.php" );
		break;

	case 'Dialog':
		include( "pagecentric.html.modals/Dialog.php" );
		break;

	case 'ArticleSummaryElement':
		include( "pagecentric.content.elements/ArticleSummaryElement.php" );
		break;

	case 'InitialisationController':
		include( "pagecentric.initialisation.controllers/InitialisationController.php" );
		break;

	case 'FilesController':
		include( "pagecentric.files.controllers/FilesController.php" );
		break;

	case 'InitialisationPage':
		include( "pagecentric.initialisation.page/InitialisationPage.php" );
		break;

	case 'Payments':
		include( "pagecentric.payments.models/Payments.php" );
		break;

	case 'AdminUsersTable':
		include( "pagecentric.admin.tables/AdminUsersTable.php" );
		break;

	case 'CreditCardControl':
		include( "pagecentric.payments.controls/CreditCardControl.php" );
		break;

	case 'PaymentPlanControl':
		include( "pagecentric.payments.controls/PaymentPlanControl.php" );
		break;

	case 'AccountDetailsControl':
		include( "pagecentric.payments.controls/AccountDetailsControl.php" );
		break;

	case 'EmailAddress':
		include( "pagecentric.objects/EmailAddress.php" );
		break;

	case 'InputValidation':
		include( "pagecentric.objects/InputValidation.php" );
		break;

	case 'DataFile':
		include( "pagecentric.objects/DataFile.php" );
		break;

	case 'SQL':
		include( "pagecentric.objects/SQL.php" );
		break;

	case 'Email':
		include( "pagecentric.objects/Email.php" );
		break;

	case 'Viva':
		include( "pagecentric.objects/Viva.php" );
		break;

	case 'URLParameters':
		include( "pagecentric.objects/URLParameters.php" );
		break;

	case 'DateX':
		include( "pagecentric.objects/DateX.php" );
		break;

	case 'Date':
		include( "pagecentric.objects/Date.php" );
		break;

	case 'JSONFile':
		include( "pagecentric.objects/JSONFile.php" );
		break;

	case 'CSVFile':
		include( "pagecentric.objects/CSVFile.php" );
		break;

	case 'PaymentsController':
		include( "pagecentric.payments.controllers/PaymentsController.php" );
		break;

	case 'ArticleInfo':
		include( "pagecentric.content.objects/ArticleInfo.php" );
		break;

	case 'Article':
		include( "pagecentric.content.objects/Article.php" );
		break;

	}
});
