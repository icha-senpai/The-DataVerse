!function (window, document, _undefined) {
    "use strict";

    XF.UIXImageUpload = XF.Element.newHandler({
        options: {},

        init: function () {
            XF.on(this.target, 'ajax-submit:response', this.ajaxResponse.bind(this));
        },

        ajaxResponse: function (e, data) {
            if (data.errors || data.exception) {
                return;
            }

            e.preventDefault();

            if (data.message) {
                XF.flashMessage(data.message, 3000);
            }

            document.querySelector(`[data-style-property-id="${data.stylePropertyId}"]`).value = data.file;
            XF.trigger(document.querySelector('.overlay'), XF.customEvent('overlay:hide', {bubbles: true}));
        }
    });

    XF.Element.register('uix-image-upload', 'XF.UIXImageUpload');
}(window, document);