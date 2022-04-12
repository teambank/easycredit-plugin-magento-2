define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/method-list',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/full-screen-loader',
        'uiRegistry'
    ],
    function ($, Component, paymentMethods, customer, setPaymentInformation, additionalValidators, customerData, quote, storage, urlBuilder, fullScreenLoader, registry) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Netzkollektiv_EasyCredit/payment/easycredit',
                isAvailable: true,
                errorMessage: window.checkoutConfig.payment.easycredit.defaultErrorMessage,
                isPrefixValid: true,
                isAgreementChecked: false,
                isPrefixSelected: false,
                agreement: null,
                calculatedForGrandTotal: null,
                redirectAfterPlaceOrder: false
            },
            initObservable: function () {
                this._super()
                    .observe(
                        [
                            'isAvailable',
                            'errorMessage',
                            'isPrefixValid',
                            'isAgreementChecked',
                            'isPrefixSelected',
                            'agreement',
                            'calculatedForGrandTotal'
                        ]
                    );
                var me = this;

                this.checkAvailable();

                var prefixComponent = registry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.prefix');
                if (typeof prefixComponent !== 'undefined') {
                    prefixComponent.on('value',function(value){
                        me.checkAvailable();
                    });
                }

                var billingAddressComponent = registry.get(function(item){ 
                    return item.component == 'Magento_Checkout/js/view/billing-address';
                })
                if (typeof billingAddressComponent !== 'undefined') {
                    billingAddressComponent.isAddressDetailsVisible.subscribe(function(){
                        me.checkAvailable();
                    });
                }

                paymentMethods.subscribe(function(){
                    me.checkAvailable();
                });
                me.isAvailable.subscribe(function(){
                    fullScreenLoader.stopLoader(true);
                })
                return this;
            },
            getData: function () {
                var data = {
                    method: this.getCode(),
                    additional_data: {}
                };

                var prefix = $('#easycredit-customer-prefix').val();
                if (prefix) {
                    data.additional_data.customer_prefix = prefix;
                }
            
                return data;
            },
            getCheckoutMethod: function() {
                return customer.isLoggedIn() ? 'customer' : 'guest';
            },
            getIsAvailableUri: function(quote) {
                var method = this.getCheckoutMethod();

                var params = {};
                if (method == 'guest') {
                    params.quoteId = quote.getQuoteId();
                }
                var urls = {
                    'guest': '/easycredit/checkout/data/:quoteId',
                    'customer': '/easycredit/checkout/data'
                };
                var uri = urlBuilder.createUrl(urls[method], params);
                return uri;
            },
            checkAvailable: function() {
                var me = this;

                fullScreenLoader.startLoader();

                var uri = this.getIsAvailableUri(quote);

                storage.get(uri).done(function(data){
                    fullScreenLoader.stopLoader(true);

                    if (typeof data.error_message !== 'undefined') {
                        me.errorMessage(data.error_message);

                        var radioButton = $('#easycredit');

                        if (me.errorMessage() !== null && radioButton.prop('checked')) {
                            radioButton.prop('checked', false);
                        }

                        return me.isAvailable(false);
                    }
                    if (typeof data.agreement !== 'undefined') {
                        me.agreement(data.agreement);
                        me.isAvailable(true);
                    }
                    
                    me.isPrefixValid((typeof data.is_prefix_valid !== 'undefined' && data.is_prefix_valid));
                }).fail(function(){
                    me.isAvailable(false);
                    fullScreenLoader.stopLoader(true);
                });
                return true;
            },
            continueToEasyCredit: function () {
              if (additionalValidators.validate()) {
                this.selectPaymentMethod();
                setPaymentInformation(this.messageContainer, quote.paymentMethod()).done(
                  function () {
                    customerData.invalidate(['cart']);
                    $.mage.redirect(
                      window.checkoutConfig.payment.easycredit.redirectUrl
                    );
                  }
                );

                return false;
              }
            }
        });
    }
);
