{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='checkout-customer-shipping-contact'}
    {if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N'
        || $Einstellungen.kunden.lieferadresse_abfragen_fax !== 'N'
        || $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N'
        || $Einstellungen.kunden.lieferadresse_abfragen_mobil !== 'N'
    }
        {$name = 'shipping_address'}
        <fieldset>
            <legend>{lang key='contactInformation' section='account data'}</legend>
            {if $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N' || $Einstellungen.kunden.lieferadresse_abfragen_mobil !== 'N'}
                {block name='checkout-customer-shipping-contact-mail-phone'}
                    {row}
                        {if $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N'}
                        {col cols=12 md=6}
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    "email", "{$prefix}-{$name}-email", "{$prefix}[{$name}][email]",
                                    {$Lieferadresse->cMail|default:null}, {lang key='email' section='account data'},
                                    $Einstellungen.kunden.lieferadresse_abfragen_email, null, "shipping email"
                                ]
                            }
                        {/col}
                        {/if}
                        {if $Einstellungen.kunden.lieferadresse_abfragen_mobil !== 'N'}
                            {col cols=12 md=6}
                                {include file='snippets/form_group_simple.tpl'
                                    options=[
                                        "tel", "{$prefix}-{$name}-mobil", "{$prefix}[{$name}][mobil]",
                                        {$Lieferadresse->cMobil|default:null}, {lang key='mobil' section='account data'},
                                        $Einstellungen.kunden.lieferadresse_abfragen_mobil, null, "shipping mobile tel"
                                    ]
                                }
                            {/col}
                        {/if}
                    {/row}
                {/block}
            {/if}
            {if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N' || $Einstellungen.kunden.lieferadresse_abfragen_fax !== 'N'}
                {block name='checkout-customer-shipping-contact-mobile-fax'}
                    {row}
                        {if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N'}
                            {col cols=12 md=6}
                                {include file='snippets/form_group_simple.tpl'
                                    options=[
                                        "tel", "{$prefix}-{$name}-tel", "{$prefix}[{$name}][tel]",
                                        {$Lieferadresse->cTel|default:null}, {lang key='tel' section='account data'},
                                        $Einstellungen.kunden.lieferadresse_abfragen_tel, null, "shipping home tel"
                                    ]
                                }
                            {/col}
                        {/if}
                        {if $Einstellungen.kunden.lieferadresse_abfragen_fax !== 'N'}
                            {col cols=12 md=6}
                                {include file='snippets/form_group_simple.tpl'
                                    options=[
                                        "tel", "{$prefix}-{$name}-fax", "{$prefix}[{$name}][fax]",
                                        {$Lieferadresse->cFax|default:null}, {lang key='fax' section='account data'},
                                        $Einstellungen.kunden.lieferadresse_abfragen_fax, null, "shipping fax tel"
                                    ]
                                }
                            {/col}
                        {/if}
                    {/row}
                {/block}
            {/if}
        </fieldset>
    {/if}
{/block}
