var Map = {

	// Initializes the map in the div.
	initialize: function(div) {
		// Start the google map.
		google.maps.event.addDomListener(window, 'load', function() {
			var map = null;
			var mapProp = {
				center: new google.maps.LatLng(34.156111, -118.131944),
				zoom: 13,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			Map._map = new google.maps.Map(div, mapProp);
		});
	},

	// Updates the map with the services from the Database. It takes a function serviceToHtml(service) which returns HTML that describes the service.
	update: function(services, serviceToHtml) {
		for(var i in Map._markers)
			Map._markers[i].setMap(null);
		Map._markers = [];
		Map.closeInfoWindow();
		Map._infoWindows = [];
		for(var i in services) {
			var service = services[i];
			var marker = new google.maps.Marker({
				map: Map._map,
				position: new google.maps.LatLng(service.lat, service.lon),
				title: service.name
			});
			Map._markers.push(marker);
			var infoWindow = new google.maps.InfoWindow({content: serviceToHtml(service)});
			Map._infoWindows[service.id] = infoWindow;
			marker.infoWindow = infoWindow;
			infoWindow.marker = marker;
			marker.addListener('click', function() {
				Map.closeInfoWindow();
				this.infoWindow.open(Map._map, this);
				Map._openInfoWindow = this.infoWindow;
			});
		}
	},

	// Opens the info window of a service. This will also close all other info windows.
	openInfoWindow: function(serviceId) {
		Map.closeInfoWindow();
		var infoWindow = Map._infoWindows[serviceId];
		infoWindow.open(Map._map, infoWindow.marker);
		Map._openInfoWindow = infoWindow;
	},

	// Closes the currently open info window.
	closeInfoWindow: function() {
		if(Map._openInfoWindow != null)
			Map._openInfoWindow.close();
	},

	updateSize: function() {
		if(Map._map != null)
			google.maps.event.trigger(Map._map, "resize");
	},

	_map: null,
	_markers: [],
	_infoWindows: [],
	_openInfoWindow: null
}