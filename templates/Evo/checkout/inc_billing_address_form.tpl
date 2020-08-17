<fieldset>
    <legend>
        {if isset($checkout)}
            {lang key='proceedNewCustomer' section='checkout'}
        {else}
            {lang key='address' section='account data'}
        {/if}
    </legend>
    {* salutation / title *}
    <div class="row">
        {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede !== 'N'}
            <div class="col-xs-12 col-md-6">
                <div class="form-group float-label-control{if isset($fehlendeAngaben.anrede)} has-error{/if}">
                    <label for="salutation" class="control-label">
                        {lang key='salutation' section='account data'}
                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede === 'O'}
                            <span class="optional"> - {lang key='optional'}</span>
                        {/if}
                    </label>
                    <select name="anrede" id="salutation" class="form-control" {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede === 'Y'}required{/if} autocomplete="billing sex">
                        <option value="" selected="selected" {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede === 'Y'}disabled{/if}>
                            {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede === 'Y'}{lang key='salutation' section='account data'}{else}{lang key='noSalutation'}{/if}
                        </option>
                        <option value="w" {if isset($cPost_var['anrede']) && $cPost_var['anrede'] === 'w'}selected="selected"{elseif isset($Kunde->cAnrede) && $Kunde->cAnrede === 'w'}selected="selected"{/if}>{lang key='salutationW'}</option>
                        <option value="m" {if isset($cPost_var['anrede']) && $cPost_var['anrede'] === 'm'}selected="selected"{elseif isset($Kunde->cAnrede) && $Kunde->cAnrede === 'm'}selected="selected"{/if}>{lang key='salutationM'}</option>
                    </select>
                    {if isset($fehlendeAngaben.anrede)}
                        <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                            {lang key='fillOut' section='global'}
                        </div>
                    {/if}
                </div>
            </div>
        {/if}

        {if $Einstellungen.kunden.kundenregistrierung_abfragen_titel !== 'N'}
            <div class="col-xs-12 col-md-6">
                {if isset($cPost_var['titel'])}
                    {assign var='inputVal_title' value=$cPost_var['titel']}
                {elseif isset($Kunde->cTitel)}
                    {assign var='inputVal_title' value=$Kunde->cTitel}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'text', 'title', 'titel',
                        {$inputVal_title|default:null}, {lang key='title' section='account data'},
                        {$Einstellungen.kunden.kundenregistrierung_abfragen_titel}, null, 'billing honorific-prefix'
                    ]
                }
            </div>
        {/if}
    </div>
    {* firstname lastname *}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            {if isset($cPost_var['vorname'])}
                {assign var='inputVal_firstName' value=$cPost_var['vorname']}
            {elseif isset($Kunde->cVorname)}
                {assign var='inputVal_firstName' value=$Kunde->cVorname}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    "text", "firstName", "vorname",
                    {$inputVal_firstName|default:null}, {lang key='firstName' section='account data'},
                    {$Einstellungen.kunden.kundenregistrierung_pflicht_vorname}, null, "billing given-name"
                ]
            }
        </div>
        <div class="col-xs-12 col-md-6">
            {if isset($cPost_var['nachname'])}
                {assign var='inputVal_lastName' value=$cPost_var['nachname']}
            {elseif isset($Kunde->cNachname)}
                {assign var='inputVal_lastName' value=$Kunde->cNachname}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'lastName', 'nachname',
                    {$inputVal_lastName|default:null}, {lang key='lastName' section='account data'},
                    true, null, 'billing family-name'
                ]
            }
        </div>
    </div>
    {* firm / firmtext *}
    <div class="row">
        {if $Einstellungen.kunden.kundenregistrierung_abfragen_firma !== 'N'}
        <div class="col-xs-12 col-md-6">
            {if isset($cPost_var['firma'])}
                {assign var='inputVal_firm' value=$cPost_var['firma']}
            {elseif isset($Kunde->cFirma)}
                {assign var='inputVal_firm' value=$Kunde->cFirma}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'firm', 'firma',
                    {$inputVal_firm|default:null}, {lang key='firm' section='account data'},
                    $Einstellungen.kunden.kundenregistrierung_abfragen_firma, null, 'billing organization'
                ]
            }
        </div>
        {/if}

        {if $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz !== 'N'}
        <div class="col-xs-12 col-md-6">
            {if isset($cPost_var['firmazusatz'])}
                {assign var='inputVal_firmext' value=$cPost_var['firmazusatz']}
            {elseif isset($Kunde->cZusatz)}
                {assign var='inputVal_firmext' value=$Kunde->cZusatz}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'firmext', 'firmazusatz',
                    {$inputVal_firmext|default:null}, {lang key='firmext' section='account data'},
                    $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz
                ]
            }
        </div>
        {/if}
    </div>
    {* street / number *}
    <div class="row">
        <div class="col-xs-12 col-md-8">
            {if isset($cPost_var['strasse'])}
                {assign var='inputVal_street' value=$cPost_var['strasse']}
            {elseif isset($Kunde->cStrasse)}
                {assign var='inputVal_street' value=$Kunde->cStrasse}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'street', 'strasse',
                    {$inputVal_street|default:null}, {lang key='street' section='account data'},
                    true, null, 'billing address-line1'
                ]
            }
        </div>

        <div class="col-xs-12 col-md-4">
            {if isset($cPost_var['hausnummer'])}
                {assign var='inputVal_streetnumber' value=$cPost_var['hausnummer']}
            {elseif isset($Kunde->cHausnummer)}
                {assign var='inputVal_streetnumber' value=$Kunde->cHausnummer}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'streetnumber', 'hausnummer',
                    {$inputVal_streetnumber|default:null}, {lang key='streetnumber' section='account data'},
                    true, null, 'billing address-line2'
                ]
            }
        </div>
    </div>
    {* adress addition *}
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_adresszusatz !== 'N'}
        <div class="row">
            <div class="col-xs-12 col-md-6">
                {if isset($cPost_var['adresszusatz'])}
                    {assign var='inputVal_street2' value=$cPost_var['adresszusatz']}
                {elseif isset($Kunde->cAdressZusatz)}
                    {assign var='inputVal_street2' value=$Kunde->cAdressZusatz}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'text', 'street2', 'adresszusatz',
                        {$inputVal_street2|default:null}, {lang key='street2' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_adresszusatz, null, 'billing address-line3'
                    ]
                }
            </div>
        </div>
    {/if}
    {* country *}
    {if isset($cPost_var['land'])}
        {assign var='cIso' value=$cPost_var['land']}
    {elseif !empty($Kunde->cLand)}
        {assign var='cIso' value=$Kunde->cLand}
    {else}
        {assign var='cIso' value=$defaultCountry}
    {/if}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.land)} has-error{/if}">
                <label class="control-label" for="country">{lang key='country' section='account data'}</label>
                <select name="land" id="country" class="country-input form-control" required autocomplete="billing country">
                    <option value="" disabled>{lang key='country' section='account data'}</option>
                    {foreach $laender as $land}
                        <option value="{$land->getISO()}" {if $cIso === $land->getISO()}selected="selected"{/if}>{$land->getName()}</option>
                    {/foreach}
                </select>
                {if isset($fehlendeAngaben.land)}
                    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                        {lang key='fillOut' section='global'}
                    </div>
                {/if}
            </div>
        </div>
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland === 'N'}
    </div>
    {/if} {* close row if there won't follow another form-group *}

    {if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland !== 'N'}
        {getStates cIso=$cIso assign='oStates'}
        {if isset($cPost_var['bundesland'])}
            {assign var='cState' value=$cPost_var['bundesland']}
        {elseif !empty($Kunde->cBundesland)}
            {assign var='cState' value=$Kunde->cBundesland}
        {else}
            {assign var='cState' value=''}
        {/if}
        <div class="col-xs-12 col-md-6">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.bundesland)} has-error{/if}">
                <label class="control-label" for="state">{lang key='state' section='account data'}
                    {if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland !== 'Y'}
                        <span class="optional"> - {lang key='optional'}</span>
                    {/if}
                </label>
                {if !empty($oStates)}
                    <select
                    title="{lang key=pleaseChoose}"
                    name="bundesland"
                    id="state"
                    class="form-control state-input"
                    autocomplete="billing address-level1"
                    {if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland === 'Y'} required{/if}
                    >
                        <option value="" selected disabled>{lang key='pleaseChoose'}</option>
                        {foreach $oStates as $oState}
                            <option value="{$oState->cCode}" {if $cState === $oState->cName || $cState === $oState->cCode}selected{/if}>{$oState->cName}</option>
                        {/foreach}
                    </select>
                {else}
                    <input
                    type="text"
                    title="{lang key=pleaseChoose}"
                    name="bundesland"
                    value="{$cState}"
                    id="state"
                    class="form-control"
                    placeholder="{lang key='state' section='account data'}"
                    autocomplete="billing address-level1"
                    {if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland === 'Y'} required{/if}
                    >
                {/if}

                {if isset($fehlendeAngaben.bundesland)}
                    <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                        {lang key='fillOut' section='global'}
                    </div>
                {/if}
            </div>
        </div>
    </div>{* close row for country *}
    {/if}
    {* zip / city *}
    <div class="row">
        <div class="col-xs-12 col-md-3">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.plz)} has-error{/if}">
                <label class="control-label" for="postcode">{lang key='plz' section='account data'}</label>
                <input
                type="text"
                name="plz"
                value="{if isset($cPost_var['plz'])}{$cPost_var['plz']}{elseif isset($Kunde->cPLZ)}{$Kunde->cPLZ}{/if}"
                id="postcode"
                class="postcode_input form-control"
                placeholder="{lang key='plz' section='account data'}"
                required
                autocomplete="billing postal-code"
                >
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

        <div class="col-xs-12 col-md-6">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.ort)} has-error{/if}">
                <label class="control-label" for="city">{lang key='city' section='account data'}</label>
                <input
                type="text"
                name="ort"
                value="{if isset($cPost_var['ort'])}{$cPost_var['ort']}{elseif isset($Kunde->cOrt)}{$Kunde->cOrt}{/if}"
                id="city"
                class="city_input form-control typeahead"
                placeholder="{lang key='city' section='account data'}"
                required
                autocomplete="billing address-level2"
                >
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
    {* UStID *}
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_ustid !== 'N'}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="form-group float-label-control{if isset($fehlendeAngaben.ustid)} has-error{/if}">
                <label class="control-label"
                       for="ustid">{lang key='ustid' section='account data'}
                    {if $Einstellungen.kunden.kundenregistrierung_abfragen_ustid !== 'Y'}
                        <span class="optional"> - {lang key='optional'}</span>
                    {/if}
                </label>
                <input
                type="text"
                name="ustid"
                value="{if isset($cPost_var['ustid'])}{$cPost_var['ustid']}{elseif isset($Kunde->cUSTID)}{$Kunde->cUSTID}{/if}"
                id="ustid"
                class="form-control"
                placeholder="{lang key='ustid' section='account data'}"
                {if $Einstellungen.kunden.kundenregistrierung_abfragen_ustid === 'Y'} required{/if}
                >
                {if isset($fehlendeAngaben.ustid)}
                <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                    {if $fehlendeAngaben.ustid == 1}
                        {lang key='fillOut' section='global'}
                    {elseif $fehlendeAngaben.ustid == 2}
                        {assign var=errorinfo value=","|explode:$fehlendeAngaben.ustid_err}
                        {if $errorinfo[0] == 100}{lang key='ustIDError100' section='global'}{/if}
                        {if $errorinfo[0] == 110}{lang key='ustIDError110' section='global'}{/if}
                        {if $errorinfo[0] == 120}{lang key='ustIDError120' section='global'}{$errorinfo[1]}{/if}
                        {if $errorinfo[0] == 130}{lang key='ustIDError130' section='global'}{$errorinfo[1]}{/if}
                    {elseif $fehlendeAngaben.ustid == 4}
                        {assign var=errorinfo value=","|explode:$fehlendeAngaben.ustid_err}
                        {lang key='ustIDError200' section='global'}{$errorinfo[1]}
                    {elseif $fehlendeAngaben.ustid == 5}
                        {lang key='ustIDCaseFive' section='global'}
                    {/if}
                </div>
                {/if}
            </div>
        </div>
    </div>
    {/if}
</fieldset>

<fieldset>
   <legend>{lang key='contactInformation' section='account data'}</legend>
    {* E-Mail *}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            {if isset($cPost_var['email'])}
                {assign var='inputVal_email' value=$cPost_var['email']}
            {elseif isset($Kunde->cMail)}
                {assign var='inputVal_email' value=$Kunde->cMail}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'email', 'email', 'email',
                    {$inputVal_email|default:null}, {lang key='email' section='account data'},
                    true, null, 'billing email'
                ]
            }
        </div>
    </div>
    {* phone & fax *}
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_tel !== 'N' || $Einstellungen.kunden.kundenregistrierung_abfragen_fax !== 'N'}
        <div class="row">
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_tel !== 'N'}
            <div class="col-xs-12 col-md-6">
                {if isset($cPost_var['tel'])}
                    {assign var='inputVal_tel' value=$cPost_var['tel']}
                {elseif isset($Kunde->cTel)}
                    {assign var='inputVal_tel' value=$Kunde->cTel}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'tel', 'tel', 'tel',
                        {$inputVal_tel|default:null}, {lang key='tel' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_tel, null, 'billing home tel'
                    ]
                }
            </div>
            {/if}

            {if $Einstellungen.kunden.kundenregistrierung_abfragen_fax !== 'N'}
            <div class="col-xs-12 col-md-6">
                {if isset($cPost_var['fax'])}
                    {assign var='inputVal_fax' value=$cPost_var['fax']}
                {elseif isset($Kunde->cFax)}
                    {assign var='inputVal_fax' value=$Kunde->cFax}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'tel', 'fax', 'fax',
                        {$inputVal_fax|default:null}, {lang key='fax' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_fax, null, 'billing fax tel'
                    ]
                }
            </div>
            {/if}
        </div>
    {/if}

    {if $Einstellungen.kunden.kundenregistrierung_abfragen_mobil !== 'N' || $Einstellungen.kunden.kundenregistrierung_abfragen_www !== 'N'}
        <div class="row">
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_mobil !== 'N'}
                <div class="col-xs-12 col-md-6">
                    {if isset($cPost_var['mobil'])}
                        {assign var='inputVal_mobile' value=$cPost_var['mobil']}
                    {elseif isset($Kunde->cMobil)}
                        {assign var='inputVal_mobile' value=$Kunde->cMobil}
                    {/if}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'tel', 'mobile', 'mobil',
                            {$inputVal_mobile|default:null}, {lang key='mobile' section='account data'},
                            $Einstellungen.kunden.kundenregistrierung_abfragen_mobil, null, 'billing mobile tel'
                        ]
                    }
                </div>
            {/if}

            {if $Einstellungen.kunden.kundenregistrierung_abfragen_www !== 'N'}
                <div class="col-xs-12 col-md-6">
                    {if isset($cPost_var['www'])}
                        {assign var='inputVal_www' value=$cPost_var['www']}
                    {elseif isset($Kunde->cWWW)}
                        {assign var='inputVal_www' value=$Kunde->cWWW}
                    {/if}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'text', 'www', 'www',
                            {$inputVal_www|default:null}, {lang key='www' section='account data'},
                            $Einstellungen.kunden.kundenregistrierung_abfragen_www, null, 'billing url'
                        ]
                    }
                </div>
            {/if}
        </div>
    {/if}

    {if $Einstellungen.kunden.kundenregistrierung_abfragen_geburtstag !== 'N'}
        <div class="row">
            <div class="col-xs-12 col-md-6">
                {if isset($cPost_var['geburtstag'])}
                    {assign var='inputVal_birthday' value=$cPost_var['geburtstag']}
                {elseif isset($Kunde->dGeburtstag_formatted)}
                    {assign var='inputVal_birthday' value=$Kunde->dGeburtstag_formatted}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'date', 'birthday', 'geburtstag',
                        {$inputVal_birthday|default:null|date_format:"%Y-%m-%d"}, {lang key='birthday' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_geburtstag, null, 'billing bday'
                    ]
                }
            </div>
        </div>
    {/if}
</fieldset>
{if $Einstellungen.kundenfeld.kundenfeld_anzeigen === 'Y' && !empty($oKundenfeld_arr)}
<fieldset>
    <div class="row">
        <div class="col-xs-12 col-md-6">
            {if $step === 'formular' || $step === 'edit_customer_address' || $step === 'Lieferadresse' || $step === 'rechnungsdaten'}
                {if ($step === 'formular' || $step === 'edit_customer_address') && isset($Kunde)}
                    {assign var="customerAttributes" value=$Kunde->getCustomerAttributes()}
                {/if}
                {foreach $oKundenfeld_arr as $oKundenfeld}
                    {assign var="kKundenfeld" value=$oKundenfeld->getID()}
                    {if isset($customerAttributes[$kKundenfeld])}
                        {assign var="cKundenattributWert" value=$customerAttributes[$kKundenfeld]->getValue()}
                        {assign var="isKundenattributEditable" value=$customerAttributes[$kKundenfeld]->isEditable()}
                    {else}
                        {assign var="cKundenattributWert" value=''}
                        {assign var="isKundenattributEditable" value=true}
                    {/if}
                    <div class="form-group float-label-control{if isset($fehlendeAngaben.custom[$kKundenfeld])} has-error{/if}">
                        <label class="control-label" for="custom_{$kKundenfeld}">{$oKundenfeld->getLabel()}
                            {if !$oKundenfeld->isRequired()}
                                <span class="optional"> - {lang key='optional'}</span>
                            {/if}
                        </label>
                        {if $oKundenfeld->getType() !== \JTL\Customer\CustomerField::TYPE_SELECT}
                            <input
                            type="{if $oKundenfeld->getType() === \JTL\Customer\CustomerField::TYPE_NUMBER}number{elseif $oKundenfeld->getType() === \JTL\Customer\CustomerField::TYPE_DATE}date{else}text{/if}"
                            name="custom_{$kKundenfeld}"
                            id="custom_{$kKundenfeld}"
                            value="{$cKundenattributWert}"
                            class="form-control"
                            placeholder="{$oKundenfeld->getLabel()}"
                            {if $oKundenfeld->isRequired()} required{/if}
                            data-toggle="floatLabel"
                            data-value="no-js"
                            {if !$isKundenattributEditable}readonly{/if}/>
                        {else}
                            <select name="custom_{$kKundenfeld}" class="form-control" {if !$isKundenattributEditable}disabled{/if}{if $oKundenfeld->isRequired()} required{/if}>
                                <option value="" selected disabled>{lang key='pleaseChoose'}</option>
                                {foreach $oKundenfeld->getValues() as $oKundenfeldWert}
                                    <option value="{$oKundenfeldWert}" {if ($oKundenfeldWert == $cKundenattributWert)}selected{/if}>{$oKundenfeldWert}</option>
                                {/foreach}
                            </select>
                        {/if}
                        {if isset($fehlendeAngaben.custom[$kKundenfeld])}
                            <div class="form-error-msg text-danger"><i class="fa fa-warning"></i>
                                {if $fehlendeAngaben.custom[$kKundenfeld] === 1}
                                    {lang key='fillOut' section='global'}
                                {elseif $fehlendeAngaben.custom[$kKundenfeld] === 2}
                                    {lang key='invalidDateformat' section='global'}
                                {elseif $fehlendeAngaben.custom[$kKundenfeld] === 3}
                                    {lang key='invalidDate' section='global'}
                                {elseif $fehlendeAngaben.custom[$kKundenfeld] === 4}
                                    {lang key='invalidInteger' section='global'}
                                {/if}
                            </div>
                        {/if}
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>
</fieldset>
{/if}
{if !isset($fehlendeAngaben)}
    {assign var="fehlendeAngaben" value=array()}
{/if}
{if !isset($cPost_arr)}
    {assign var="cPost_arr" value=array()}
{/if}
{hasCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$fehlendeAngaben cPost_arr=$cPost_arr bReturn='bHasCheckbox'}
{if $bHasCheckbox}
<fieldset>
    {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$fehlendeAngaben cPost_arr=$cPost_arr}
</fieldset>
{/if}

{if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true)
&& isset($Einstellungen.kunden.registrieren_captcha) && $Einstellungen.kunden.registrieren_captcha !== 'N' && empty($Kunde->kKunde)}
    <hr>
    <div class="form-group float-label-control{if isset($fehlendeAngaben.captcha) && $fehlendeAngaben.captcha != false} has-error{/if}">
        {captchaMarkup getBody=true}
    </div>
    <hr>
{/if}
