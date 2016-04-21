var downloads =
{
	// url - string
	// passFunction - function (string url, string text)
	// failFunction - function (string url, int status)
	add: function (url, passFunction, failFunction)
	{
		var download = {}
		download.url = url;
		download.passFunction = passFunction;
		download.failFunction = failFunction;
		this._queue.push(download);
		this._update();
	},

	// Internal

	_active: [],
	_queue: [],
	_maxActive: 10,
	_update: function()
	{
		if(this._active.length < this._maxActive && this._queue.length > 0)
		{
			var download = this._queue.shift();
			var request = new XMLHttpRequest();
			request.onreadystatechange = function ()
			{
				if(request.readyState == 4)
				{
					if(request.status == 200 || request.status == 0)
					{
						if(download.passFunction)
						{
							download.passFunction(download.url, request.responseText);
						}
					}
					else
					{
						if(download.failFunction)
						{
							download.failFunction(download.url, request.status);
						}
					}
					downloads._update();
				}
			}
			request.open("GET", download.url, true);
			request.send();
		}
	}
}
