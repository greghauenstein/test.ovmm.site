// Simple debounce function
// Source: https://gist.github.com/vincentorback/9649034
/* Example:

import {debounce} from './modules/debounce'
window.addEventListener('resize', debounce(() => {  }, 500));

*/

export function debounce(fn, wait) {
	let timeout;
	return function() {
		clearTimeout(timeout);
		timeout = setTimeout(() => fn.apply(this, arguments), wait || 1);
	};
}
