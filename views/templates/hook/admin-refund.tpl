{if $custom_refund}
    <button name="latitudeRefund" id="refundAction" style="display: none" class="btn btn-default" onclick="processRefund(event, '{$refund_url}', '{$query_data}')" style="margin-bottom: 15px">
        <i class="icon-exchange"></i> {l s=$paymenet_gateway_name} Refund
    </button>
{/if}

{literal}
<script type="text/javascript">
    ;(function($, window, document) {
        if ($('#refundAction').length > 0) {
            var origPartialBtn = $('#desc-order-partial_refund');
            $(origPartialBtn).hide().after($('#refundAction').show());
        }
    })(jQuery, window, document);

    function processRefund(e, refundUrl, queryData) {
        event.preventDefault();

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
                }
                // popup alert message
                alert(response.message);
                // refersh the page
                window.location.reload();
            },
            error: function(response) {
                alert(response);
            }
        });
    }
</script>
{/literal}