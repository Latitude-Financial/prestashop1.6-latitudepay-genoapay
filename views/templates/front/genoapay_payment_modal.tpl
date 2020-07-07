<div class="g-infomodal-container" id="g-infomodal-container" style="display: none;">
    <div class="g-infomodal-content">
        <img id="g-infomodal-close" class="g-infomodal-close" src="{$base_dir}{$current_module_uri}views/images/genoapay/close_btn_gen_green.svg">
        <div class="g-infomodal-inner">
            <div class="g-modal-header">
                <img class="g-infomodal-logo" src="{$base_dir}{$current_module_uri}views/images/genoapay/genoapay_logo_white.svg">
                <span>{l s="Pay over 10 weeks."}<br>{l s="No interest, no fees."}</span>
            </div>
            <div class="g-infomodal-body">
                <div class="g-infomodal-card-group">
                    <div class="g-infomodal-card">
                        <div class="g-infomodal-card-content">
                            <img src="{$base_dir}{$current_module_uri}views/images/genoapay/shopping_trolly_icon.svg">
                        </div>
                        <div class="g-infomodal-card-footer">
                            <div class="g-infomodal-card-title"><span>{l s="Checkout with"} </span><span>{l s="Genoapay"}</span></div>
                        </div>
                    </div>
                    <div class="g-infomodal-card">
                        <div class="g-infomodal-card-content">
                            <img src="{$base_dir}{$current_module_uri}views/images/genoapay/thin_tick_icon.svg">
                        </div>
                        <div class="g-infomodal-card-footer">
                            <div class="g-infomodal-card-title"><span>{l s="Credit approval"} </span><span>{l s="in seconds"}</span></div>
                        </div>
                    </div>
                    <div class="g-infomodal-card">
                        <div class="g-infomodal-card-content">
                            <img src="{$base_dir}{$current_module_uri}views/images/genoapay/get_it_now_icon.svg">
                        </div>
                        <div class="g-infomodal-card-footer">
                            <div class="g-infomodal-card-title"><span>{l s="Get it now,"} </span><span>{l s="pay over 10 weeks"}</span></div>
                        </div>
                    </div>
                </div>
                <p>{l s="That's it! We manage automatic weekly payments until you're paid off. Full purchase details can be viewed anytime online."}</p>
                <hr>
                <p>{l s="You will need"}</p>
                <ul class="g-infomodal-list">
                    <li>{l s="To be over 18 years old"}</li>
                    <li>{l s="Visa/Mastercard payment"}</li>
                    <li>{l s="NZ drivers licence or passport"}</li>
                    <li>{l s="First instalment paid today"}</li>
                </ul>
                <div class="g-infomodal-terms">Learn more about <a href="https://www.genoapay.com/how-it-works/" target="_blank">how it works</a>. {l s="Credit criteria applies. Weekly payments will be automatically deducted. Failed instalments incur a $10 charge."}See our <a href="https://www.genoapay.com/terms-and-conditions/" target="_blank">Terms & Conditions</a> for more information.</div>
            </div>
        </div>
    </div>
</div>

{* {literal} tags allow a block of data to be taken literally. This is typically used around Javascript or stylesheet blocks where {curly braces} would interfere with the template delimiter syntax. *}
{literal}
<script>
    // Pure JS just in case if the merchant website is not using jQuery
    ;(function() {
        var popupTrigger = document.getElementById('genoapay-popup'),
            popup        = document.getElementById('g-infomodal-container'),
            closeBtn     = document.getElementById('g-infomodal-close');

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
            // popup the genoapay HTML
            openPopup(popup);
        });

        closeBtn.addEventListener('click', function() {
            closePopup(popup);
        });
    })();
</script>
{/literal}