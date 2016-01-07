<?php
/* 
(C) 2006- Sigbert Klinke (sigbert@wiwi.hu-berlin.de), 

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

$commands = array('.C' =>              array(R_CMD_EXEC, '/(\W)?\.C[\W]*[\(\=\.]+/'),      
		  // .Call .Call.graphics
		  '.Call' =>           array(R_CMD_EXEC, '/(\W)?\.Call(\.graphics)?[\W]*[\(\=\.]+/'),
		  // .External .External.graphics
		  '.External' =>       array(R_CMD_EXEC, '/(\W)?\.External(\.graphics)?[\W]*[\(\=\.]+/'),
		  '.Fortran' =>        array(R_CMD_EXEC, '/(\W)?\.Fortran[\W]*[\(\=\.]+/'),  
	   // .readRDS .saveRDS 
		  'RDS' =>             array(R_CMD_IO, '/(\W)?\.(read|save)RDS[\W]*[\(\=\.]+/'),
		  '.Script' =>         array(R_CMD_EXEC, '/(\W)?\.Script[\W]*[\(\=\.]+/'),
		  // make.socket print.socket
		  '.socket' =>         array(R_CMD_IO, '/\b(make|print)\.socket[\W]*[\(\=\.]+/'),
		  // .Tcl .Tcl.args .Tcl.callback
		  '.Tcl' =>            array(R_CMD_UI, '/(\W)?\.Tcl(\.args|\.callback)?[\W]*[\(\=\.]+/'),
		  // .Tk.ID .Tk.newwin .Tk.subwin .Tkroot .Tkwin
		  '.Tk' =>             array(R_CMD_IO, '/(\W)?\.Tk(\.ID|\.newwin|\.subwin|root|win)[\W]*[\(\=\.]+/'),
		  'basename' =>        array(R_CMD_INFO, '/\bbasename[\W]*[\(\=\.]+/'),
		  'browseURL' =>       array(R_CMD_UI|R_CMD_EXEC, '/\bbrowseURL[\W]*[\(\=\.]+/'),
		  'bzfile' =>          array(R_CMD_IO, '/\bbzfile[\W]*[\(\=\.]+/'),
		  'capture.output' =>  array(R_CMD_IO, '/\bcapture\.output[\W]*[\(\=\.]+/'),
		  // close close.screen closeAllConnection socketConnection
		  'close' =>           array(R_CMD_IO, '/\bclose(\.screen|AllConnection)?[\W]*[\(\=\.]+/'),
		  // closeAllConnection getConnection showConnection socketConnection textConnection
		  'Connection' =>      array(R_CMD_IO, '/\b(closeAll|get|show|socket|text)Connection[\W]*[\(\=\.]+/'),
		  // data.entry data.restore
		  'data.' =>            array(R_CMD_UI, '/\bdata\.(entry|restore)[\W]*[\(\=\.]+/'),
		  'dataentry' =>        array(R_CMD_UI,'/\bdataentry[\W]*[\(\=\.]+/'),
		  'de' =>               array(R_CMD_UI,'/\bde[\W]*[\(\=\.]+/'),
		  // dev.control dev.copy2eps dev.cur dev.list dev.next 
		  // dev.prev dev.print dev.set
		  'dev.'  =>           array(R_CMD_IO, '/\bdev\.(control|copy2eps|cur|list|next|prev|print|set)[\W]*[\(\=\.]+/'),
		  'dev2bitmap' =>      array(R_CMD_IO, '/\bdev2bitmap[\W]*[\(\=\.]+/'),
		  'dget' =>            array(R_CMD_IO, '/\bdget[\W]*[\(\=\.]+/'),
		  // dir dir.create dirname
		  'dir' =>             array(R_CMD_IO, '/\bdir(\.create|name)?[\W]*[\(\=\.]+/'),
		  'do.call' =>         array(R_CMD_EXEC, '/\bdo\.call[\W]*[\(\=\.]+/'),
		  'download.file' =>   array(R_CMD_IO, '/\bdownfload\.file[\W]*[\(\=\.]+/'),
		  'dput' =>            array(R_CMD_IO, '/\bdput[\W]*[\(\=\.]+/'),
		  'dump' =>            array(R_CMD_IO, '/\bdump[\W]*[\(\=\.]+/'),
		  'dyn.load' =>        array(R_CMD_EXEC, '/\bdyn.load[\W]*[\(\=\.]+/'),
		  // edit edit.data.frame xedit
		  'edit' =>            array(R_CMD_UI, '/\b(x)?edit(\.data\.frame)?[\W]*[\(\=\.]+/'),
		  // emacs xemacs
		  'emacs' =>            array(R_CMD_UI | R_CMD_EXEC,'/\b(x)?emacs[\W]*[\(\=\.]+/'),
		  'erase.screen' =>     array(R_CMD_UI, '/\berase\.screen[\W]*[\(\=\.]+/'),
		  'example' =>          array(R_CMD_EXEC, '/\bexample[\W]*[\(\=\.]+/'),
		  'fifo' =>             array(R_CMD_IO, '/\bfifo[\W]*[\(\=\.]+/'),
		  // file file.access file.append file.append file.choose
		  // file.copy file.create file.exists file.info file.path
		  // file.remove file.rename file.show file.symlink
		  'file' =>             array(R_CMD_IO, '/\bfile(\.(access|append|choose|copy|create|exists|info|path|remove|rename|show|symlink))?[\W]*[\(\=\.]+/'),
		  'fix' =>              array(R_CMD_UI, '/\bfix[\W]*[\(\=\.]+/'),
		  'getwd' =>            array(R_CMD_INFO, '/\bgetwd[\W]*[\(\=\.]+/'),
		  'graphics.off' =>     array(R_CMD_UI, '/\bgraphics\.off[\W]*[\(\=\.]+/'),
		  'gzcon' =>            array(R_CMD_IO, '/\bgzcon[\W]*[\(\=\.]+/'),
		  'gzfile' =>           array(R_CMD_IO, '/\bgzfile[\W]*[\(\=\.]+/'),
		  'INSTALL' =>          array(R_CMD_ALL, '/\bINSTALL[\W]*[\(\=\.]+/'),
		  'jpeg' =>             array(R_CMD_IO, '/\bjpeg[\W]*[\(\=\.]+/'),
		  'library.dynam' =>    array(R_CMD_EXEC, '/\blibrary\.dynam[\W]*[\(\=\.]+/'),
		  'list.files' =>       array(R_CMD_INFO, '/\blist\.files[\W]*[\(\=\.]+/'),
		  'loadhistory' =>      array(R_CMD_IO, '/\bloadhistory[\W]*[\(\=\.]+/'),
		  'locator' =>          array(R_CMD_UI, '/\blocator[\W]*[\(\=\.]+/'),
		  'lookup.xport' =>     array('/\blookup.xport[\W]*[\(\=\.]+/'),
		  'menu' =>             array(R_CMD_UI, '/\bmenu[\W]*[\(\=\.]+/'),
		  'open' =>             array(R_CMD_IO, '/\bopen[\W]*[\(\=\.]+/'),
		  // install.packages remove.packages update.packages make.packages.html
		  '.packages' =>        array(R_CMD_IO, '/\b(install|remove|update|make)\.packages(\.html)?[\W]*[\(\=\.]+/'),
		  'parent.frame' =>     array('/\bparent\.frame[\W]*[\(\=\.]+/'),
		  'path.expand' =>      array('/\bpath.expand[\W]*[\(\=\.]+/'),
		  'pico' =>             array(R_CMD_UI | R_CMD_EXEC, '/\bpico[\W]*[\(\=\.]+/'),
		  'pictex' =>           array(R_CMD_IO, '/\bpictex[\W]*[\(\=\.]+/'),
		  'pipe' =>             array(R_CMD_IO, '/\bpipe[\W]*[\(\=\.]+/'),
		  'png' =>              array(R_CMD_IO, '/\bpng[\W]*[\(\=\.]+/'),
		  'postscript' =>       array(R_CMD_IO, '/\bpostscript[\W]*[\(\=\.]+/'),
		  // prompt promptData
		  'prompt' =>           array(R_CMD_IO, '/\bprompt(Data)?[\W]*[\(\=\.]+/'),
		  'quartz' =>           array(R_CMD_UI, '/\bquartz[\W]*[\(\=\.]+/'),
		  // R.home R.version
		  'R.' =>               array(R_CMD_INFO, '/\bR\.(home|version)[\W]*[\(\=\.]+/'),
		  // read.*
		  'read.' =>            array(R_CMD_IO, '/\bread\.(\w)+[\W]*[\(\=\.]+/'),
		  //readBin readline readLines
		  'read' =>             array(R_CMD_IO, '/\bread(Bin|line|Lines)[\W]*[\(\=\.]+/'),
		  'Rprof' =>            array(R_CMD_EXEC, '/\bRprof[\W]*[\(\=\.]+/'),
		  // save savehistory
		  'save' =>             array(R_CMD_IO, '/\bsave(history)?[\W]*[\(\=\.]+/'),
		  'scan' =>             array(R_CMD_IO, '/\bscan[\W]*[\(\=\.]+/'),
		  'screen' =>           array(R_CMD_UI, '/\bscreen[\W]*[\(\=\.]+/'),
		  'seek' =>             array(R_CMD_IO, '/\bseek[\W]*[\(\=\.]+/'),
		  'setwd' =>            array(R_CMD_IO, '/\bsetwd[\W]*[\(\=\.]+/'),
		  // sink sink.number
		  'sink' =>             array(R_CMD_IO, '/\bsink(\.number)?[\W]*[\(\=\.]+/'),
		  'source' =>           array(R_CMD_EXEC, '/\bsource[\W]*[\(\=\.]+/'),
		  'split.screen' =>     array(R_CMD_UI, '/\bsplit\.screen[\W]*[\(\=\.]+/'),
		  'stderr' =>           array(R_CMD_IO, '/\bstderr[\W]*[\(\=\.]+/'),
		  'stdin' =>            array(R_CMD_IO, '/\bstdin[\W]*[\(\=\.]+/'),
		  'stdout' =>           array(R_CMD_IO, '/\bstdout[\W]*[\(\=\.]+/'),
		  // Sys.*
		  'Sys.' =>             array(R_CMD_IO, '/\bSys\.[\w]+[\W]*[\(\=\.]+/'),
		  // sys.*
		  'sys.' =>             array(R_CMD_IO, '/\bsys\.[\w]+[\W]*[\(\=\.]+/'),
		  // system system.file
		  'system' =>           array(R_CMD_EXEC, '/\bsystem(\.file)?[\W]*[\(\=\.]+/'),
		  'tempfile' =>         array(R_CMD_IO, '/\btempfile[\W]*[\(\=\.]+/'),
		  'tkpager' =>          array(R_CMD_UI, '/\btkpager[\W]*[\(\=\.]+/'),
		  'tkStartGUI' =>       array(R_CMD_UI, '/\btkStartGUI[\W]*[\(\=\.]+/'),
		  'unlink' =>           array(R_CMD_IO, '/\bunlink[\W]*[\(\=\.]+/'),
		  'unz' =>              array(R_CMD_IO, '/\bunz[\W]*[\(\=\.]+/'),
		  // url url.show
		  'url' =>              array(R_CMD_IO, '/\burl(\.show)?[\W]*[\(\=\.]+/'),
		  'vi' =>               array(R_CMD_UI | R_CMD_EXEC, '/\bvi[\W]*[\(\=\.]+/'),
		  // write WriteBim writeLines
		  'write' =>            array(R_CMD_IO, '/\bwrite(Bin|Lines)?[\W]*[\(\=\.]+/'),
		  // write.*
		  'write.' =>           array(R_CMD_IO, '/\bwrite\.[\w]+[\W]*[\(\=\.]+/'),
		  'x11' =>              array(R_CMD_UI, '/\bx11[\W]*[\(\=\.]+/'),
		  'xfig' =>             array(R_CMD_UI, '/\bxfig[\W]*[\(\=\.]+/'),
		  'zip.file.extract' => array(R_CMD_IO, '/\bzip\.file\.extract[\W]*[\(\=\.]+/'),
		  # added by suggestion of M. Cassin 
		  'call' =>             array(R_CMD_EXEC, '/\bcall[\W]*[\(\=\.]+/'),
		  'eval' =>             array(R_CMD_EXEC, '/\beval[\W]*[\(\=\.]+/')
		  );

?>
