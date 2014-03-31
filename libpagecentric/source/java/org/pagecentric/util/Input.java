package org.pagecentric.util;

public class Input
{
/*

Converts encoded characters into HTML entitites:
e.g. %20 -> &#20;

Is also intended to protect against symbols related to SQL
injection attacts. 

*/
public static String sanitise( String unchecked )
{
	StringBuffer sb = new StringBuffer();
	int ch;
	int len = unchecked.length();

	for ( int i =0; i < len; i++ )
	{
		switch ( (ch = unchecked.charAt( i )) )
		{
		case '%':
			String hex = extractHex( unchecked, i ); i += 2;
			int    dec = Integer.decode( hex ).intValue();
			int    sub = substitute( dec );

			sb.append( "&#" + Integer.toString( sub ) + ";" );
			break;

		case '+':
			sb.append( ' ' );
			break;

		case '\"':
			sb.append( "&#34;" );
			break;

		case '\'':
			sb.append( "&#39;" );
			break;

		case '(':
			sb.append( "&#40;" );
			break;

		case ')':
			sb.append( "&#41;" );
			break;

		case '<':
			sb.append( "&#60;" );
			break;

		case '>':
			sb.append( "&#62;" );
			break;

		default:
			sb.append( (char) ch );
		}
	}

	return sb.toString();
}

/*
 *  Extracts an encoded hex value from the "unchecked" string
 *	and returns a hex string which can be used with Integer.decode:
 *	e.g. %3b -> "0x3b"
 */ 
private static String extractHex( String unchecked, int i )
{
	char a = unchecked.charAt( ++i );
	char b = unchecked.charAt( ++i );

	char[] data = { '0', 'x', a, b };

	return new String( data );
}

/*
 *	Hard code substitutions for non-standard character codes that
 *	are passed to the web server.
 *  For example Word open quotes 147 and close quotes 148 are
 *	substituted with 34 (").
 */
private static int substitute( int dec )
{
	int ret = dec;

	switch ( dec )
	{
	case 147:
	case 148:
		ret = 34;	//	translate open/close quotes to quotes.

	default:
	}
	return ret;
}






}
