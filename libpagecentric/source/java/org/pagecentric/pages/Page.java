package org.pagecentric.pages;

import org.pagecentric.util.*;

import java.io.*;

import java.util.*;

public class Page
{
	private printer out;

	private printer debug;

	private static HTTPContext context = HTTPContext.Singleton();

	private String pageDescription;

	private String pageKeywords;

	private String pageTitle;

	private String pageTemplate;

	private String pagePath;

	private String pageID;

	public Page()
	{
		this.out     = new printer( System.out );
		this.debug   = new printer( System.out );
	}

public String getRequest( String key )
{
	dictionary request = context.getRequest();

	if ( request.contains( key ) )
	{
		return request.get( key );
	}
	else
	{
		return new String();
	}
}

public String getPageID()
{
	return this.pageID;

}

public String getPagePath()
{
	return this.pagePath;
}

public String getPageTemplate()
{
	return this.pageTemplate;
}

public String getPageDescription()
{
	return this.pageDescription;
}

public String getPageKeywords()
{
	return this.pageKeywords;
}

public String getPageTitle()
{
	return (null != this.pageTitle) ? this.pageTitle : new String();
}

public void render()
{
	redirect( this.debug );
	presync( this.debug );

	headers( this.out );
	doctype( this.out );
	html   ( this.out );
}
public void redirect( printer debug )
{
	//	This method may be overridden to redirect to another page.
}

public void presync( printer debug )
{
	//	This method may be overriden.
}

public void headers( printer out )
{
	header( "Content-type: text/html\n\n" );
}

public void doctype( printer out )
{
	out.println( "<!DOCTYPE html>" );
}

public void htmlStart( printer out )
{
	out.println( "<html>" );
}

public void htmlEnd( printer out )
{
	out.println( "</html>" );
}

public void headStart( printer out )
{
	out.println( "<head>" );
}

public void headContent( printer out )
{
	title( out );
	meta( out );
	stylesheets( out );
	javascript( out );
}

public void headEnd( printer out )
{
	out.println( "</head>" );
}

public void title( printer out )
{
	String title = getPageTitle();

	out.println( "<title>" + title + "</title>" );
}

public void meta( printer out )
{
	//	This method may be overridden. This is it.
}

public void html( printer out )
{
	htmlStart( out );
	htmlContent( out );
	htmlEnd( out );
}

public void bodyStart( printer aPrinter )
{
	aPrinter.println( "<body style='overflow-y:scroll;'>" );
}

public void sync( printer debug )
{
	//	Override this method.
}

public void bodyContent( printer out )
{
	out.println( "<div>" );
	{
		bodyNavigation( out );
		bodyBackground( out );
		bodyBreadcrumbs( out );
		bodyHeader( out );
		bodyMiddle( out );
		bodyFooter( out );
	}
	out.println( "</div>" );
}

public void bodyEnd( printer out )
{
	out.println( "</body>" );
}

public void bodyNavigation( printer out )
{
}

public void bodyBackground( printer out )
{
}

public void bodyBreadcrumbs( printer out )
{
}

public void bodyHeader( printer out )
{
}

public void bodyMiddle( printer out )
{
	out.in( "<div id='middle'>" );
	{
		middleContent( out );
	}
	out.out( "</div>" );
}

public void bodyFooter( printer out )
{
	out.println( "</body>" );
}

public void htmlContent( printer out )
{
	headStart( out );
	headContent( out );
	headEnd( out );

	sync( debug );

	debug.writeBuffer();

	bodyStart( out );
	bodyContent( out );
	bodyEnd( out );
}

public void stylesheets( printer out )
{
}

public void javascript( printer out )
{
}

public void header( String aString )
{
	System.out.println( aString );
	System.out.println();
}

public void setTitle( String title )
{
	title = title;
}

public void middleContent( printer out )
{
}

public static HTTPContext getContext()
{
	return context;
}









































}
