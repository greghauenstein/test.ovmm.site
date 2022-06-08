'use strict';

// Pulled from:
// https://stackoverflow.com/a/28054735

export let checkDomain = function(url) {
	if (url.indexOf('//') === 0) {
		url = location.protocol + url;
	}
	return url
		.toLowerCase()
		.replace(/([a-z])?:\/\//, '$1')
		.split('/')[0];
};

export let isExternal = function(url) {
	return (
		(url.indexOf('://') > -1 || url.indexOf('//') > -1) &&
		checkDomain(location.href) !== checkDomain(url)
	);
};

export let getParameterByName = function(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, '\\$&');
	var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, ' '));
};
