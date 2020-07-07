<div class="lp-modal-container" id="lp-modal-container" style="display: none;">
    <div class="lp-modal">
        <div class="lp-content">
            <div class="modal-header lp-header">
                <button id="lp-modal-close" aria-hidden="true" class="lp-close-container" type="button">×</button>
                <h4 class="modal-title text-center">
                <img class="lp-logo" src="{$base_dir}{$current_module_uri}views/images/latitudepay/lpay_modal_logo.png">
                </h4>
            </div>
            <div class="modal-body p-0">
                <div class="lp-content">
                    <div class="lp-body">
                        <div class="lp-heading lp-block">
                            <div>
                                {l s="How does this work?"}
                            </div>
                            <div class="lp-bold">
                                {l s="Glad you asked!"}
                            </div>
                        </div>
                        <ul class="lp-steps lp-block">
                            <li>
                                <img src="{$base_dir}{$current_module_uri}views/images/latitudepay/lp_phone.png" style="margin-left: auto;margin-right: auto;">
                                <div class="lp-subheading">
                                    {l s="Choose LatitudePay"}
                                    <br class="lp-line-break">
                                    {l s="at the checkout"}
                                </div>
                                <span>
                                    {l s="There is no extra cost to you - just select it as your"}
                                    <br class="lp-line-break">
                                    {l s="payment option."}
                                </span>
                            </li>
                            <li>
                                <img src="{$base_dir}{$current_module_uri}views/images/latitudepay/lp_timer.png" style="margin-left: auto;margin-right: auto;">
                                <div class="lp-subheading">
                                    {l s="Approval in"}
                                    <br class="lp-line-break">
                                    {l s="minutes"}
                                </div>
                                <span>
                                    {l s="Set up your account and we will tell you straight away"}
                                    <br class="lp-line-break">
                                    {l s="if approved."}
                                </span>
                            </li>
                            <li>
                                <img src="{$base_dir}{$current_module_uri}views/images/latitudepay/lp_calender.png" style="margin-left: auto;margin-right: auto;">
                                <div class="lp-subheading">
                                    {l s="Get it now, pay"}
                                    <br class="lp-line-break">
                                    {l s="over 10 weeks"}
                                </div>
                                <span>{l s="It is the today way to pay, just 10 easy payments."}
                                    <br class="lp-line-break">
                                    {l s="No interest. Ever."}
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="lp-requirements lp-block">
                        <div class="lp-subheading">
                            {l s="If you are new to LatitudePay, you will need this stuff:"}
                        </div>
                        <ul class="lp-requirements-list">
                            <li>{l s="Be over 18 years old"}</li>
                            <li>{l s="An Australian driver’s licence or passport"}</li>
                            <li>{l s="A couple of minutes to sign up,"}
                                <br class="lp-line-break">{l s="it’s quick and easy"}
                            </li>
                            <li>{l s="A credit/debit card (Visa or Mastercard)"}</li>
                        </ul>
                    </div>
                    <div class="apply-button-container text-center" style="text-align: center;">
                        <a href="https://app.latitudepay.com" class="btn btn_lg btn_primary">{l s="Apply Now"}</a>
                    </div>
                    <div class="lp-footer lp-block">
                        {l s="Subject to approval. Conditions and late fees apply. Payment Plan provided by LatitudePay Australia Pty Ltd ABN 23 633 528 873. For complete terms visit"}<a href="https://latitudepay.com/terms" target="_blank"> {l s="latitudepay.com/terms"}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{* {literal} tags allow a block of data to be taken literally. This is typically used around Javascript or stylesheet blocks where {curly braces} would interfere with the template delimiter syntax. *}
{literal}
<script>
    // Pure JS just in case if the merchant website is not using jQuery
    ;(function() {
        var popupTrigger = document.getElementById('latitudepay-popup'),
            popup        = document.getElementById('lp-modal-container'),
            closeBtn     = document.getElementById('lp-modal-close');

        function openPopup(element) {
          element.style.display = 'block';
        }

        function closePopup(element) {
            element.style.display = 'none';
        }

        popupTrigger.addEventListener('click', function(event) {
            // prevent default
            event.preventDefault();
            event.stopImmediatePropagation();
            document.body.appendChild(popup);
            // popup the latitudepay HTML
            openPopup(popup);
        });

        closeBtn.addEventListener('click', function() {
            closePopup(popup);
        });
    })();
</script>
{/literal}