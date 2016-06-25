// The first level are called Topics and the second level are subtopics. Categories are the actual search terms.
var topics = {
	'Emergency': {
		'Police': 'police',
		'Fire': 'fire',
		'Hotlines': 'hotline',
		'Emergency Rooms': 'emergency room',
		'Urgent Care': 'urgent care',
		'Disaster Relief': 'relief' },
	'Basic Needs': {
		'Food': 'food',
		'Shelters': 'shelter',
		'Clothing': 'clothing' },
	'Health': {
		'Urgent Care': 'urgent care',
		'Mental Health': 'mental health',
		'Medical Services': 'medical',
		'Disability Services': 'disability',
		'Nutrition Education': 'nutrition',
		'Home Care': 'home care' },
	'Substance Abuse': {
		'Recovery Centers': 'substance abuse recovery',
		'Prevention Programs': 'substance abuse prevention' },
	'Violence & Abuse': {
		'Safe Houses': 'safe house',
		'Abuse Counseling': 'abuse counseling' },
	'Youth & Children': {
		'Tutoring': 'tutoring',
		'Mentoring': 'mentoring',
		'Abuse Counseling': 'abuse counseling',
		'Foster Care': 'foster care',
		'After School Programs': 'after school program' },
	'Jobs, Financial & Legal Aid': {
		'Financial Aid': 'financial aid',
		'Employment Services': 'employment',
		'Career Development': 'career development',
		'Legal Services': 'legal service' },
	'Education': {
		'Tutoring': 'tutoring',
		'Literacy Programs': 'literacy',
		'Public Schools': 'school',
		'Colleges': 'college',
		'Libraries': 'library',
		'Classes': 'class' },
};

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

// Activates and deactivates the menu.
function setMenuActive(active) {
	Map.closeInfoWindow();
	if(active) {
		$('#menu').fadeIn('fast');
		$('#list').fadeOut('fast');
	}
	else {
		$('#menu').fadeOut('fast');
	}
}

// This uses the topics and populates the #menu div with all of the topic menus.
function populateMenu() {
	var html = '';
	html += '<div id="main" class="topic" style="display: block;">';
	for(var topic in topics) {
		html += '<div class="icon" onclick="setActiveTopic(\'' + topic.replace(/ /g, '').replace(/&/g,'') + '\');"><img src="images/generic.svg"/><span>' + topic + '</span></div>';
	}
	html += '</div>';
	for(var topic in topics) {
		html += '<div id="' + topic.replace(/ /g, '').replace(/&/g, '') + '" class="topic" style="display: none;">';
		html += '<div class="icon" onclick="setActiveTopic(\'main\');"><img src="images/generic.svg"/><span>Back</span></div>';
		for(var subtopic in topics[topic]) {
			html += '<div class="icon" onclick="setCategories(topics[\'' + topic + '\'][\'' + subtopic + '\']); setMenuActive(false);"><img src="images/generic.svg"/><span>' + subtopic + '</span></div>';
		}
		html += '</div>';
	}
	$('#menu').html(html);
}

// This changes the topic that is shown to the user.
var activeTopic = 'main';
function setActiveTopic(topic) {
	if(activeTopic != '') {
		$('#' + activeTopic).fadeOut('fast', function() {
			$('#' + topic).fadeIn('fast');
		});
	}
	else {
		$('#' + topic).fadeIn('fast');
	}
	activeTopic = topic;
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
function setCategories(categories) {
	services = Database.getServicesByCategory(categories);
	Map.update(services, serviceToHtml);
	if (!($('#map')[0].style.display == 'block')) {
		setListActive(true);
	}
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
