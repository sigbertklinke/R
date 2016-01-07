<?php
/* 
(C) 2006- Sigbert Klinke (sigbert@wiwi.hu-berlin.de), 
 Markus Cozowicz, Alex Browne, Michael Cassin

This file is part of R/Octave Extension for Mediawiki.

The R/Octave Extension is free software: you can 
redistribute it and/or modify it under the terms of 
the GNU General Public License as published by the
Free Software Foundation, either version 3 of the 
License, or (at your option) any later version.

The R/Octave Extension is distributed in the hope that 
It will be useful, but WITHOUT ANY WARRANTY; without 
even the implied warranty of MERCHANTABILITY or 
FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
Public License for more details.

You should have received a copy of the GNU General 
Public License along with the R/Octave Extension. 
If not, see http://www.gnu.org/licenses/.
*/

abstract class SpecialPage {
  // makes PHP and Sigbert happy :)
};

// Make sure that current directory is the root directory of the wiki!
chdir('..');
require_once (getcwd() . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'R' . DIRECTORY_SEPARATOR . 'R.php');
echo(Engine::renderCGI($_POST));

?>
