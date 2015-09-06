default: php

all: org_pagecentric_html org_pagecentric_multipart org_pagecentric_util org_pagecentric_pages

org_pagecentric_html:
	javac -g -d obj -classpath source/java source/java/org/pagecentric/html/Menu.java

org_pagecentric_multipart:
	javac -g -d obj -classpath source/java source/java/org/pagecentric/multipart/Multipart.java

org_pagecentric_util:
	javac -g -d obj -classpath source/java source/java/org/pagecentric/util/dictionary.java
	javac -g -d obj -classpath source/java source/java/org/pagecentric/util/string.java
	javac -g -d obj -classpath source/java source/java/org/pagecentric/util/printer.java

org_pagecentric_pages:
	javac -g -d obj -classpath source/java source/java/org/pagecentric/pages/Page.java

php:
	phpcompile > lib/autoload.php

