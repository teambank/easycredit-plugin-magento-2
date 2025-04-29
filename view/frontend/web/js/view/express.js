define([
  "jquery",
  "uiComponent",
  "Netzkollektiv_EasyCredit/js/view/component-utils",
  "Magento_Customer/js/customer-data",
], function ($, Component, componentUtils, customerData) {
  "use strict";

  return Component.extend({
    defaults: {
      priceBoxSelector: ".price-box",
      priceType: "finalPrice",
      element: null,
      storeCode: "default",
    },
    initialize: function (config, element) {
      this._super();

      this.element = element;
      this.fixCustomerDataModule();
      this.handlePaymentConfirm(element);

      this.handleProductPriceUpdate();
    },
    handleProductPriceUpdate: function () {
      if (!this.isInCatalogProduct) {
        return;
      }
      var priceBox = $(this.priceBoxSelector);
      priceBox.on("priceUpdated", this.onPriceChange.bind(this));
      priceBox.trigger("updatePrice");
    },
    onPriceChange: function (event, data) {
      this.element.setAttribute("amount", data[this.priceType].amount);
    },
    handlePaymentConfirm: function (element) {
      document.addEventListener("easycredit-submit", (e) => {
        if (e.target !== element) {
          return;
        }

        if (
          !componentUtils.isValidEasyCreditEvent(e, "easycredit-express-button")
        ) {
          return;
        }

        var form;

        const params = {
          ...e.detail,
          express: 1,
        };

        let buyForm = $(element).closest("form#product_addtocart_form").get(0);

        if (
          (form = componentUtils.replicateForm(buyForm, [
            { key: "easycredit[express]", value: true },
          ]))
        ) {
          this.addProductToCart(form, (data) => {
            this.startCheckout(data.quoteId, params);
          });
          return;
        }
        return this.startCheckout(null, params);
      });
    },
    addProductToCart: function (form, callback) {
      $.ajax({
        url: form.action,
        data: new FormData(form),
        type: "post",
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
          if (data.backUrl) {
            $.mage.redirect(data.backUrl);
            return;
          }
          callback(data);
        }.bind(this),
      });
    },
    startCheckout: function (quoteId, params) {
      var apiEndpoint =
        "/rest/" + this.storeCode + "/V1/easycredit/checkout/start";

      if (!quoteId) {
        quoteId = this.quoteId;
      }
      if (typeof quoteId === "string") {
        apiEndpoint += "/" + quoteId;
      }

      $.ajax({
        url: apiEndpoint,
        type: "post",
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        data: JSON.stringify({
          checkoutData: params,
        }),
        success: function (data) {
          if (data.redirect_url) {
            customerData.invalidate(["cart"]);
            $.mage.redirect(data.redirect_url);
          }
        },
        error: function (res) {
          var message = res.responseJSON;
          alert(message.message);
        },
      });
    },
    fixCustomerDataModule: function () {
      $.each($._data(document, "events").submit, function (index, listener) {
        if (listener.handler.toString().match(/getAffectedSections/)) {
          var h = listener.handler;
          listener.handler = function (e) {
            if (!e.target.method) {
              e.target.method = "no-method";
            }
            h(e);
          };
        }
      });
    },
  });
});
