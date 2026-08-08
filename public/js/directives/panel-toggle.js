'use strict';

import { ajaxLoadToElement } from '../utils/ajax';

export default {
    mounted(el) {
        el.addEventListener('click', function () {
            const panel = el.closest('.card');
            const panelBody = panel.querySelector('.card-body');

            panelBody.classList.toggle('d-none');

            const icon = el.querySelector('.panel-toggle-icon');
            if (icon) {
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }

            // Load content if needed
            const contentUrl = panelBody.dataset.contentUrl;
            if (!panelBody.classList.contains('d-none') && contentUrl) {
                panelBody.innerHTML = 'Loading...';
                ajaxLoadToElement(panelBody, contentUrl);
                delete panelBody.dataset.contentUrl;
            }
        });
    }
};

