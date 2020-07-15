<button name="latitudeRefund" class="btn btn-default" onclick="processRefund(event, '{$refund_url}', '{$query_data}')" style="margin-bottom: 15px">
    <i class="icon-exchange"></i> {l s=$paymenet_gateway_name} Refund
</button>

{literal}
<script type="text/javascript">
    function processRefund(e, refundUrl) {
        event.preventDefault();

        $.ajax({
            url: refundUrl,
            type: 'GET',
            data: {
                ajax: true
            },
            success: function(response) {
                console.log(response);
            },
            error: function(response) {
                console.log(response);
            }
        });
    }
</script>
{/literal}