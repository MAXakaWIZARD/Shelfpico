'use strict';

import { createApp, defineAsyncComponent } from 'vue';

// Import directives
import ajaxDeleteDirective from './directives/ajax-delete';
import panelToggleDirective from './directives/panel-toggle';
import submitInNewTabDirective from './directives/submit-in-new-tab';
import formUnloadWatcherDirective from './directives/form-unload-watcher';

const app = createApp({
    name: 'shelfpico-app'
});

// Register global components (needed for use in templates)
app.component('ProductsCover', defineAsyncComponent(() => import('./components/products-cover')));
app.component('EntityTagsEditor', defineAsyncComponent(() => import('./components/entity-tags-editor')));

// Register global directives
app.directive('ajax-delete', ajaxDeleteDirective);
app.directive('panel-toggle', panelToggleDirective);
app.directive('submit-in-new-tab', submitInNewTabDirective);
app.directive('form-unload-watcher', formUnloadWatcherDirective);

app.mount('#shelfpico-app');

