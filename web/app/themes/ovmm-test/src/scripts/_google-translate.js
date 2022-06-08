import { windowWidth } from './modules/window-size-helpers';
import { debounce } from './modules/debounce';

// Bring in choices.
const choices = window.Choices;
const defaultValue = 'Translate';

function rafAsync() {
	return new Promise(resolve => {
		requestAnimationFrame(resolve); //faster than set time out
	});
}

function waitForElement(selector) {
	var element = document.querySelector(selector);

	if (element === null) {
		return rafAsync().then(() => waitForElement(selector));
	} else {
		return Promise.resolve(element);
	}
}

window.googleTranslateElementInit = function() {
	new google.translate.TranslateElement(
		{
			pageLanguage: 'en',
			includedLanguages:
				'en,ar,de,es,fr,hr,it,ja,ko,nl,pl,pt,ro,ru,sr,tl,vi,zh-CN,zh-TW'
		},
		'google_translate_element'
	);
};

// This will remove the "By Google" below the text.
function removeText() {
	// loop through all the nodes of the element
	var nodes = document.querySelector(
		'#google_translate_element .skiptranslate'
	).childNodes;

	for (var i = 0; i < nodes.length; i++) {
		var node = nodes[i];

		// if it's a text node, remove it
		if (node.nodeType == Node.TEXT_NODE) {
			node.parentNode.removeChild(node);
			i--;
		}
	}
}

function moveTranslate() {
	if (windowWidth() > 739) {
		if (document.querySelector('#google_translate_element-mobile > div')) {
			document
				.querySelector('#google_translate_element')
				.appendChild(
					document.querySelector(
						'#google_translate_element-mobile > div'
					)
				);
		}
	} else {
		if (document.querySelector('#google_translate_element > div')) {
			document
				.querySelector('#google_translate_element-mobile')
				.appendChild(
					document.querySelector('#google_translate_element > div')
				);
		}
	}
}

document.addEventListener('DOMContentLoaded', function() {
	waitForElement(
		'#google_translate_element select.goog-te-combo option'
	).then(function(element) {
		const eleTranslateSelect = document.querySelector(
			'#google_translate_element select.goog-te-combo'
		);

		// Convert text to specified value instead of default.
		element.innerText = defaultValue;

		let choicesSelect = new Choices(eleTranslateSelect, {
			searchEnabled: false,
			itemSelectText: ''
		});

		// Call method to remove the Google text below the select as it breaks the layout / makes it more difficult to style.
		removeText();
	});

	waitForElement('#google_translate element .goog-te-gadget span').then(
		function(element) {
			element.remove();
		}
	);

	waitForElement('#google_translate_element .choices__inner').then(function(
		element
	) {
		window.addEventListener(
			'resize',
			debounce(() => {
				moveTranslate();
			}, 500)
		);

		moveTranslate();
	});
});
