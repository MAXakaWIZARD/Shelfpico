'use strict';

export default {
    mounted(el) {
        el.addEventListener('click', function () {
            const form = el.closest('form');
            if (form) {
                form.setAttribute('target', '_blank');
                form.submit();
            }
        });
    }
};

