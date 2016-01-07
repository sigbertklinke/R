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
PS4("octave> ");
figure("visible", "off");
global handle_print = @print;

function print (varargin)
  global rpdf;
  global rpdfno;
  global handle_print;
  full = {'-mono', '-color', '-solid', '-dashed'};
  par2 = {'-S', '-F', '-r'};
  handle = gcf();
  j=2; 
  for i=1:nargin 
    if (ishandle(varargin{i}))
      handle = varargin{i};
    elseif (ischar(varargin{i}))
      if (any(strcmp(varargin{i}, full)) ||
          any(strncmp(varargin{i}, par2, 2)))
        param{j} = varargin{i};   
        j++;
      end
    end
  end
  param{1} = sprintf(rpdf, rpdfno);
  rpdfno++;
  handle_print (param{:});
  disp(['<img src="', param{1}, '">']);
end 

