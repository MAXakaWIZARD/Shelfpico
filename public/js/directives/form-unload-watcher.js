'use strict';

export default {
    mounted(el) {
        var dirty = false;
        let form = el;

        let fields = form.querySelectorAll('input[type="text"], textarea, select');

        fields.forEach(function (field) {
            field.addEventListener('change', () => dirty = true);
            field.addEventListener('keydown', () => dirty = true);
        });

        form.addEventListener('submit', function () {
            dirty = false;
        });

        window.addEventListener("beforeunload", function (e) {
            if (dirty) {
                e.preventDefault();

                const message = "Form contains unsaved data, are you sure?";
                e.returnValue = message;

                return message;
            }
        });
    }
};
