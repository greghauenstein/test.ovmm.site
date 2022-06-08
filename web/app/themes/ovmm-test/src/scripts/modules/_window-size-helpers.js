'use strict';

// import {windowWidth, windowHeight, isMobile} from './modules/windowSizeHelper'

// Returns integer.
export let windowWidth = function() {
	return (
		window.innerWidth ||
		document.documentElement.clientWidth ||
		document.body.clientWidth
	);
};
export let windowHeight = function() {
	return (
		window.innerHeight ||
		document.documentElement.clientHeight ||
		document.body.clientHeight
	);
};

// Returns boolean.
export let isMobile = function() {
	return windowWidth() <= 1024;
};
export let isTablet = function() {
	return windowWidth() <= 1024 && windowWidth() > 699;
};
export let isPhone = function() {
	return windowWidth() <= 699;
};

export function outerWidth(el) {
	var width = el.offsetWidth;
	var style = getComputedStyle(el);

	width += parseInt(style.marginLeft) + parseInt(style.marginRight);
	return width;
}
