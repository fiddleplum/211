// Service class used by every service.
var Service = function() {
	this.id = '';
	this.name = '';
	this.description = '';
	this.address = '';
	this.point_of_contact = '';
	this.phone_1 = '';
	this.phone_2 = '';
	this.phone_3 = '';
	this.email_1 = '';
	this.email_2 = '';
	this.email_3 = '';
	this.website = '';
	this.categories = [];
	this.lat = 0;
	this.lon = 0;
}

var Database = {

	// Initialize the database for future use.
	initialize: function (readyFunction) {
		csv.load('data/database.txt', true, function(url, data) {
			for(var i in data) {
				var service = new Service();
				service.id = i;
				service.name = data[i].name;
				service.description = data[i].description;
				service.address = data[i].address;
				service.point_of_contact = data[i].point_of_contact;
				service.phone_1 = data[i].phone_1;
				service.phone_2 = data[i].phone_2;
				service.phone_3 = data[i].phone_3;
				service.email_1 = data[i].email_1;
				service.email_2 = data[i].email_2;
				service.email_3 = data[i].email_3;
				service.website = data[i].website;
				service.categories = data[i].categories.toLowerCase().split(',');
				service.lat = data[i].lat;
				service.lon = data[i].lon;
				Database._services.push(service);
			}
			readyFunction();
		}, function(url, error) {
			console.log('While loading data. ' + error);
			readyFunction();
		});
	},

	// Returns a service with the given id.
	getServiceById: function (id) {
		for(var i in Database._services) {
			var service = Database._services[i];
			if(service.id == id)
				return service;
		}
		return null;
	},

	// Returns a list of Services in the given category.
	getServicesByCategory: function (category) {
		var services = [];
		for(var i in Database._services) {
			var service = Database._services[i];
			if(service.categories.indexOf(category) != -1)
				services.push(service);
		}
		return services;
	},

	// Returns a list of Services that are in the given search.
	getServicesBySearch: function (search) {
		search = search.toLowerCase();
		var services = [];
		for(var i in Database._services) {
			var service = Database._services[i];
			if(	   service.categories.indexOf(search) != -1
				|| service.name.toLowerCase().indexOf(search) != -1
				|| service.description.toLowerCase().indexOf(search) != -1
				|| service.address.toLowerCase().indexOf(search) != -1)
				services.push(service);
		}
		return services;
	},

	// Returns a list of all of the services.
	getAllServices: function () {
		return Database._services;
	},

	_services: []
}