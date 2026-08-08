export const ajaxLoad = (url, callback) => {
    let xhr = new XMLHttpRequest();
    xhr.addEventListener('load', function () {
        callback(this);
    });
    xhr.open('GET', url);
    xhr.send();
};

export const ajaxLoadToElement = (el, url, callback) => {
    ajaxLoad(url, function (response) {
        el.innerHTML = response.responseText;

        if (callback) {
            callback();
        }
    });
};

export const ajaxDrop = (url, deleteElementId, redirectUrl) => {
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }

    ajaxLoad(url, function (response) {
        if (response.status === 200) {
            if (deleteElementId) {
                document.getElementById(deleteElementId).remove();
            }

            if (redirectUrl) {
                document.location = redirectUrl;
            }
        }
    });
};

