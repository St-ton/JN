{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N'
    || $Einstellungen.kunden.lieferadresse_abfragen_fax !== 'N'
    || $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N'
    || $Einstellungen.kunden.lieferadresse_abfragen_mobil !== 'N'}
{$name = 'shipping_address'}
<fieldset>
    <legend>{lang key='contactInformation' section='account data'}</legend>
    {if $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N' || $Einstellungen.kunden.lieferadresse_abfragen_mobil !== 'N'}
    <div class="row">
        {if $Einstellungen.kunden.lieferadresse_abfragen_email !== 'N'}
        <div class="col-xs-12 col-md-6">
            {include file='snippets/form_group_simple.tpl'
                options=[
                    "email", "{$prefix}-{$name}-email", "{$prefix}[{$name}][email]",
                    {$Lieferadresse->cMail|default:null}, {lang key='email' section='account data'},
                    $Einstellungen.kunden.lieferadresse_abfragen_email, null, "shipping email"
                ]
            }
        </div>
        {/if}
        {if $Einstellungen.kunden.lieferadresse_abfragen_mobil !== 'N'}
            <div class="col-xs-12 col-md-6">
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        "tel", "{$prefix}-{$name}-mobil", "{$prefix}[{$name}][mobil]",
                        {$Lieferadresse->cMobil|default:null}, {lang key='mobil' section='account data'},
                        $Einstellungen.kunden.lieferadresse_abfragen_mobil, null, "shipping mobile tel"
                    ]
                }
            </div>
        {/if}
    </div>
    {/if}
    {if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N' || $Einstellungen.kunden.lieferadresse_abfragen_fax !== 'N'}
    <div class="row">
        {if $Einstellungen.kunden.lieferadresse_abfragen_tel !== 'N'}
            <div class="col-xs-12 col-md-6">
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        "tel", "{$prefix}-{$name}-tel", "{$prefix}[{$name}][tel]",
                        {$Lieferadresse->cTel|default:null}, {lang key='tel' section='account data'},
                        $Einstellungen.kunden.lieferadresse_abfragen_tel, null, "shipping home tel"
                    ]
                }
            </div>
        {/if}
        {if $Einstellungen.kunden.lieferadresse_abfragen_fax !== 'N'}
            <div class="col-xs-12 col-md-6">
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        "tel", "{$prefix}-{$name}-fax", "{$prefix}[{$name}][fax]",
                        {$Lieferadresse->cFax|default:null}, {lang key='fax' section='account data'},
                        $Einstellungen.kunden.lieferadresse_abfragen_fax, null, "shipping fax tel"
                    ]
                }
            </div>
        {/if}
    </div>
    {/if}
</fieldset>
{/if}
