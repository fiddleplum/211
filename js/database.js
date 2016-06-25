// Service class used by every service.
var Service = function() {
	this.id = '';
	this.name = '';
	this.short_description = '';
	this.long_description = '';
	this.address = '';
	this.point_of_contact = '';
	this.phone_1 = '';
	this.phone_2 = '';
	this.phone_3 = '';
	this.email_1 = '';
	this.email_2 = '';
	this.email_3 = '';
	this.website = '';
	this.hours = [];
	this.extra_info = '';
	this.spanish_short_description = '';
	this.spanish_long_description = '';
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
				service.short_description = data[i].short_description;
				service.long_description = data[i].long_description;
				service.address = data[i].address;
				service.point_of_contact = data[i].point_of_contact;
				service.phone_1 = data[i].phone_1;
				service.phone_2 = data[i].phone_2;
				service.phone_3 = data[i].phone_3;
				service.email_1 = data[i].email_1;
				service.email_2 = data[i].email_2;
				service.email_3 = data[i].email_3;
				service.website = data[i].website;
				var days = data[i].hours.toLowerCase().split(',');
				for(var day in days) {
					if(days[day] != '') {
						var hours = days[day].split('-');
						if(hours.length == 2) {
							service.hours[day] = []
							service.hours[day][0] = parseInt(hours[0]);
							service.hours[day][1] = parseInt(hours[1]);
						}
					}
				}
				service.extra_info = data[i].extra_info;
				service.spanish_short_description = data[i].spanish_short_description;
				service.spanish_long_description = data[i].spanish_long_description;
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

	// Returns a list of Services in all of the given categories array.
	getServicesByCategory: function (categories) {
		var services = [];
		for(var i in Database._services) {
			var service = Database._services[i];
			for(var j = 0; j < categories.length; j++) {
				if(service.categories.indexOf(categories[j] == -1))
					break;
			}
			if(j == categories.length) // it matched every category
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
				|| service.short_description.toLowerCase().indexOf(search) != -1
				|| service.long_description.toLowerCase().indexOf(search) != -1
				|| service.spanish_short_description.toLowerCase().indexOf(search) != -1
				|| service.spanish_long_description.toLowerCase().indexOf(search) != -1
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