'use strict';

import { ajaxDrop } from '../utils/ajax';

export default {
    mounted(el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();

            const url = el.dataset.url;
            const deleteElementId = el.dataset.deleteElementId || null;
            const redirectUrl = el.dataset.redirect || null;

            ajaxDrop(url, deleteElementId, redirectUrl);
        });
    }
};

