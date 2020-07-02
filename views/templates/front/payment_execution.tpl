<div>Redirect to Latitude</div>
<form action="{$purchase_url|escape:'html'}" method="get">
    <p class="cart_navigation" id="cart_navigation">
        <input type="submit" value="{l s='I confirm my order' mod='latitude_official'}" class="exclusive_large"/>
        <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='latitude_official'}</a>
    </p>
</form>