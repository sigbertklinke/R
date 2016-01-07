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
function outMatrix (x, varargin) 
  global rout;
  [data, colnames, rownames] = getData(x);
  [rows, cols] = size(data);
# defaults
  param = {
    { "title",               @string,                ""},
    { "caption",             @string,                ""},
    { "col",                 @stringvector,          colnames},
    { "row",                 @stringvector,          rownames},
    { "style.table",         @string,                 ""},
    { "style.caption",       @string,                ""},
    { "style.title",         @string,                "background-color:#999999;vertical-align:top;text-align:left;font-weight:bold;"},      
    { "style.row",           @stringvector,          {"background-color:#999999;vertical-align:top;text-align:right;font-weight:bold;"} },      
    { "style.col",           @stringvector,          {"background-color:#999999;vertical-align:top;text-align:right;font-weight:bold;"} },
    { "style.logical",       @stringmatrix,          {"background-color:#CCCCCC; vertical-align:top; text-align:right;"; "background-color:#FFFFFF; vertical-align:top; text-align:right;"}},
    { "style.numeric",       @stringmatrix,          {"background-color:#CCCCCC; vertical-align:top; text-align:right;"; "background-color:#FFFFFF; vertical-align:top; text-align:right;"}},
    { "style.char",          @stringmatrix,          {"background-color:#CCCCCC; vertical-align:top; text-align:left;"; "background-color:#FFFFFF; vertical-align:top; text-align:left;"}},
    { "format.title",        @string,                "%s"},
    { "format.row",          @stringvector,          {"%s"}},
    { "format.col",          @stringvector,          {"%s"}},
    { "format.logical",      @stringmatrix,          {"%d"}},
    { "format.numeric",      @stringmatrix,          {"%f"}},
    { "format.char",         @stringmatrix,          {"%s"}},
    { "type",                @string,                rout}
  }; 
# check parameters
  tag = cell2mat(param)(:,1);
  for i = 1:2:length(varargin)
    [tf, idx] = ismember(varargin{i}, tag);
    if (!any(tf)) 
       error(strcat('error: In "outMatrix": for "', varargin{idx} , '" is unknown'));
    end
    param{idx}{3} = param{idx}{2}(varargin{i+1}, varargin{i});
  end
# format data
  output      = cell(1+size(data));
  ostyle      = cell(1+size(data));
## title
  [tf, idx]   = ismember({"title", "style.title", "format.title"}, tag);
  params      = cell2mat(param)(:,3)(idx);
  output{1,1} = sprintf(params{3}, params{1});
  ostyle{1,1} = params{2};
## column headers
  [tf, idx]         = ismember({"col", "style.col", "format.col"}, tag);
  params            = cell2mat(param)(:,3)(idx);
  colv              = 1:cols;
  pos               = recycle(colv, cellfun("length", params));
  output(1, colv+1) = sprintf2({params{3}{pos(:,3)}}, {params{1}{pos(:,1)}});
  ostyle(1, colv+1) = params{2}{pos(:,2)};
## row headers
  [tf, idx]         = ismember({"row", "style.row", "format.row"}, tag);
  params            = cell2mat(param)(:,3)(idx);
  rowv              = 1:rows;
  pos               = recycle(rowv, cellfun("length", params));
  output(rowv+1,1)  = sprintf2({params{3}{pos(:,3)}}, {params{1}{pos(:,1)}});
  ostyle(rowv+1,1)  = params{2}{pos(:,2)};
## data
  [tf, nidx] = ismember({"style.numeric", "format.numeric"}, tag);
  nparams    = cell2mat(param)(:,3)(nidx);
  [tf, cidx] = ismember({"style.char",    "format.char"}, tag);
  cparams    = cell2mat(param)(:,3)(cidx);
  [tf, lidx] = ismember({"style.logical", "format.logical"}, tag);
  lparams    = cell2mat(param)(:,3)(lidx);
  for i=1:rows
    for j=1:cols
      dij = data{i,j};
      if (ischar(dij))   
        dparams = cparams;
      elseif (islogical(dij))
        dparams = lparams;
      else
        dparams = nparams;
      end
      pos1 = 1+rem([i,j]-1, size(dparams{1})); 
      pos2 = 1+rem([i,j]-1, size(dparams{2}));
      output{i+1,j+1} = sprintf(dparams{2}{pos2(1), pos2(2)}, dij);
      ostyle{i+1,j+1} = dparams{1}{pos1(1), pos1(2)};
    end
  end
## build text
  [rows, cols] = size(output);
  [tf, idx]    = ismember({"type"}, tag);
  type         =  param{idx}{3};
  [tf, idx]    = ismember({"style.table"}, tag);
  if (strcmp(type,"html"))  
    btable   = sprintf('<table style="%s">', param{idx}{3});
    btr      = "\n<tr>";
    btd      = "\n<td style=\"%s\">";
    bcaption = "\n<caption style=\"%s\">";
    ecaption = "</caption>";
    etd      = "</td>";
    etr      = "</tr>";
    etable   = "\n</table>";
  elseif (strcmp(type,"wiki"))
    btable   = sprintf('{| style="%s"', param{idx}{3});
    btr      = "\n|-";
    btd      = "\n| style=\"%s\" | ";
    bcaption = "\n|+ style=\"%s\" | ";
    ecaption = "";
    etd      = "";
    etr      = "";
    etable   = "\n|}";
  else 
    btable   = "\n";
    btr      = "";
    btd      = "";
    bcaption = "";
    ecaption = "\n";
    etd      = "";
    etr      = "\n";
    etable   = "";
    maxlen   = max(cellfun(@length, output));
    for i=1:rows
      output{i,1} = sprintf("%- *s", 1+maxlen(1), output{i,1});
      for j=2:cols
        output{i,j} = sprintf("% *s", 1+maxlen(j), output{i,j});
      end
    end
  end  
## build output text
  result = btable;
  [tf, idx] = ismember({"caption", "style.caption"}, tag);
  params     = cell2mat(param)(:,3)(idx);
  if (!isempty(params{1})) 
    result = [result, sprintf(bcaption, params{2}), params{1}, ecaption];
  end
  for i=1:rows
    result = [result, btr];
    for j=1:cols
      result = [result, sprintf(btd, ostyle{i,j}), output{i,j}, etd];
    end
    result = [result, etr];
  end
  result = [result, etable];
  disp(result);
end

function res = string (x, arg) 
  if (!ischar(x))
    error (strcat('For "', arg, '" string expected'));
  end
  res = x; 
end

function res = stringvector (x, arg) 
  if (iscell(x)) 
    if (ndims(x)>2)
      error (strcat('For "', arg, '" string vector expected'));
    end
    res = x;
  elseif (ischar(x))
    res = { x };
  end
  if (!ischar(res{1}))
    error (strcat('For "', arg, '" string vector expected'));
  end
end

function res = stringmatrix (x, arg) 
  if (iscell(x)) 
    res = {x{:}};
  elseif (ischar(x))
    res = { x };
  end
  if (!ischar(res{1}))
    error (strcat('For "', arg, '" string matrix expected'));
  end
end

function res = recycle (vec, max) 
  x = vec-1;
  res = 1+rem((vec-1)(:)*ones(1,length(max)), ones(length(vec),1)*max(:).' );
end

function res = sprintf2 (fmt, val) 
  for i=1:length(fmt)
    res{i} = sprintf(fmt{i}, val{i});
  end
end

function [data, colnames, rownames] = getData (x)
  if (length(size(x))>2)
    error("two dimensional matrix or cell array expected");
  end
  if (ismatrix(x)) 
    data = num2cell(x);
    bracket="()";
  elseif (iscell(x))
    data = x;
    bracket="{}";
  else
    error('matrix or cell array expected') 
  end
  for i=1:size(x)(2)
    colnames{i} = sprintf("%c,%d%c", bracket(1), i, bracket(2));
  end
  for i=1:size(x)(1)
    rownames{i} = sprintf("%c%d,%c", bracket(1), i, bracket(2));
  end
end
