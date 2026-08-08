import '../css/styles.scss';

require('../js/utils/ajax.js');

// Load directives before Vue instance
require('../js/directives/ajax-delete.js');
require('../js/directives/panel-toggle.js');
require('../js/directives/submit-in-new-tab.js');
require('../js/directives/form-unload-watcher.js');

// Create Vue instance
require('../js/app.js');
