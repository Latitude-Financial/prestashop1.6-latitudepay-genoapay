{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}

<form action="{$purchase_url|escape:'html'}" method="get">
    <h1>{l s='Latitude Finance'}</h1>
    <p>{l s='You will be redirected to the Latitude Finance official website to continue the payment.'}</p>
    <p class="cart_navigation" id="cart_navigation">
        <input type="submit" value="{l s='I confirm my order' mod='latitude_official'}" class="exclusive_large"/>
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='latitude_official'}</a>
    </p>
</form>