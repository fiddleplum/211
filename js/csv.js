var csv =
{
	// filename - string
	// hasHeaders - boolean
	// passFunction - function (string url, object csv)
	// failFunction - function (string url, string error)
	load : function(filename, hasHeaders, passFunction, failFunction)
	{
		downloads.add(filename, function(url, text)
		{
			var data = []
			var i = 0;
			var lineNum = 1;
			while(i < text.length)
			{
				var o = csv._parseLine(text, i, lineNum);
				if(o.error != '')
				{
					failFunction(url, 'Error loading ' + filename + '. ' + o.error);
					return null;
				}
				var fields = o.fields;
				i = o.i;
				if(i == text.length && fields.length == 0)
				{
					break;
				}
				if(data.length > 0 && fields.length != data[0].length)
				{
					failFunction(url, 'Error loading ' + filename + '. On line ' + lineNum + ', there are an incorrect number of fields. ');
					return null;
				}
				if(hasHeaders && data.length > 0)
				{
					var fieldsH = {};
					for(var h = 0; h < data[0].length; h++)
					{
						fieldsH[data[0][h]] = fields[h];
					}
					data.push(fieldsH);
				}
				else
				{
					data.push(fields);
				}
				lineNum++;
			}
			if(hasHeaders && data.length > 0)
			{
				data.shift();
			}
			passFunction(url, data);
		}, function(url, status)
		{
			failFunction(url, 'Failed to download: ' + status + '. ');
		});
	},
	
	save: function (filename, object)
	{
	},
	
	// Internal

	// returns [array fields, int new_i]
	_parseLine : function(text, i, lineNum, failFunction, url)
	{
		var o = {};
		o.fields = [];
		o.i = i;
		o.error = '';
		var fieldNum = 1;
		var field = '';
		var quoted = false;
		while(o.i < text.length)
		{
			var c = text[o.i];
			var nc = (o.i + 1 < text.length ? text[o.i + 1] : '');
			if(c == '"')
			{
				if(field == '') // start of quoted field
				{
					quoted = true;
					o.i++;
				}
				else if(quoted)
				{
					if(o.i + 1 >= text.length || nc == '\n' || nc == '\r' || nc == ',') // end of quoted field
					{
						o.fields.push(field);
						field = '';
						fieldNum++;
						quoted = false;
						o.i++; // pass quote
						if(o.i + 1 >= text.length || nc == '\n' || nc == '\r') // end of line
						{
							while(o.i < text.length && (text[o.i] == '\n' || text[o.i] == '\r')) // get passed nl and cr
							{
								o.i++;
							}
							return o;
						}
						else // pass comma
						{
							o.i++;
						}
					}
					else if(nc == '"') // escaped quote
					{
						field += '"';
						o.i += 2;
					}
					else // lonely double quote
					{
						o.error = 'On line ' + lineNum + ', field ' + fieldNum + ', lonely double quote (escape with another quote). ';
						return o;
					}
				}
				else // double quote outside of double quoted field
				{
					o.error = 'On line ' + lineNum + ', field ' + fieldNum + ', invalid double quote (quote the field and escape this one). ';
					return o;
				}
			}
			else if(!quoted && (c == ',' || c == '\n' || c == '\r')) // end of field
			{
				o.fields.push(field);
				field = '';
				quoted = false;
				fieldNum++;
				if(c == '\n' || c == '\r') // end of line
				{
					while(o.i < text.length && (text[o.i] == '\n' || text[o.i] == '\r')) // get passed nl and cr
					{
						o.i++;
					}
					return o;
				}
				else
				{
					o.i++;
				}
			}
			else
			{
				field += c;
				o.i++;
			}
		}
		o.fields.push(field);
		return o;
	}
};