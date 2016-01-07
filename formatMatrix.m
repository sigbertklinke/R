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
function format = formatMatrix (x, columnwise) 
  data  = getData(x);
  cdata = cellfun(@class, data, "UniformOutput", false);
  [rows, cols] = size(data);
  if (columnwise) 
    if (iscell(x)) 
      for i=1:cols
        for j=2:rows
          if (!strcmp(cdata{j,i}, cdata{1,i}));
	    error("different classes for entries");
          end
        end
      end
    end    
    for i=1:cols 
      if (ischar(data{1,i}))
        format{i} = "%s";
      elseif (islogical(data{1,i}))
        format{i} = "%d";
      else
        format{i} = formatNumeric(cell2mat({data{:,i}}));
      end
    end
  else
    if (iscell(x))
      for i=2:numel(data)
        if (!strcmp(cdata{1}, cdata{i}))
	    error("different classes for entries");
        end
      end
    end
    if (ischar(data{1}))
      format{1} = "%s";
    elseif (islogical(data{1}))
      format{1} = "%d";
    else
      format{1} = formatNumeric(cell2mat(data));
    end
  end
end 

function fmt = formatNumeric(x) 
  dg  = 3-floor(log10(max(max(x))-min(min(x))));
  if (dg>1)
    for j=dg:-1:0
      if (!sum(rem(round(10^j*x),10)!=0))
        dg--;
      end
    end
  end
  fmt = strcat("%.", num2str(dg), "f"); 
end

function data = getData (x)
  if (length(size(x))>2)
    error("two dimensional matrix or cell array expected");
  end
  if (ismatrix(x)) 
    data = num2cell(x);
  elseif (iscell(x))
    data = x;
  else
    error('matrix or cell array expected');
  end
end
