
function string_isMatchingNumber( value, min, max, spaces )
{
	var valid = false;

	if ( (min <= value.length) && (value.length <= max) )
	{
		valid = true;
	
		for ( var i=0; i < value.length; i++ )
		{
			var ch = value.charAt(i);
			if ( (' ' == ch) && spaces )
			{}
			else
			if ( NaN == parseInt( ch ) )
			{
				valid = false;
				break;
			}
		}
	}
	return valid;
}

function string_isCreditCard( value )
{
	return string_isMatchingNumber( value, 16, 20, true );
}

function string_isCVV( value )
{
	return string_isMatchingNumber( value, 3, 3, false );
}

function string_isMonth( value )
{
	var n = parseInt( value );
	return (string_isMatchingNumber( value, 1, 2, false ) && (1 <= n) && (n <= 12));
}

function string_isMonth2( value )
{
	var n = parseInt( value );
	return (string_isMatchingNumber( value, 2, 2, false ) && (1 <= n) && (n <= 12));
}

function string_isYear( value )
{
	return string_isMatchingNumber( value, 4, 4, false );
}

function checkOtherFields()
{
	var form   = document.getElementById( 'braintree-payment-form' );
	var inputs = form.getElementsByTagName( "input" );

	var valid = true;
	for ( var i=0; i < inputs.length; i++ )
	{
		if ( "text" == inputs[i].type )
		{
			valid = inputs[i].getAttribute( "data-valid" );
			if ( !valid )
			{
				inputs[i].focus();
				break;
			}
		}
	}

	if ( valid )
	{
		var submit = document.getElementById( "creditcard-submit" );
		if ( submit )
		{
			submit.disabled = false;
			submit.focus();
		}
	}
}

function creditcard( event )
{
	if ( ("U+0009" == event.keyIdentifier) || ("Shift" == event.keyIdentifier) )
	{
		//	If the user has used Shift+Tab to return to the field,
		//	don't perform processing.


		return false;
	}
	else
	{
		var field = this.getAttribute( "data-encrypted-name" );
		var value = this.value;
		var valid = false;

		switch ( field )
		{
		case "number":
			value = value.replace( / /g, '' );
			valid = string_isCreditCard( value );
			var four = value.substring(12,16);
			var ff   = document.getElementById( 'creditcard-final_four' );
			if ( ff )
			{
				ff.value = four;
			}
			break;
			
		case "cvv":
			valid = string_isCVV( value );
			break;

		case "month":
			valid = string_isMonth( value );
			valid = string_isMonth2( value );
			break;

		case "year":
			valid = string_isYear( value );
		}
		
		if ( valid )
		{
			this.setAttribute( "data-valid", "true" );
			checkOtherFields();
		}
		else
		{
			this.className = "warning";
			this.focus();
		}
		return true;
	}
}

function setup_creditcard()
{
	var inputs = document.getElementsByTagName( 'input' );
	var n      = inputs.length;

	for ( var i=0; i < n; i++ )
	{
		if ( 'creditcard' == inputs[i].getAttribute( 'data-action' ) )
		{
			inputs[i].onkeyup = creditcard;
		}
	}
}

//setup_checklimit();
