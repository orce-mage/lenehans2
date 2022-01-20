/**
 * Copyright © 2018 Magenest. All rights reserved.
 */
define([
    'jquery',
    'uiRegistry',
    'mage/translate',
    'ko',
    'Magenest_StripePayment/js/payment/country_region',
    'mage/cookies',
], function ($, uiRegistry, $t, ko, countryRegion) {
    'use strict';
    return function (config, elem) {
        require([
            'Magento_Customer/js/customer-data',
            'mage/storage',
            'mage/url'
        ], function (customerdata, storage, url) {
            if(config.is_catalog){
                window.stripe_button_in_catalog = config.is_catalog;
            }
            if(config.is_cart){
                window.stripe_button_in_cart = config.is_cart;
            }
            var id_selector = 'stripe_'+ Math.random().toString(16).slice(2);
            var jsondata = config.json_data;
            var isVirtualCart = true;
            var shipping_carrier = '', shipping_method = '';
            var isLoggedIn;

            $(elem).attr("id", id_selector);
            $.when(reloadData()).done(function () {
                isLoggedIn = function () {
                    var customer = customerdata.get('customer');
                    return (customer() && customer().firstname);
                };
                loadScript();
                // var cart = customerdata.get('cart');
                // if(cart().summary_count>0) {
                //     loadScript();
                // }
            });

            function reloadData() {
                var d = $.Deferred();
                var customer = customerdata.get('customer');
                var cart = customerdata.get('cart');
                var section = [];
                if(!customer()){
                    section.push("customer");
                }
                if(!cart()){
                    section.push("cart");
                }
                if(section.length>0){
                    customerdata.reload(section, false).done(function () {
                        d.resolve();
                    }).fail(function () {
                        d.reject();
                    });
                }else{
                    return d.resolve();
                }

                return d.promise();
            }

            async function loadScript() {
                await sleep(1000);
                if($('#'+id_selector).length>0) {
                    if (typeof Stripe === "undefined") {
                        $.ajax({
                            url: "https://js.stripe.com/v3/",
                            dataType: 'script',
                            success: function (result) {
                                requireLib(function () {
                                    initStripe();
                                });
                            }
                        });
                    }
                    else {
                        requireLib(function () {
                            initStripe();
                        });
                    }
                }
            }

            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            function requireLib(callback) {
                callback();
            }

            function updateRequestPayment() {
                paymentRequest.update(
                    {
                        currency: jsondata.currency_code,
                        total: {
                            label: $t('Shopping Cart'),
                            amount: getAmount(getTotals()),
                            pending: true
                        },
                        displayItems: getDisplayItems(),

                    }
                );
            }

            async function addToCartFunction() {
                await updateRequestPayment();
                paymentRequest.show();
            }

            var paymentRequest;
            function initStripe() {
                var self = this;
                var stripe = Stripe(jsondata.publishableKey);
                paymentRequest = stripe.paymentRequest({
                    country: jsondata.country_code,
                    currency: jsondata.currency_code,
                    total: {
                        label: $t('Shopping Cart'),
                        amount: getAmount(getTotals()),
                        pending: true
                    },
                    displayItems: getDisplayItems(),
                    requestShipping: true,
                    requestPayerName: true,
                    requestPayerEmail: true,
                    requestPayerPhone: true
                });
                var elements = stripe.elements();
                var prButton = elements.create('paymentRequestButton', {
                    paymentRequest: paymentRequest,
                    style: {
                        paymentRequestButton: {
                            type: jsondata.button_type,
                            theme: jsondata.button_theme,
                            height: '40px'
                        }
                    }
                });

                // Check the availability of the Payment Request API first.
                paymentRequest.canMakePayment().then(function (result) {
                    if (result) {
                        prButton.mount('#' + id_selector);
                    } else {
                        document.getElementById(id_selector).style.display = 'none';
                    }
                });

                prButton.on('click', function (ev) {
                    var cart = customerdata.get('cart');
                    if(cart().summary_count>0) {
                        updateRequestPayment();
                    }else{
                        ev.preventDefault();
                    }
                    // if($('#'+id_selector).length>0) {
                    //     if($(elem).closest('#product_addtocart_form').length > 0){
                    //         //in product detail page, click add to cart button
                    //         // ev.preventDefault();
                    //         $('#product-addtocart-button').trigger("click");
                    //
                    //         customerdata.get('cart').subscribe(function (cart) {
                    //             if($('#'+id_selector).length>0) {
                    //                 //call update total
                    //                 addToCartFunction();
                    //             }
                    //         })
                    //     }else {
                    //         //call update total
                    //
                    //     }
                    // }
                });

                paymentRequest.on('token', function (ev) {
                    $.when(setShippingInformationAction(ev, quoteId), setBillingInformationAction(ev, quoteId))
                        .done(function () {
                            placeOrderAction(ev, quoteId).done(function (result) {
                                console.log("done");
                                ev.complete('success');
                                window.location.replace(url.build('checkout/onepage/success/'));
                            }).fail(function (result) {
                                ev.complete('fail');
                            });
                        }).fail(function () {
                            ev.complete('fail');
                        }
                    );
                });

                paymentRequest.on('shippingaddresschange', function(ev) {
                    getShippingMethod(ev);
                });

                paymentRequest.on('shippingoptionchange', function(ev) {
                    changeShippingOption(ev);
                });

                if(customerdata.get('cart')().summary_count>0) {
                    $(elem).show();
                }else{
                    $(elem).hide();
                }

                customerdata.get('cart').subscribe(function (cart) {
                    if($('#'+id_selector).length>0) {
                        if(cart.summary_count>0) {
                            $(elem).show();
                        }else{
                            $(elem).hide();
                        }
                        //call update total
                        // paymentRequest.update(
                        //     {
                        //         currency: jsondata.currency_code,
                        //         total: {
                        //             label: $t('Shopping Cart'),
                        //             amount: getAmount(getTotals()),
                        //             pending: true
                        //         },
                        //         displayItems: getDisplayItems(),
                        //     }
                        // )
                    }
                })
            }

            function changeShippingOption(ev) {
                var shippingAmount = ev.shippingOption.amount;
                if(typeof shippingAmount !== 'undefined'){
                    var shippingId = ev.shippingOption.id;
                    var _arrResult = shippingId.split(" ");
                    shipping_carrier = _arrResult[0];
                    shipping_method = _arrResult[1];
                    ev.updateWith({
                        status: 'success',
                        displayItems: getDisplayItems(shippingAmount),
                        total: {
                            label: $t('Shopping Cart'),
                            amount: parseFloat(getAmount(getTotals())+shippingAmount),
                            pending: true
                        },
                    });
                }else{
                    ev.updateWith({
                        status: 'fail'
                    });
                }
            }

            function callGetShippingMethodApi(ev, quoteId) {
                var url = getUrlShippingMethodApi(quoteId);
                storage.post(
                    url,
                    JSON.stringify({
                        address: {
                            country_id: ev.shippingAddress.country,
                            postcode: ev.shippingAddress.postalCode,
                            region: ev.shippingAddress.region
                        }
                    })
                ).success(
                    function (result) {
                        var shippingOptions = [];
                        var cart = customerdata.get('cart')();
                        isVirtualCart = cart.is_virtual;
                        if(isVirtualCart){
                            shippingOptions.push({
                                'id': 'null',
                                'label':
                                    $t('You don\'t need to select a shipping method.'),
                                'amount': 0
                            });
                        }else {
                            for (var i = 0; i < result.length; i++) {
                                shippingOptions.push({
                                    'id': result[i]['carrier_code'] + ' ' + result[i]['method_code'],
                                    'label':
                                        result[i]['carrier_title'],
                                    'detail': result[i]['method_title'],
                                    'amount': getAmount(result[i]['amount'])
                                });
                            }
                            shipping_carrier = result[0]['carrier_code'];
                            shipping_method = result[0]['method_code'];
                        }
                        if(shippingOptions.length>0){
                            ev.updateWith({
                                status: 'success',
                                shippingOptions: shippingOptions,
                                displayItems: getDisplayItems(shippingOptions[0].amount),
                                total: {
                                    label: $t('Shopping Cart'),
                                    amount: parseFloat(getAmount(getTotals())+shippingOptions[0].amount),
                                    pending: true
                                },
                            });
                        }else{
                            ev.updateWith({
                                status: 'fail'
                            });
                        }

                    }
                ).fail(
                    function (result) {
                        console.log("fail");
                    }
                ).done(
                    function (result) {
                        //console.log(result);
                    }
                );
            }
            var quoteId;
            function getShippingMethod(ev) {
                if (window.stripe_button_in_cart){
                    require([
                        'Magento_Checkout/js/model/quote',
                    ], function (quote) {
                        quoteId = quote.getQuoteId();
                        callGetShippingMethodApi(ev, quoteId);
                    })
                }else{
                    $.get(
                        url.build("stripe/quote/getQuoteInfo"),
                        {
                            form_key: $.cookie('form_key')
                        },
                        function (result) {
                            quoteId = result.quote_id;
                            callGetShippingMethodApi(ev, quoteId);
                        },
                        "json"
                    )
                }
            }

            function getDisplayItems(shippingAmount) {
                var arr = [];
                var items = customerdata.get('cart')().items;
                if(typeof items !== 'undefined') {
                    items.forEach(function (v, i) {
                        arr.push({
                            amount: getAmount(v.product_price_value * v.qty),
                            label: v.qty + " " + v.product_name + " " + v.product_sku,
                            pending: true
                        })
                    });
                }
                if(typeof shippingAmount !== 'undefined'){
                    arr.push({
                        amount: shippingAmount,
                        label: $t('Shipping'),
                        pending: true
                    })
                }
                return arr;
            }

            function getAmount(amount) {
                if(!jsondata.isZeroDecimal){
                    amount=parseFloat(amount)*100;
                }
                return amount;
            }

            function getTotals() {
                var items = customerdata.get('cart')();
                if(items.subtotalAmount) {
                    return items.subtotalAmount;
                }else{
                    return 0;
                }
            }

            function getUrlShippingMethodApi(quoteId) {
                if(isLoggedIn()){
                    return "rest/default/V1/carts/mine/estimate-shipping-methods"
                }else{
                    return "rest/default/V1/guest-carts/"+quoteId+"/estimate-shipping-methods"
                }
            }

            function getUrlSetShippingInformation(quoteId) {
                if(isLoggedIn()){
                    return "rest/default/V1/carts/mine/shipping-information"
                }else{
                    return "rest/default/V1/guest-carts/"+quoteId+"/shipping-information"
                }
            }

            function getUrlSetBillingInformation(quoteId) {
                if(isLoggedIn()){
                    return "rest/default/V1/carts/mine/billing-address"
                }else{
                    return "rest/default/V1/guest-carts/"+quoteId+"/billing-address"
                }
            }

            function getUrlPlaceOrder(quoteId) {
                if(isLoggedIn()){
                    return "rest/default/V1/carts/mine/payment-information"
                }else{
                    return "rest/default/V1/guest-carts/"+quoteId+"/payment-information"
                }
            }

            function placeOrderAction(ev, quoteId) {
                var url = getUrlPlaceOrder(quoteId);
                var payload = {
                    cartId: quoteId,
                    billingAddress: getBillingAddress(ev),
                    paymentMethod: {
                        'method': 'magenest_stripe_applepay',
                        'additional_data': {
                            "stripe_response": JSON.stringify(ev.token)
                        }
                    },
                    email: ev.payerEmail
                };
                return storage.post(
                    url, JSON.stringify(payload)
                );
            }

            function getBillingAddress(ev) {
                var fullname = ev.payerName;
                var firstName = fullname.split(' ').slice(0, -1).join(' ');
                var lastName = fullname.split(' ').slice(-1).join(' ');
                if(firstName === ""){firstName = lastName;}
                return {
                    city: ev.token.card.address_city,
                    company: "",
                    countryId: ev.token.card.address_country,
                    firstname: firstName,
                    lastname: lastName,
                    postcode: ev.token.card.address_zip,
                    region: ev.token.card.address_state,
                    regionId: countryRegion.getRegionId(ev.token.card.address_country, ev.token.card.address_state),
                    street: [ev.token.card.address_line1],
                    telephone: ev.payerPhone
                }
            }

            function setBillingInformationAction(ev, quoteId) {
                var d = new $.Deferred();
                var url = getUrlSetBillingInformation(quoteId);
                storage.post(
                    url,
                    JSON.stringify({
                        address: getBillingAddress(ev),
                        cartId: quoteId
                    })
                ).success(
                    function (result) {
                        console.log(result);
                    }
                ).fail(
                    function (result) {
                        console.log("fail");
                        d.reject();
                    }
                ).done(
                    function (result) {
                        d.resolve();
                    }
                );
                return d.promise();
            }

            function setShippingInformationAction(ev, quoteId) {
                var d = new $.Deferred();
                var cart = customerdata.get('cart')();
                isVirtualCart = cart.is_virtual;
                if(isVirtualCart){
                    return ;
                }
                var url = getUrlSetShippingInformation(quoteId);
                var fullname = ev.payerName;
                var firstName = fullname.split(' ').slice(0, -1).join(' ');
                var lastName = fullname.split(' ').slice(-1).join(' ');
                if(firstName === ""){firstName = lastName;}
                storage.post(
                    url,
                    JSON.stringify({
                        addressInformation: {
                            billing_address: {
                                city: ev.token.card.address_city,
                                company: "",
                                countryId: ev.token.card.address_country,
                                firstname: firstName,
                                lastname: lastName,
                                postcode: ev.token.card.address_zip,
                                region: ev.token.card.address_state,
                                regionId: countryRegion.getRegionId(ev.token.card.address_country, ev.token.card.address_state),
                                street: [ev.token.card.address_line1],
                                telephone: ev.payerPhone
                            },
                            extension_attributes: {},
                            shipping_address: {
                                city: ev.shippingAddress.city,
                                company: "",
                                countryId: ev.shippingAddress.country,
                                firstname: firstName,
                                lastname: lastName,
                                postcode: ev.shippingAddress.postalCode,
                                region: ev.shippingAddress.region,
                                regionId: countryRegion.getRegionId(ev.shippingAddress.country, ev.shippingAddress.region),
                                street: ev.shippingAddress.addressLine,
                                telephone: ev.shippingAddress.phone
                            },
                            shipping_carrier_code: shipping_carrier,
                            shipping_method_code: shipping_method
                        }
                    })
                ).success(
                    function (result) {
                        console.log(result);
                    }
                ).fail(
                    function (result) {
                        console.log("fail");
                        d.reject();
                    }
                ).done(
                    function (result) {
                        d.resolve();
                    }
                );
                return d.promise();
            }

        });
    }
});
