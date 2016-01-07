# (C) 2006- Sigbert Klinke (sigbert@wiwi.hu-berlin.de)
#
# This file is part of R/Octave Extension for Mediawiki.
#
# The R/Octave Extension is free software: you can 
# redistribute it and/or modify it under the terms of 
# the GNU General Public License as published by the
# Free Software Foundation, either version 3 of the 
# License, or (at your option) any later version.
#
# The R/Octave Extension is distributed in the hope that 
# It will be useful, but WITHOUT ANY WARRANTY; without 
# even the implied warranty of MERCHANTABILITY or 
# FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
# Public License for more details.
#
# You should have received a copy of the GNU General 
# Public License along with the R/Octave Extension. 
# If not, see http://www.gnu.org/licenses/.
#
#format$tab
#format$col
#format$row
#format$cell
#format$title
#
#style$caption=NULL+
#style$table=style+
#style$col=cstyle+
#style$row=rstyle+
#style$cell=matrix(c(ostyle, estyle), nr=2, nc=1)+
#style$title=tstyle
#
#title
#caption
#

outMatrix <- function(x, title=NULL, caption=NULL, format=list(), style=list(), names=list(), type=NULL) {
  getData <- function(x) {
    data <- NULL;
    coln <- colnames(x);
    rown <- rownames(x);
    if (is.data.frame(x)) {
      data <- as.list(x);
      if (is.null(coln)) coln <-sprintf("V%i", 1:ncol(x)); 
      if (is.null(rown)) rown <-sprintf("%i",  1:nrow(x)); 
    }
    if (is.matrix(x)) { 
      data <- lapply(seq_len(ncol(x)), function(i) x[,i]);
      if (is.null(coln)) coln <-sprintf("[,%i]", 1:ncol(x)); 
      if (is.null(rown)) rown <-sprintf("[%i,]", 1:nrow(x)); 
    }
    if (is.null(data)) {
      data <- as.matrix(x)
      if (is.null(coln)) coln <-sprintf("[,%i]", 1:ncol(data)); 
      if (is.null(rown)) rown <-sprintf("[%i,]", 1:nrow(data)); 
      data <- lapply(seq_len(ncol(data)), function(i) data[,i]);
    }
    if (is.null(data)) stop("two dimensional array or data frame expected");
    return(list(data=data, colnames=coln, rownames=rown));  
  }
    
  recycle <- function (vec, max) { return (1+(vec-1)%%max); }
  
  gd = getData(x);
  # defaults and check
  param                = list();
  param$title          = as.character(if (is.null(title)) "" else title);
  param$caption        = as.character(if (is.null(caption)) "" else caption);
  param$col            = as.vector(if (is.null(names$col)) gd$colnames else names$col, "character");
  param$row            = as.vector(if (is.null(names$row)) gd$rownames else names$row, "character");  
  param$style.table    = as.character(if (is.null(style$table)) "" else style$table);
  param$style.caption  = as.character(if (is.null(style$caption)) "" else style$caption); 
  param$style.title    = as.character(if (is.null(style$caption)) "background-color:#999999;vertical-align:top;text-align:left;font-weight:bold;" else style$caption);
  param$style.row      = as.vector(if (is.null(style$row)) "background-color:#999999;vertical-align:top;text-align:right;font-weight:bold;" else style$row);
  param$style.col      = as.vector(if (is.null(style$col)) "background-color:#999999;vertical-align:top;text-align:right;font-weight:bold;" else style$col);
  param$style.logical  = as.matrix(if (is.null(style$logical)) c("background-color:#CCCCCC; vertical-align:top; text-align:right;", "background-color:#FFFFFF; vertical-align:top; text-align:right;") else style$logical);
  param$style.numeric  = as.matrix(if (is.null(style$numeric)) 
                                     if (is.null(style$cell)) c("background-color:#CCCCCC; vertical-align:top; text-align:right;", "background-color:#FFFFFF; vertical-align:top; text-align:right;") 
                                     else style$cell
                                   else style$numeric);
  param$style.char     = as.matrix(if (is.null(style$char)) c("background-color:#CCCCCC; vertical-align:top; text-align:right;", "background-color:#FFFFFF; vertical-align:top; text-align:left;") else style$char);
  param$format.title   = as.character(if (is.null(format$title)) "%s" else format$title);                             
  param$format.row     = as.vector(if (is.null(format$row)) "%s" else format$row, "character");                             
  param$format.col     = as.vector(if (is.null(format$col)) "%s" else format$col, "character");                             
  param$format.logical = as.matrix(if (is.null(format$logical)) "%d" else format$logical);
  param$format.numeric = as.matrix(if (is.null(format$numeric)) 
                                     if (is.null(format$cell)) "%f" else format$cell
                                   else format$numeric);
  param$format.char    = as.matrix(if (is.null(format$char)) "\"%s\"" else format$char);
# format data                                    
  cols   <- length(gd$data);
  colv   <- 1:cols;
  rows   <- length(gd$data[[1]]);
  rowv   <- 1:rows;
  output <-  ostyle <-matrix("", nrow=1+rows, ncol=1+cols);
## title    
  output[1,1] <- sprintf(param$format.title, param$title);
  ostyle[1,1] <- param$style.title;
## column headers
  pos  <- recycle(colv, length(param$col));                     
  posf <- recycle(colv, length(param$format.col)); 
  poss <- recycle(colv, length(param$style.col));
  output[1,1+colv] <- sprintf(param$format.col[posf], param$col[pos]);
  ostyle[1,1+colv] <- param$style.col[poss];
## row headers
  pos  <- recycle(rowv, length(param$row));                     
  posf <- recycle(rowv, length(param$format.row)); 
  poss <- recycle(rowv, length(param$style.row));
  output[1+rowv,1] <- sprintf(param$format.row[posf], param$row[pos]);
  ostyle[1+rowv,1] <- param$style.row[poss];
## table
  for (i in colv) {
    di   <- gd$data[[i]];
    niv  <- 1:length(di);
    typei <- typeof(di[1]);
    if (typei=="logical") {
      posf <- recycle(niv, nrow(param$format.logical));         
      posi <- recycle(colv, ncol(param$format.logical))[i];         
      output[1+rowv,i+1] <- sprintf(param$format.logical[posf,posi], di);    
      poss <- recycle(niv, nrow(param$style.logical));   
      posi <- recycle(colv, ncol(param$style.logical))[i];         
      ostyle[1+rowv,i+1] <- param$style.logical[poss,posi];
    } else if (typei=='character') {
      posf <- recycle(niv, nrow(param$format.char));         
      posi <- recycle(colv, ncol(param$format.char))[i];         
      output[1+rowv,i+1] <- sprintf(param$format.char[posf,posi], di);    
      poss <- recycle(niv, nrow(param$style.char));   
      posi <- recycle(colv, ncol(param$style.char))[i];     
      ostyle[1+rowv,i+1] <- param$style.char[poss,posi];     
    } else if ((typei=='double')||(typei=='integer')) {
      posf <- recycle(niv, nrow(param$format.numeric));         
      posi <- recycle(colv, ncol(param$format.numeric))[i];        
      output[1+rowv,i+1] <- sprintf(param$format.numeric[posf,posi], as.double(di));    
      poss <- recycle(niv, nrow(param$style.numeric));   
      posi <- recycle(colv, ncol(param$style.numeric))[i];      
      ostyle[1+rowv,i+1] <- param$style.numeric[poss,posi];
    } else stop(paste('unexpected data type "', typei, '"', sep=''));
  }
## build text
  if (is.null(type)) {
    type <- "rout not found";
    if (exists('rout', envir=.GlobalEnv)) type <- get('rout', envir=.GlobalEnv);
  }
  rows <- nrow(output);
  cols <- ncol(output);
  if (type=="html") {  
    btable   <-  sprintf('<table style="%s">', param$style.table);
    btr      <- "\n<tr>";
    btd      <- "\n<td style=\"%s\">";
    bcaption <- "\n<caption style=\"%s\">";
    ecaption <- "</caption>";
    etd      <- "</td>";
    etr      <- "</tr>";
    etable   <- "\n</table>\n";
  }
  else if (type=="wiki") {
    btable   <- sprintf('{| style="%s"', param$style.table);
    btr      <- "\n|-";
    btd      <- "\n| style=\"%s\" | ";
    bcaption <- "\n|+ style=\"%s\" | ";
    ecaption <- "";
    etd      <- "";
    etr      <- "";
    etable   <- "\n|}\n";
  }
  else { 
    btable   <- "\n";
    btr      <- "";
    btd      <- "";
    bcaption <- "";
    ecaption <- "\n";
    etd      <- "";
    etr      <- "\n";
    etable   <- "\n";
    maxlen   <- apply(nchar(output), 2, max);
    for (i in 1:cols) output[,i] <- sprintf("% *s", 1+maxlen[i], output[,i])      
    output[1,1] <- sprintf("%-*s", 1+maxlen[1], output[1,1])      
  }
# build output text
  result <- btr;
  for (i in 1:cols) result <- paste(result, sprintf(btd, ostyle[,i]), output[,i], etd, sep='');
  result <- paste(paste(result, etr, sep=''), sep='', collapse="");
  result <- paste(btable,  if (nchar(param$style.caption)) paste(result, sprintf(bcaption, param$style.caption), param$caption, ecaption, sep='') else "", result, etable, sep='');
  cat(result);
}

out.Object <- function(html, x, type, title=NULL, caption=NULL, format=NULL, style=NULL) {
  outMatrix(x, title=title, caption=caption, format=if(is.null(format)) list() else format, style=if(is.null(style)) list() else style)
}

out.HTML <- function (html, x, title=NULL, caption=NULL, format=NULL, style=NULL) {
  outMatrix (x, title=title, caption=caption,  format=if(is.null(format)) list() else format, style=if(is.null(style)) list() else style)
}

out.Wiki <- function (x, title=NULL, caption=NULL, format=NULL, style=NULL) {
  outMatrix (x, title=title, caption=caption, format=if(is.null(format)) list() else format, style=if(is.null(style)) list() else style)
}

outHTML <- function (html, x, title='', 
                     style ='width:100%;', 
                     tstyle='background-color:#BBBBBB; vertical-align:top; text-align:left;', 
                     cstyle='background-color:#BBBBBB; vertical-align:top; text-align:right;', 
                     rstyle='background-color:#BBBBBB; vertical-align:top; text-align:right;', 
                     ostyle='background-color:#FFFFFF; vertical-align:top; text-align:left;',
                     estyle='background-color:#CCCCCC; vertical-align:top; text-align:left;',
                     ...) {
  param = list(table=style, title=tstyle, col=cstyle, row=rstyle, char=c(ostyle, estyle), numeric=c(ostyle, estyle), logical=c(ostyle, estyle)); 
  outMatrix(x, title, style=ohstyle, ...)
}

trellisSK <- function (file, ...) {
  trellis.device ("pdf", file=file, ...)
} 

readdataSK <- function (name, format = "csv", ...) {
  fullname <- list.files(pattern=name, recursive=TRUE, full.names=TRUE)
# if filename not unique then return nothing !!
  if (length(fullname)>1) {
    stop("More than one file found", fullname)
  }
  switch (format,
    csv   = read.csv (file=fullname, ...),
    csv2  = read.csv2 (file=fullname, ...),
    table = read.table (file=fullname, ...),
    txt   = read.table (textConnection(name), ...),
    default = NULL)
}

pdf <- function (file, ...) {
  grDevices::pdf (sprintf (rpdf, rpdfno), ...)
}

dev.off <- function(which = dev.cur()) {
  grDevices::dev.off(which)
  cat (sprintf("<img src=\"%s\">\n", sprintf (rpdf, rpdfno)))
  rpdfno <<- rpdfno+1
}
