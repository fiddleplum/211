var currentDay = 0;
var currentTime = 0;

// Initializes everything required to run the web app.
function initialize(callback) {
	// Initialize the current time for use in the 'hours' section of the services to determine if that service is open now.
	var currentDate = new Date();
	currentDay = currentDate.getDay();
	var currentHours = currentDate.getHours();
	var currentMinutes = currentDate.getMinutes();
	var timeString = '';
	if(currentHours < 10)
		timeString += '0';
	timeString += currentHours;
	if(currentMinutes < 10)
		timeString += '0';
	timeString += currentMinutes;
	currentTime = parseInt(timeString);

	Database.initialize(callback);

	Map.initialize($('#map')[0]);
}

// [De]activates the menu.
function setMenuActive(active) {
	Map.closeInfoWindow();
	if(active) {
		$('#menu').fadeIn('fast');
		$('#menu_button')[0].src = 'images/map-reversed.svg';
	}
	else {
		$('#menu').fadeOut('fast');
		$('#menu_button')[0].src = 'images/menu.svg';
	}
}

// [De]activates the list.
function setListActive(active, doneFunction) {
	Map.closeInfoWindow();
	if(active) {
		$('#list').fadeIn('fast');
		$('#map').slideUp('fast', function() {
			Map.updateSize();
			if(doneFunction)
				doneFunction();
		});
		$('#listbar img')[0].src = 'images/down.svg';
		$('#listbar img')[1].src = 'images/down.svg';
		$('#listbar span')[0].innerHTML = 'Map';
	}
	else {
		$('#list').fadeOut('fast');
		$('#map').slideDown('fast', function() {
			Map.updateSize();
			if(doneFunction)
				doneFunction();
		});
		$('#listbar img')[0].src = 'images/up.svg';
		$('#listbar img')[1].src = 'images/up.svg';
		$('#listbar span')[0].innerHTML = 'List';
	}
}

var services = []
var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

function getTimeString(time) { // time is in the format HHMM, as a number
	var hour = Math.floor(time / 100);
	var minute = time % 100;
	var text = '';
	if(hour == 0)
		text += (hour + 12);
	else if(hour > 0 && hour <= 12)
		text += hour;
	else
		text += (hour - 12);
	if(minute > 0) {
		if(minute < 10)
			text += ':0' + minute;
		else
			text += ':' + minute;
	}
	if(hour < 12 || hour == 24)
		text += ' am';
	else
		text += ' pm';
	return text;	
}

// Returns nice html from the given service.
function serviceToHtml(service) {
	var html = '';
	html += '<h2>' + service.name + '</h2>';
	html += '<p><b>Description</b>: ' + service.short_description;
	if(service.long_description != '') {
		html += ' <button class="btn btn-expand-description" onclick="toggleLongDescription(' + service.id + ');">read more</button></p>';
		html += '<p class="long-description" id="long-description-' + service.id + '" style="display: none;">' + service.long_description + '</p>';
	}
	else {
		html += '</p>';
	}
	html += '<p><b>Address</b>: ' + service.address + '</p>';
	if(service.point_of_contact != '')
		html += '<p><b>Contact</b>: ' + service.point_of_contact + '</p>';
	if(service.phone_1 != '' && service.phone_2 == '' && service.phone_3 == '')
		html += '<p><b>Phone</b>: <a href="tel:' + service.phone_1 + '">' + service.phone_1 + '</a></p>';
	else if(service.phone_1 != '')
		html += '<p><b>Phone 1</b>: <a href="tel:' + service.phone_1 + '">' + service.phone_1 + '</a></p>';
	if(service.phone_2 != '')
		html += '<p><b>Phone 2</b>: <a href="tel:' + service.phone_2 + '">' + service.phone_2 + '</a></p>';
	if(service.phone_3 != '')
		html += '<p><b>Phone 3</b>: <a href="tel:' + service.phone_3 + '">' + service.phone_3 + '</a></p>';
	if(service.email_1 != '' && service.email_2 == '' && service.email_3 == '')
		html += '<p><b>E-mail</b>: <a href="mailto:' + service.email_1 + '">' + service.email_1 + '</a></p>';
	else if(service.email_1 != '')
		html += '<p><b>E-mail 1</b>: <a href="mailto:' + service.email_1 + '">' + service.email_1 + '</a></p>';
	if(service.email_2 != '')
		html += '<p><b>E-mail 2</b>: <a href="mailto:' + service.email_2 + '">' + service.email_2 + '</a></p>';
	if(service.email_3 != '')
		html += '<p><b>E-mail 3</b>: <a href="mailto:' + service.email_3 + '">' + service.email_3 + '</a></p>';
	if(service.website != '')
		html += '<p><b>Website</b>: <a href="http://' + service.website + '">' + service.website + '</a></p>';
	if(service.extra_info != '')
		html += '<p><b>Extra Info</b>: ' + service.extra_info + '</p>';
	if(service.hours.length > 0) {
		html += '<p><b>Hours</b>: ';
		for(day in service.hours) {
			html += dayNames[day] + ': ';
			if(service.hours[day][0] == 0 && service.hours[day][1] == 0)
				html += 'closed';
			else
				html += getTimeString(service.hours[day][0]) + ' to ' + getTimeString(service.hours[day][1]);
			if(currentDay == day && service.hours[day][0] <= currentTime && currentTime <= service.hours[day][1])
				html += " <b>Open Now</b>";
			html += '<br/>';
		}
		html += '</p>';
	}
	html += '<p><b>Categories</b>: ';
	for(var c in service.categories)
		html += service.categories[c] + ' ';
	html += '</p>';
	return html;
}

// Sets the category
function setCategory(category) {
	services = Database.getServicesByCategory(category);
	Map.update(services, serviceToHtml);
	updateList();
}

// Sets the services from a search term.
function setBySearch(search) {
	services = Database.getServicesBySearch(search);
	Map.update(services, serviceToHtml);
	updateList();
}

// Shows a service on the map.
function showServiceOnMap(serviceId) {
	setListActive(false, function() {
		Map.openInfoWindow(serviceId);
	});
}

// Updates the list.
function updateList() {
	var html = '';
  var noResults = '<p class="text-center">Sorry, it appears there were no results returned with those keywords</p>';
  if ( services.length < 1 ) {
    $('#list')[0].innerHTML = noResults;
  }
  else {
    for(var i in services) {
  		var service = services[i];
  		html += '<div class="item">';
  		html += '<div style="float: right;" onclick="showServiceOnMap(' + service.id + ');"> Map it: <img src="images/map.svg" style="width: 2em; height: 2em;"></div>';
  		html += serviceToHtml(service) + '</div>';
  	}
  	$('#list')[0].innerHTML = html;
  }
}

$(document).ready(function(){
  $('#google_translate_element').bind('DOMNodeInserted', function(event) {
    $('.goog-te-combo option:first').html('En');
    $('.goog-te-menu-frame.skiptranslate').load(function(){
      setTimeout(function(){
        $('.goog-te-menu-frame.skiptranslate').contents().find('.goog-te-menu2-item-selected .text').html('Translate');
      }, 100);
    });
  });
});

function toggleLongDescription(serviceId) {
  var $target = $('#long-description-' + serviceId);
  if (! $target.hasClass('visible')) {
    $target.fadeIn();
    $target.addClass('visible');
  }
  else {
    $target.fadeOut();
    $target.removeClass('visible');
  }


}

