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
        <fieldset>
            {row}
                {$name = 'shipping_address'}
                {col cols=12}<hr>{/col}
                {col cols=12 md=4}
                    <div class="h3">{lang key='contactInformation' section='account data'}</div>
                {/col}
                {col md=8}
                    {formrow}
                        {if $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N' || $Einstellungen.kunden.lieferadresse_abfragen_mobil !== 'N'}
                            {block name='checkout-customer-shipping-contact-mail-phone'}
                                {if $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N'}
                                {col md=6}
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
                                    {col md=6}
                                        {include file='snippets/form_group_simple.tpl'
                                            options=[
                                                "tel", "{$prefix}-{$name}-mobil", "{$prefix}[{$name}][mobil]",
                                                {$Lieferadresse->cMobil|default:null}, {lang key='mobile' section='account data'},
                                                $Einstellungen.kunden.lieferadresse_abfragen_mobil, null, "shipping mobile tel"
                                            ]
                                        }
                                    {/col}
                                {/if}
                            {/block}
                        {/if}
                        {if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N' || $Einstellungen.kunden.lieferadresse_abfragen_fax !== 'N'}
                            {block name='checkout-customer-shipping-contact-mobile-fax'}
                                {if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N'}
                                    {col md=6}
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
                                    {col md=6}
                                        {include file='snippets/form_group_simple.tpl'
                                            options=[
                                                "tel", "{$prefix}-{$name}-fax", "{$prefix}[{$name}][fax]",
                                                {$Lieferadresse->cFax|default:null}, {lang key='fax' section='account data'},
                                                $Einstellungen.kunden.lieferadresse_abfragen_fax, null, "shipping fax tel"
                                            ]
                                        }
                                    {/col}
                                {/if}
                            {/block}
                        {/if}
                    {/formrow}
                {/col}
            {/row}
        </fieldset>
    {/if}
{/block}
