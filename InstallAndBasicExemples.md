To install this plugin, you need to retrieve two things :

- jpgraphmw.php from trunk (svn checkout http://jpgraphmw.googlecode.com/svn/trunk/ jpgraphmw)

- a fresh copy of jpgraph (http://www.aditus.nu/jpgraph/)

Put jpgraphmw.php into extension directory of mediawiki (/var/www/wiki/extensions/), create a sub directory for jpgraph (/var/www/wiki/extensions/jpgraph) and extract the jpgraph archive onto it.

After that, edit LocalSettings.php (/var/www/wiki/) and add the following line somewhere at the end of the file :
<pre>
require_once("$IP/extensions/jpgraph.php");<br>
</pre>

Now, everything are in place, you can try to use it.

You can check out this page on mediawiki to get an idea of what you can do with this plugin : http://www.mediawiki.org/wiki/Extension:Jpgraphmw