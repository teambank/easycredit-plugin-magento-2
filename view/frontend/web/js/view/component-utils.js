define(function () {
  "use strict";

  var utils = {
    isValidEasyCreditEvent: function (e, expectedTagName, expectedPaymentType) {
      return (
        e instanceof CustomEvent &&
        e.target &&
        e.target.tagName.toLowerCase() === expectedTagName.toLowerCase() &&
        (!expectedPaymentType ||
          e.target.getAttribute("payment-type") === expectedPaymentType)
      );
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
    },
  };
  return utils;
});
