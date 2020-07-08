<div style="display: inline-block; padding: 5px;" class="{$container_class}">
    <a style="text-decoration: none;display: flex;" href="javascript: void(0)" id="{$gateway_name}-popup">
        <img src="{$payment_logo}" style="float: left;padding-right: 5px; max-width: 110px;"/>

        <span style="font-size: 15px;padding-right: 5px;color: rgb(46, 46, 46);">
            {$payment_info}
        </span>

        <span style="color: {$color}; font-weight: bold; font-size:13px;">learn more</span>
    </a>

    {if $currency_code === 'NZD'}
        {include file="../front/genoapay_payment_modal.tpl"}
    {else if $currency_code === 'AUD'}
        {include file="../front/latitudepay_payment_modal.tpl"}
    {/if}
</div>