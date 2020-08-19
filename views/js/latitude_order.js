document.addEventListener("DOMContentLoaded", function () {

    // Make partial order refund in Order page in BO
    $(document).on('click', '#desc-order-partial_refund', function () {
        if ($('#doPartialRefundBraintree').length == 0) {
            var p, label, input; // Create checkbox for Braintree refund

            p = document.createElement('p');
            p.className = 'checkbox';
            label = document.createElement('label');
            label.setAttribute('for', 'doPartialRefundLatitude');
            input = document.createElement('input');
            input.type = 'checkbox';
            input.id = 'doPartialRefundLatitude';
            input.name = 'doPartialRefundLatitude'; // insert checkbox

            label.appendChild(input);
            label.appendChild(document.createTextNode(latitude_refund_js));
            p.appendChild(label);
            $('button[name=partialRefund]').parent('.partial_refund_fields').prepend(p);
        }
    });

});
