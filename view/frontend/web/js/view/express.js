define(
    [
        'jquery',
        'uiComponent',
        'Netzkollektiv_EasyCredit/js/view/component-utils',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        Component,
        componentUtils,
        customerData
    ) {
        'use strict';

        return Component.extend(
            {
                defaults: {
                    priceBoxSelector: '.price-box',
                    priceType: 'finalPrice',
                    element: null
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
                    priceBox.on('priceUpdated', this.onPriceChange.bind(this));
                    priceBox.trigger('updatePrice');
                },
                onPriceChange: function(event, data) {
                    this.element.setAttribute('amount', data[this.priceType].amount);
                },
                handlePaymentConfirm: function (element) {
                    componentUtils.onHydrated(element, () => {
                        element.addEventListener("submit", (e) => {
                            var form;
                            let buyForm = $(element).closest('form#product_addtocart_form').get(0);
                            if (form = componentUtils.replicateForm(buyForm, [{key: 'easycredit-express-checkout', value: true}])) {
                                this.addProductToCart(form);
                                return;
                            }
                            return this.startCheckout();
                        });
                    });                    
                },
                addProductToCart: function (form) {
                    $.ajax({
                        url: form.action,
                        data: new FormData(form),
                        type: 'post',
                        dataType: 'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function(data) {
                            if (data.backUrl) {
                                $.mage.redirect(data.backUrl);
                                return;
                            }
                            this.startCheckout(data.quoteId);
                        }.bind(this)
                    });
                },
                startCheckout: function (quoteId) {
                    var apiEndpoint = '/rest/default/V1/easycredit/checkout/express';

                    if (!quoteId) {
                        quoteId = this.quoteId;
                    }
                    if (typeof quoteId === 'string') {
                        apiEndpoint += '/' + quoteId;
                    }

                    $.ajax({
                        url: apiEndpoint,
                        type: 'get',
                        contentType: "application/json; charset=utf-8",
                        dataType: 'json',
                        success: function (data) {
                            if (data.redirect_url) {
                                customerData.invalidate(['cart']);
                                $.mage.redirect(data.redirect_url);
                            }
                        },
                        error: function (res) {
                            var message = res.responseJSON;
                            alert(message.message);
                        }
                    });
                },
                fixCustomerDataModule: function () {
                    $.each($._data( document, "events" ).submit,function(index, listener){
                        if (listener.handler.toString().match(/getAffectedSections/)) {
                            var h = listener.handler;
                            listener.handler = function (e) {
                                if (!e.target.method) {
                                    e.target.method = 'no-method';
                                }
                                h(e);
                            }
                        }
                    });
                }
            }
        );
    }
);