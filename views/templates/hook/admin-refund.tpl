{if $custom_refund && $available_amount}
    <div class="btn latitudeRefund" id="refundAction" style="display: none; border: 1px #ccc solid; cursor: pointer">
        <span class="refundBoxLabel">
            <i class="icon-exchange"></i> {l s=$paymenet_gateway_name} Refund
        </span>
        <div id="latitudeRefundBox" style="display: none;">
            <input name="refund_amount" type="text" placeholder="Refund amount" value="{l s=$available_amount }" data-maximum="{l s=$available_amount }">
            <button class="btn  btn-refund" style="margin: 0 2px; border: 1px #ccc solid" data-return_url="{$refund_url}" data-query="{$query_data}">Refund</button>
            <button class="btn btn-cancel" style="border: 1px #ccc solid">Cancel</button>
        </div>
    </div>
{/if}

{literal}
<script type="text/javascript">
    ;(function($, window, document) {
        if ($('#refundAction').length > 0) {
            var origPartialBtn = $('#desc-order-partial_refund');
            $(origPartialBtn).hide().after($('#refundAction').show());
            var latitudeRefundBoxContainer = $("#refundAction");
            var refundAmountInput = latitudeRefundBoxContainer.find("input[name='refund_amount']");
            var refundBtn = latitudeRefundBoxContainer.find(".btn-refund");

            latitudeRefundBoxContainer.on("click", function () {
                latitudeRefundBoxContainer.find(".refundBoxLabel").hide();
                $("#latitudeRefundBox").css('display', 'flex');
            });
            latitudeRefundBoxContainer.find(".btn-cancel").on("click", function (e) {
                e.stopPropagation();
                latitudeRefundBoxContainer.find(".refundBoxLabel").show();
                $("#latitudeRefundBox").css('display', 'none');
            });
            refundBtn.on("click", function (e) {
                e.stopPropagation();
                var refundBtn = $(this);
                if (!refundBtn.prop('disabled')) {
                    if (isValidRefundAmount(refundAmountInput)) {
                        var returnUrl = refundBtn.data('return_url');
                        var queryData = refundBtn.data('query');
                        queryData += ("&amount="+ refundAmountInput.val());
                        processRefund(refundBtn, refundAmountInput, returnUrl, queryData);
                    } else {
                        alert("The maximum allowed refund amount is "+refundAmountInput.data('maximum')+" only!");
                    }
                }
            });

            refundAmountInput.on('keyup', function () {
                if (!isValidRefundAmount($(this))) {
                    $(this).css('border', '1px red solid');
                    refundBtn.prop('disabled', true);
                } else {
                    $(this).css('border', '1px #ccc solid');
                    refundBtn.prop('disabled', false);
                }
            })
        }
    })(jQuery, window, document);

    function processRefund(refundBtn, refundAmountInput, refundUrl, queryData) {
        refundBtn.prop('disabled', true);
        refundAmountInput.prop('disabled', true);
        $.ajax({
            url: refundUrl,
            type: 'GET',
            data: {
                ajax: true,
                query_data: queryData
            },
            success: function(response) {
                if (response.status == 'success') {
                    console.log('Success');
                } else {
                    alert(response.message);
                }
                // refersh the page
                window.location.reload();
            },
            error: function(response) {
                alert(response);
            }
        });
    }

    function isValidRefundAmount(refundAmountInput) {
        return refundAmountInput.val() <= refundAmountInput.data('maximum');
    }
</script>
{/literal}