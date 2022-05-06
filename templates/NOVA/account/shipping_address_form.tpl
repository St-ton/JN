{block name='account-shipping-address-form'}
    {row class='btn-row'}
        {block name='account-shipping-address-form-form-lieferadressen'}
            {col md=6}
                {form method="post" id='lieferadressen' action="{get_static_route params=['editLieferadresse' => 1]}" class="jtl-validate" slide=true}
                    {block name='account-inc-shipping-address-include-customer-shipping-address-first'}
                        {include file='../checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=null}
                    {/block}
                    {block name='account-address-form-form-submit'}
                        {row class='btn-row'}
                            {col md=8 xl=6 class="checkout-button-row-submit"}
                                {input type="hidden" name="editLieferadresse" value="1"}
                                {input type="hidden" name="edit" value="1"}
                                {if $Lieferadresse->nIstStandardLieferadresse === "1"}
                                    {input type="hidden" name="isDefault" value=1}
                                {/if}
                                {if isset($Lieferadresse->kLieferadresse) && !isset($smarty.get.fromCheckout)}
                                    {input type="hidden" name="editAddress" value=$Lieferadresse->kLieferadresse}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='updateAddress' section='account data'}
                                    {/button}
                                {else if !isset($Lieferadresse->kLieferadresse)}
                                    {input type="hidden" name="editAddress" value="neu"}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='saveAddress' section='account data'}
                                    {/button}
                                {else if isset($Lieferadresse->kLieferadresse) && isset($smarty.get.fromCheckout)}
                                    {input type="hidden" name="editAddress" value=$Lieferadresse->kLieferadresse}
                                    {input type="hidden" name="backToCheckout" value="1"}
                                    {button type="submit" value="1" block=true variant="primary"}
                                        {lang key='updateAddressBackToCheckout' section='account data'}
                                    {/button}
                                {/if}
                            {/col}
                        {/row}
                    {/block}
                {/form}
            {/col}
            {col md=6}
                {block name='checkout-inc-shipping-address-fieldset-address'}
                        {foreach $Lieferadressen as $adresse}
                            {if $adresse->kLieferadresse > 0}
                                {block name='checkout-inc-shipping-address-address'}
                                <div class="card mb-3">
                                    {if $adresse->nIstStandardLieferadresse === '1'}
                                        <div class="card-header bg-primary">
                                            <strong>Standard Lieferadresse</strong>
                                        </div>
                                    {/if}
                                    <div class="card-body">
                                        <span class="control-label label-default">
                                            {if $adresse->cFirma}{$adresse->cFirma}<br />{/if}
                                            {if $adresse->cTitel}{$adresse->cTitel}<br />{/if}
                                            <strong>{$adresse->cVorname} {$adresse->cNachname}</strong><br />
                                            {$adresse->cStrasse} {$adresse->cHausnummer}<br />
                                            {$adresse->cPLZ} {$adresse->cOrt}<br />
                                            {$adresse->angezeigtesLand}
                                        </span>
                                    </div>
                                    <div class="card-footer text-muted">
                                        {if $adresse->nIstStandardLieferadresse !== '1'}
                                            <div class="control-label label-default">
                                                {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'setAddressAsDefault' => {$adresse->kLieferadresse}]}" class="btn btn-primary btn-sm" rel="nofollow" }
                                                    {lang key='useAsDefaultShippingAdress' section='account data'}
                                                {/link}
                                            </div>
                                        {/if}
                                        <div class="control-label label-default mt-2">
                                            {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'editAddress' => {$adresse->kLieferadresse}]}" class="btn btn-secondary btn-sm" alt="Adresse bearbeiten"}
                                                <span class="fas fa-pencil-alt"></span>
                                                {lang key='editShippingAdress' section='account data'}
                                            {/link}
                                            {link href="{get_static_route id='jtl.php' params=['editLieferadresse' => 1, 'deleteAddress' => {$adresse->kLieferadresse}]}" class="btn btn-danger btn-sm" alt="Adresse löschen"}
                                                <span class="fas fa-times"></span>
                                                {lang key='deleteAddres' section='account data'}
                                            {/link}
                                        </div>
                                    </div>
                                </div>
                                {/block}



                            {/if}
                        {/foreach}

                {/block}
            {/col}
        {/block}
    {/row}
{/block}