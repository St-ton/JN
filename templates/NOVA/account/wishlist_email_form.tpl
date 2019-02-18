{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
    <h1>{lang key='wishlistViaEmail' section='login'}</h1>
    {row}
        {col cols=12}
            {block name='wishlist-email-form'}
            {card}
                <h3>{block name='wishlist-email-form-title'}{$CWunschliste->cName}{/block}</h3>
                {block name='wishlist-email-form-body'}
                {form method="post" action="{get_static_route id='jtl.php'}" name="Wunschliste"}
                    {input type="hidden" name="wlvm" value="1"}
                    {input type="hidden" name="wl" value=$CWunschliste->kWunschliste}
                    {input type="hidden" name="send" value="1"}
                    {formgroup label-for="wishlist-email" label="{lang key='wishlistEmails' section='login'}{if $Einstellungen.global.global_wunschliste_max_email > 0} | {lang key='wishlistEmailCount' section='login'}: {$Einstellungen.global.global_wunschliste_max_email}{/if}"}
                        {textarea id="wishlist-email" name="email" rows="5"}{/textarea}
                    {/formgroup}
                    <hr>
                    {row}
                        {col cols=12}
                            {button name="abschicken" type="submit" value="1" variant="primary"}{lang key='wishlistSend' section='login'}{/button}
                        {/col}
                    {/row}
                {/form}
                {/block}
            {/card}
            {/block}
        {/col}
    {/row}
{/if}
