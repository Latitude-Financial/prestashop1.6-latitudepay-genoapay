{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}

<form action="{$purchase_url|escape:'html'}" method="get" style="color: #333434; font-size: 16px; max-width: 500px; line-height: 24px">
    {if $currency_code === 'NZD'}
        <img src="{$payment_checkout_logo}" alt="GenoaPay" />
    {elseif $currency_code === 'AUD'}
        <img src="{$payment_checkout_logo}" alt="LatitudePay" />
    {/if}

    <div style="margin:10px 0px;">
        <span style="font-weight: 700; line-height: 24px;">{l s="Shop now pay later."}</span>
        {if $currency_code === 'NZD'}
            <a id="genoapay-popup" href="javascript:void(0)" target="_blank" style="text-decoration: underline; marigin-left: 5px; color: {$branding_color}">
                {l s="Learn More"}
            </a>
        {elseif $currency_code === 'AUD'}
            <a id="latitudepay-popup" href="javascript:void(0)" target="_blank" style="text-decoration: underline; marigin-left: 5px; color: {$branding_color}">
                {l s="Learn More"}
            </a>
        {/if}
    </div>
    <p style="margin-bottom: 20px;">{l s="10 interest free payments from "}<strong style="color:{$branding_color}">{$currency_code}{$currency_symbol}{$splited_payment}</strong></p>

    <p style="font-weight: 600;">You will be redirected to the {$payment_gateway_name} website when you select Continue to Payment.</p>

    <p style="color:rgb(102, 102, 102)">{$payment_description|unescape:'html'}</p>

    <input
        style="background: none; padding:10px 20px; color: white; border: none; font-weight: 700; background-color: #26a65b;"
        type="submit"
        onMouseOver="this.style.backgroundColor='#00884b'"
        onMouseOut="this.style.backgroundColor='#26a65b'"
        value="{l s='Continue to Payment' mod='latitude_official'}"
    />
        
    <input
        type="button"
        style="background: none; padding:10px 20px; color:#545454; border: 1px solid #26a65b; font-weight: 700; background-color: transparent;"
        onclick='chooseOtherPaymentMethod(event, "{$link->getPageLink('order', true, NULL, 'step=3')|escape:'html'}")'
        onMouseOver="this.style.color='#333434'"
        onMouseOut="this.style.color='#545454'"
        value="{l s='Other payment methods' mod='latitude_official'}"
    />
</form>

{if $currency_code === 'NZD'}
    {include file="./genoapay_payment_modal.tpl"}
{else if $currency_code === 'AUD'}
    {include file="./latitudepay_payment_modal.tpl"}
{/if}

{* {literal} tags allow a block of data to be taken literally. This is typically used around Javascript or stylesheet blocks where {curly braces} would interfere with the template delimiter syntax. *}
{literal}
<script type="text/javascript">
    function chooseOtherPaymentMethod(e, link) {
        e = e || window.event;
        e.preventDefault();

        location.href = link;
    }
</script>
{/literal}