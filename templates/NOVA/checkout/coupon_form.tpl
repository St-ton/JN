{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}

{if $KuponMoeglich == 1}
    {form method="post" action="{get_static_route id='bestellvorgang.php'}" class="form form-inline evo-validate"}
        {input type="hidden" name="pruefekupon" value="1"}
        <fieldset>
            {inputgroup}
                {input type="text"
                    name="Kuponcode"
                    maxlength="32"
                    value="{if !empty($Kuponcode)}{$Kuponcode}{/if}"
                    id="kupon"
                    placeholder="{lang key='couponCode' section='account data'}"
                    aria=["label"=>"{lang key='couponCode' section='account data'}"]
                    required=true}
                {inputgroupaddon append=true}
                    {input type="submit" value="{lang key='useCoupon' section='checkout'}" class="submit btn btn-secondary"}
                {/inputgroupaddon}
            {/inputgroup}
        </fieldset>
    {/form}
{/if}
