define(function () {
    'use strict';

    var utils = {
        /**
         * Check if easycredit component is hydrated
         *
         * @param {String} selector
         * @param {Function} cb
        * @return {Void}
         */
        onHydrated: function (element, cb) {    
            window.setTimeout(function() {
                if (typeof element === 'string') {
                  element = document.querySelector(element);
                }
                if (!element || !element.classList.contains('hydrated')) {
                    return utils.onHydrated(element, cb);
                }
                cb(element);
            }, 50)
        },
        replicateForm: function (buyForm, customParams) {
            if (!buyForm) {
              return false;
            }
            var form = document.createElement("form");
            form.setAttribute("action", buyForm.getAttribute("action"));
            form.setAttribute("method", "post");
            form.style.display = "none";
            var formData = new FormData(buyForm);

            if (customParams.length > 0) {
              for (let customParam of customParams) {
                formData.set(customParam.key, customParam.value);
              }
            }
            
            for (var key of formData.keys()) {
              let field = document.createElement("input");
              field.setAttribute("name", key);
              field.setAttribute("value", formData.get(key));
              form.append(field);
            }
            document.querySelector("body").append(form);
            return form;
          }
    }
    return utils;
});