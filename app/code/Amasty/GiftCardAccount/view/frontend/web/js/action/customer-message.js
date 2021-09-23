define([], function () {
    'use strict';

    return function (isVisibleMessage, errorMessage) {
        return {
            setVisibleMessage: function (text) {
                isVisibleMessage(true);
                errorMessage(text);

                setTimeout(function () {
                    isVisibleMessage(false);
                    errorMessage('');
                }, 3000);
            }
        };
    };
});
