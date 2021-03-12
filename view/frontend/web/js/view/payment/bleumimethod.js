/*browser:true*/
/*global define*/

define([
  "uiComponent",
  "Magento_Checkout/js/model/payment/renderer-list",
], function (Component, rendererList) {
  "use strict";

  rendererList.push({
    type: "bleumimethod",
    component:
      "Bleumi_PaymentAggregator/js/view/payment/method-renderer/bleumimethod-method",
  });

  return Component.extend({});
});
