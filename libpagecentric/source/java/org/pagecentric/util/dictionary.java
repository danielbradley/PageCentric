package org.pagecentric.util;

import java.util.*;


public class dictionary
{
private Map<String,String> m;


public dictionary()
{
	this.m = new HashMap<String,String>();
}
public boolean contains( String key )
{
	return m.containsKey( key );
}

public String get( String key )
{
	return m.get( key );
}

public void put( String key, String value )
{
	this.m.put( key, value );
}

public void print( printer out )
{
	Set<Map.Entry<String,String>>      set = m.entrySet();
	Iterator<Map.Entry<String,String>> it  = set.iterator();

	out.println( "<table style='width:100%; table-layout:fixed;'>" );

	while ( it.hasNext() )
	{
		Map.Entry<String,String> e = it.next();

		String key = e.getKey();
		String val = e.getValue();

		out.println( "<tr>" );
		out.println( "<td>" + key + "</td>" );
		out.println( "<td>" + val + "</td>" );
		out.println( "</tr>" );
	}

	out.println( "</table>" );
}





}
