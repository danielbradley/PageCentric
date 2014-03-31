package org.pagecentric.util;

import java.io.PrintStream;


public class printer
{
private PrintStream s;

private int tabs;

private boolean buffering;

private StringBuffer buffer;


public printer( PrintStream stream )
{
	this.tabs      = 0;
	this.buffering = false;
	this.s         = stream;
	this.buffer    = new StringBuffer();
}

public void println( String aString )
{
	for ( int i=0; i < tabs; i++ ) writeout( "\t" );

	writeout( aString );
	writeout( "\n" );
}

private void writeout( String aString )
{
	if ( buffering )
	{
		buffer.append( aString );
	}
	else
	{
		s.print( aString );
	}
}

public void writeBuffer()
{
	s.print( buffer.toString() );

	buffer = new StringBuffer();

	buffering = false;
}

public void indent(  )
{
	tabs++;
}

public void outdent(  )
{
	tabs--;
}

public void in( String aString )
{
	inprint( aString );
}

public void out( String aString )
{
	outprint( aString );
}

public void outprint( String aString )
{
	outdent();
	println( aString );
}
public void printf( String aString )
{
	for ( int i=0; i < tabs; i++ ) writeout( "\t" );

	writeout( aString );
}

public void inprint( String aString )
{
	println( aString );
	indent();
}


















}
