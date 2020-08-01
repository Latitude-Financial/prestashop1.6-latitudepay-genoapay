{*
 * PrestaPay - A Sample Payment Module for PrestaShop 1.7
 *
 * HTML to be displayed in the order confirmation page
 *
 * @author Andresa Martins <contact@andresa.dev>
 * @license https://opensource.org/licenses/afl-3.0.php
 *}

<h1 style="text-transform: uppercase;">{l s="Order received"}</h1>
<p>{l s="Thank you. Your order has been received."}</p>

<ul>
    <li>{l s="Order number:"}<span style="font-weight: bold;">{$order_id}</span></li>
    <li>{l s="Date:"}<span style="font-weight: bold;">{$invoice_date}</span></li>
    <li>{l s="Email:"}<span style="font-weight: bold;">{$email}</span></li>
    <li>{l s="Total:"}<span style="font-weight: bold;">{$currency_code}{$order_total_amount}</span></li>
    <li>{l s="Payment method:"}<span style="font-weight: bold;">{$payment_method}</span></li>
</ul>