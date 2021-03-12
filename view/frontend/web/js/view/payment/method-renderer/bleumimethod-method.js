/*browser:true*/
/*global define*/

define([
  "jquery",
  "Magento_Checkout/js/view/payment/default",
  "mage/url",
  "Magento_Customer/js/customer-data",
  "Magento_Checkout/js/model/error-processor",
], function ($, Component, url, customerData, errorProcessor) {
  "use strict";

  return Component.extend({
    redirectAfterPlaceOrder: false,

    defaults: {
      template: "Bleumi_PaymentAggregator/payment/bleumimethod",
    },

    getMailingAddress: function () {
      return window.checkoutConfig.payment.checkmo.mailingAddress;
    },

    getInstructions: function () {
      return window.checkoutConfig.bleumi.payment_options;
    },

    afterPlaceOrder: function () {
      var body = $("body").loader();

      try {
        body.loader("show");

        var custom_controller_url = url.build("bleumi/start/index");

        $.post(custom_controller_url, "json")
          .done(function (response) {
            window.location.href = response.redirectUrl;
          })
          .fail(function (response) {
            errorProcessor.process(response, this.messageContainer);
          })
          .always(function () {
            body.loader("destroy");
          });
      } catch (error) {
        window.console.error(error);
        window.console.log(error);
      }
    },

    getLogo: function () {
      return require.toUrl("Bleumi_PaymentAggregator/images/Bleumi.png");
    },
  });
});
