window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.thelostasura = thelostasura;
window.__ = wp.i18n.__;
window.sprintf = wp.i18n.sprintf;

import oxygen from "./oxygen.js";
window.oxygen = oxygen;

window.location.hash = '/';