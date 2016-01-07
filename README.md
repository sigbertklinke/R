# Purpose

The [R](https://www.r-project.org)  extension allows to integrate output (raw text, HTML and graphics) from [R](www.r-project.org) and [Octave](https://www.gnu.org/software/octave) programs, which are free software environments for statistical computing and graphics, on wiki pages.

# Usage

For the usage see [Extension:R](https://www.mediawiki.org/wiki/Extension:R) at the mediawiki web site and [my web page](http://mars.wiwi.hu-berlin.de/mediawiki/sk/index.php/R_Extension_for_MediaWiki).

# Installation

* Go to the `extensions` directory of your MediaWiki installation.
* Call `git clone https://github.com/sigbertklinke/R`
* Edit your `LocalSettings.php` and add at the end `include("$IP/extensions/R/R.php");`
* Create a directory `Rfiles` in the root of your MediaWiki installation and make it read- and writable for the webserver account

For detailled installation and and configuration instructions see [my web page](http://mars.wiwi.hu-berlin.de/mediawiki/sk/index.php/R_Extension_for_MediaWiki).


# Notes

1. The extension is potentially a security reason, since it runs R and/or Octave code embedded in a wiki page. The code could be malicious, especially when anonymous editing in your wiki is allowed.
2. The software is licensed under the GNU GENERAL PUBLIC LICENSE,                       Version 3, for details see [LICENSE](LICENSE).
