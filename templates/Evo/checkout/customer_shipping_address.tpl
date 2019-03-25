{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{$name = 'shipping_address'}
{* salutation / title *}
<div class="row">
    {if $Einstellungen.kunden.lieferadresse_abfragen_anrede !== 'N'}
        <div class="col-xs-12 col-md-6">
            <div class="form-group float-label-control{if !empty($fehlendeAngaben.anrede)} has-error{/if}">
                <label for="{$prefix}-{$name}-salutation" class="control-label">
                    {lang key='salutation' section='account data'}
                    {if $Einstellungen.kunden.lieferadresse_abfragen_anrede === 'O'}
                        <span class="optional"> - {lang key='optional'}</span>
                    {/if}
                </label>
                <select name="{$prefix}[{$name}][anrede]" id="{$prefix}-{$name}-salutation" class="form-control" {if $Einstellungen.kunden.lieferadresse_abfragen_anrede === 'Y'}required{/if} autocomplete="shipping sex">
                    <option value="" selected="selected" {if $Einstellungen.kunden.lieferadresse_abfragen_anrede === 'Y'}disabled{/if}>
                        {if $Einstellungen.kunden.lieferadresse_abfragen_anrede === 'Y'}{lang key='salutation' section='account data'}{else}{lang key='noSalutation'}{/if}
                    </option>
                    <option value="w"{if isset($Lieferadresse->cAnrede) && $Lieferadresse->cAnrede === 'w'} selected="selected"{/if}>{lang key='salutationW'}</option>
                    <option value="m"{if isset($Lieferadresse->cAnrede) && $Lieferadresse->cAnrede === 'm'} selected="selected"{/if}>{lang key='salutationM'}</option>
                </select>
                {if !empty($fehlendeAngaben.anrede)}
                    <div class="form-error-msg text-danger">{lang key='fillOut' section='global'}</div>
                {/if}
            </div>
        </div>
    {/if}
    {if $Einstellungen.kunden.lieferadresse_abfragen_titel !== 'N'}
        <div class="col-xs-12 col-md-6">
            {include file='snippets/form_group_simple.tpl'
                options=[
                    "text", "{$prefix}-{$name}-title", "{$prefix}[{$name}][titel]",
                    {$Lieferadresse->cTitel|default:null},
                    {lang key='title' section='account data'}, {$Einstellungen.kunden.lieferadresse_abfragen_titel},
                    null, "shipping honorific-prefix"
                ]
            }
        </div>
    {/if}
</div>

{* firstname lastname *}
<div class="row">
    <div class="col-xs-12 col-md-6">
        {include file='snippets/form_group_simple.tpl'
            options=[
                "text", "{$prefix}-{$name}-firstName", "{$prefix}[{$name}][vorname]",
                {$Lieferadresse->cVorname|default:null}, {lang key='firstName' section='account data'},
                {$Einstellungen.kunden.kundenregistrierung_pflicht_vorname},
                null, "shipping given-name"
            ]
        }
    </div>
    <div class="col-xs-12 col-md-6">
        {include file='snippets/form_group_simple.tpl'
            options=[
                "text", "{$prefix}-{$name}-lastName", "{$prefix}[{$name}][nachname]",
                {$Lieferadresse->cNachname|default:null}, {lang key='lastName' section='account data'},
                true, null, "shipping family-name"
            ]
        }
    </div>
</div>

{* firm / firmtext *}
<div class="row">
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_firma !== 'N'}
        <div class="col-xs-12 col-md-6">
            {include file='snippets/form_group_simple.tpl'
                options=[
                    "text", "{$prefix}-{$name}-firm", "{$prefix}[{$name}][firma]",
                    {$Lieferadresse->cFirma|default:null}, {lang key='firm' section='account data'},
                    $Einstellungen.kunden.kundenregistrierung_abfragen_firma, null, "shipping organization"
                ]
            }
        </div>
    {/if}
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz !== 'N'}
        <div class="col-xs-12 col-md-6">
            {include file='snippets/form_group_simple.tpl'
                options=[
                    "text", "{$prefix}-{$name}-firmext", "{$prefix}[{$name}][firmazusatz]",
                    {$Lieferadresse->cZusatz|default:null}, {lang key='firmext' section='account data'},
                    $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz
                ]
            }
        </div>
    {/if}
</div>

{* street / number *}
<div class="row">
    <div class="col-xs-12 col-md-8">
        {include file='snippets/form_group_simple.tpl'
            options=[
                "text", "{$prefix}-{$name}-street", "{$prefix}[{$name}][strasse]",
                {$Lieferadresse->cStrasse|default:null}, {lang key='street' section='account data'},
                true, null, "shipping address-line1"
            ]
        }
    </div>
    <div class="col-xs-12 col-md-4">
        {include file='snippets/form_group_simple.tpl'
            options=[
                "text", "{$prefix}-{$name}-streetnumber", "{$prefix}[{$name}][hausnummer]",
                {$Lieferadresse->cHausnummer|default:null}, {lang key='streetnumber' section='account data'},
                true, null, "shipping address-line2"
            ]
        }
    </div>
</div>

{* adress addition *}
{if $Einstellungen.kunden.lieferadresse_abfragen_adresszusatz !== 'N'}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            {include file='snippets/form_group_simple.tpl'
                options=[
                    "text", "{$prefix}-{$name}-street2", "{$prefix}[{$name}][adresszusatz]",
                    {$Lieferadresse->cAdressZusatz|default:null}, {lang key='street2' section='account data'},
                    $Einstellungen.kunden.lieferadresse_abfragen_adresszusatz, null, "shipping address-line3"
                ]
            }
        </div>
    </div>
{/if}

{* country *}
{if isset($Lieferadresse->cLand)}
    {assign var='cIso' value=$Lieferadresse->cLand}
{elseif !empty($Kunde->cLand)}
    {assign var='cIso' value=$Kunde->cLand}
{elseif !empty($Einstellungen.kunden.kundenregistrierung_standardland)}
    {assign var='cIso' value=$Einstellungen.kunden.kundenregistrierung_standardland}
{elseif isset($laender[0]->cISO)}
    {assign var='cIso' value=$laender[0]->cISO}
{else}
    {assign var='cIso' value=''}
{/if}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="form-group float-label-control">
            <label class="control-label" for="{$prefix}-{$name}-country">{lang key='country' section='account data'}</label>
            <select name="{$prefix}[{$name}][land]" id="{$prefix}-{$name}-country" class="country-input form-control" autocomplete="shipping country">
                <option value="" selected disabled>{lang key='country' section='account data'}*</option>
                {foreach $LieferLaender as $land}
                    <option value="{$land->getISO()}" {if ($Einstellungen.kunden.kundenregistrierung_standardland == $land->getISO() && empty($Lieferadresse->cLand)) || (isset($Lieferadresse->cLand) && $Lieferadresse->cLand == $land->getISO())}selected="selected"{/if}>{$land->getName()}</option>
                {/foreach}
            </select>
        </div>
    </div>
    {if $Einstellungen.kunden.lieferadresse_abfragen_bundesland !== 'N'}
        {getStates cIso=$cIso assign='oShippingStates'}
        {if isset($Lieferadresse->cBundesland)}
            {assign var='cState' value=$Lieferadresse->cBundesland}
        {elseif !empty($Kunde->cBundesland)}
            {assign var='cState' value=$Kunde->cBundesland}
        {else}
            {assign var='cState' value=''}
        {/if}
        <div class="col-xs-12 col-md-6">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.bundesland)} has-error{/if}">
                <label class="control-label" for="{$prefix}-{$name}-state">{lang key='state' section='account data'}
                    {if $Einstellungen.kunden.lieferadresse_abfragen_bundesland !== 'Y'}
                        <span class="optional"> - {lang key='optional'}</span>
                    {/if}
                </label>
                {if !empty($oShippingStates)}
                    <select
                            title="{lang key=pleaseChoose}"
                            name="{$prefix}[{$name}][bundesland]"
                            id="{$prefix}-{$name}-state"
                            class="form-control state-input"
                            autocomplete="shipping address-level1"
                            {if $Einstellungen.kunden.lieferadresse_abfragen_bundesland === 'Y'} required{/if}
                    >
                        <option value="" selected disabled>{lang key='pleaseChoose' section='global'}</option>
                        {foreach $oShippingStates as $oState}
                            <option value="{$oState->cCode}" {if $cState === $oState->cName || $cState === $oState->cCode}selected{/if}>{$oState->cName}</option>
                        {/foreach}
                    </select>
                {else}
                    <input
                        type="text"
                        title="{lang key=pleaseChoose}"
                        name="{$prefix}[{$name}][bundesland]"
                        value="{if isset($Lieferadresse->cBundesland)}{$Lieferadresse->cBundesland}{/if}"
                        id="{$prefix}-{$name}-state"
                        class="form-control"
                        data-toggle="state" data-target="#{$prefix}-{$name}-country"
                        placeholder="{lang key='state' section='account data'}"{if $Einstellungen.kunden.lieferadresse_abfragen_bundesland === 'Y'} required{/if}
                        autocomplete="shipping address-level1">
                {/if}

                {if !empty($fehlendeAngaben.bundesland)}
                    <div class="form-error-msg text-danger">{lang key='fillOut' section='global'}</div>
                {/if}
            </div>
        </div>
    {/if}
</div>

{* zip / city *}
<div class="row">
    <div class="col-xs-12 col-md-4">
        <div class="form-group float-label-control{if !empty($fehlendeAngaben.plz)} has-error{/if}">
            <label class="control-label" for="{$prefix}-{$name}-postcode">{lang key='plz' section='account data'}</label>
            <input
                type="text"
                name="{$prefix}[{$name}][plz]"
                value="{if isset($Lieferadresse->cPLZ)}{$Lieferadresse->cPLZ}{/if}"
                id="{$prefix}-{$name}-postcode"
                class="postcode_input form-control"
                placeholder="{lang key='plz' section='account data'}"
                data-toggle="postcode" data-city="#{$prefix}-{$name}-city" data-country="#{$prefix}-{$name}-country"
                required
                autocomplete="shipping postal-code">
            {if isset($fehlendeAngaben.plz)}
                <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                    {if $fehlendeAngaben.plz >= 2}
                        {lang key='checkPLZCity' section='checkout'}
                    {else}
                        {lang key='fillOut' section='global'}
                    {/if}
                </div>
            {/if}
        </div>
    </div>

    <div class="col-xs-12 col-md-8">
        <div class="form-group float-label-control{if !empty($fehlendeAngaben.ort)} has-error{/if}">
            <label class="control-label" for="{$prefix}-{$name}-city">{lang key='city' section='account data'}</label>
            <input type="text" name="{$prefix}[{$name}][ort]"
                   value="{if isset($Lieferadresse->cOrt)}{$Lieferadresse->cOrt}{/if}" id="{$prefix}-{$name}-city"
                   class="city_input form-control" placeholder="{lang key='city' section='account data'}" required
                   autocomplete="shipping address-level2">
            {if isset($fehlendeAngaben.ort)}
                <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                    {if $fehlendeAngaben.ort==3}
                        {lang key='cityNotNumeric' section='account data'}
                    {else}
                        {lang key='fillOut' section='global'}
                    {/if}
                </div>
            {/if}
        </div>
    </div>
</div>
