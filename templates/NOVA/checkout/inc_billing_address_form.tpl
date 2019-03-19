{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<fieldset>
    <legend class="mb-5">
        {if isset($checkout)}
            {lang key='proceedNewCustomer' section='checkout'}
        {elseif $nSeitenTyp === $smarty.const.PAGE_MEINKONTO}
            {lang key='myPersonalData'}
        {else}
            {lang key='address' section='account data'}
        {/if}
    </legend>
    {* salutation / title *}
    {row}
        {col md=2}
            <div class="h5">{lang key='name'}</div>
        {/col}
        {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede !== 'N'}
            {col md=5}
                {formgroup
                    class="{if isset($fehlendeAngaben.anrede)} has-error{/if}"
                    label-for="salutation"
                    label="{lang key='salutation' section='account data'}{if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede === 'O'}<span class='optional'> - {lang key='optional'}</span>{/if}"
                }
                {select name="anrede" id="salutation" required=($Einstellungen.kunden.kundenregistrierung_abfragen_anrede === 'Y') autocomplete="billing sex"}
                    <option value="" selected="selected" disabled>{lang key='salutation' section='account data'}</option>
                    <option value="w" {if isset($cPost_var['anrede']) && $cPost_var['anrede'] === 'w'}selected="selected"{elseif isset($Kunde->cAnrede) && $Kunde->cAnrede === 'w'}selected="selected"{/if}>{lang key='salutationW'}</option>
                    <option value="m" {if isset($cPost_var['anrede']) && $cPost_var['anrede'] === 'm'}selected="selected"{elseif isset($Kunde->cAnrede) && $Kunde->cAnrede === 'm'}selected="selected"{/if}>{lang key='salutationM'}</option>
                {/select}
                {if isset($fehlendeAngaben.anrede)}
                    <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                        {lang key='fillOut'}
                    </div>
                {/if}
                {/formgroup}
            {/col}
        {/if}

        {if $Einstellungen.kunden.kundenregistrierung_abfragen_titel !== 'N'}
            {col cols=12 md=5}
                {if isset($cPost_var['titel'])}
                    {assign var=inputVal_title value=$cPost_var['titel']}
                {elseif isset($Kunde->cTitel)}
                    {assign var=inputVal_title value=$Kunde->cTitel}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'text', 'title', 'titel',
                        {$inputVal_title|default:null}, {lang key='title' section='account data'},
                        {$Einstellungen.kunden.kundenregistrierung_abfragen_titel}, null, 'billing honorific-prefix'
                    ]
                }
            {/col}
        {/if}
    {/row}
    {* firstname lastname *}
    {row}
        {col md=2}{/col}
        {col cols=12 md=5}
            {if isset($cPost_var['vorname'])}
                {assign var=inputVal_firstName value=$cPost_var['vorname']}
            {elseif isset($Kunde->cVorname)}
                {assign var=inputVal_firstName value=$Kunde->cVorname}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    "text", "firstName", "vorname",
                    {$inputVal_firstName|default:null}, {lang key='firstName' section='account data'},
                    {$Einstellungen.kunden.kundenregistrierung_pflicht_vorname}, null, "billing given-name"
                ]
            }
        {/col}
        {col cols=12 md=5}
            {if isset($cPost_var['nachname'])}
                {assign var=inputVal_lastName value=$cPost_var['nachname']}
            {elseif isset($Kunde->cNachname)}
                {assign var=inputVal_lastName value=$Kunde->cNachname}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'lastName', 'nachname',
                    {$inputVal_lastName|default:null}, {lang key='lastName' section='account data'},
                    true, null, 'billing family-name'
                ]
            }
        {/col}
    {/row}
    {* firm / firmtext *}
    {row}
        {col md=2}{/col}
        {if $Einstellungen.kunden.kundenregistrierung_abfragen_firma !== 'N'}
        {col cols=12 md=5}
            {if isset($cPost_var['firma'])}
                {assign var=inputVal_firm value=$cPost_var['firma']}
            {elseif isset($Kunde->cFirma)}
                {assign var=inputVal_firm value=$Kunde->cFirma}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'firm', 'firma',
                    {$inputVal_firm|default:null}, {lang key='firm' section='account data'},
                    $Einstellungen.kunden.kundenregistrierung_abfragen_firma, null, 'billing organization'
                ]
            }
        {/col}
        {/if}

        {if $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz !== 'N'}
        {col cols=12 md=5}
            {if isset($cPost_var['firmazusatz'])}
                {assign var=inputVal_firmext value=$cPost_var['firmazusatz']}
            {elseif isset($Kunde->cZusatz)}
                {assign var=inputVal_firmext value=$Kunde->cZusatz}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'firmext', 'firmazusatz',
                    {$inputVal_firmext|default:null}, {lang key='firmext' section='account data'},
                    $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz
                ]
            }
        {/col}
        {/if}
    {/row}

    <hr class="mt-3 mb-5">

    {* street / number *}
    {row}
        {col md=2}
            <div class="h5">{lang key='billingAdress' section='account data'}</div>
        {/col}
        {col cols=12 md=7}
            {if isset($cPost_var['strasse'])}
                {assign var=inputVal_street value=$cPost_var['strasse']}
            {elseif isset($Kunde->cStrasse)}
                {assign var=inputVal_street value=$Kunde->cStrasse}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'street', 'strasse',
                    {$inputVal_street|default:null}, {lang key='street' section='account data'},
                    true, null, 'billing address-line1'
                ]
            }
        {/col}

        {col cols=12 md=3}
            {if isset($cPost_var['hausnummer'])}
                {assign var=inputVal_streetnumber value=$cPost_var['hausnummer']}
            {elseif isset($Kunde->cHausnummer)}
                {assign var=inputVal_streetnumber value=$Kunde->cHausnummer}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'text', 'streetnumber', 'hausnummer',
                    {$inputVal_streetnumber|default:null}, {lang key='streetnumber' section='account data'},
                    true, null, 'billing address-line2'
                ]
            }
        {/col}
    {/row}
    {* adress addition *}
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_adresszusatz !== 'N'}
        {row}
            {col md=2}{/col}
            {col cols=12 md=5}
                {if isset($cPost_var['adresszusatz'])}
                    {assign var=inputVal_street2 value=$cPost_var['adresszusatz']}
                {elseif isset($Kunde->cAdressZusatz)}
                    {assign var=inputVal_street2 value=$Kunde->cAdressZusatz}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'text', 'street2', 'adresszusatz',
                        {$inputVal_street2|default:null}, {lang key='street2' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_adresszusatz, null, 'billing address-line3'
                    ]
                }
            {/col}
        {/row}
    {/if}
    {* country *}
    {if isset($cPost_var['land'])}
        {assign var=cIso value=$cPost_var['land']}
    {elseif !empty($Kunde->cLand)}
        {assign var=cIso value=$Kunde->cLand}
    {elseif !empty($Einstellungen.kunden.kundenregistrierung_standardland)}
        {assign var=cIso value=$Einstellungen.kunden.kundenregistrierung_standardland}
    {elseif isset($laender[0]->cISO)}
        {assign var=cIso value=$laender[0]->cISO}
    {else}
        {assign var=cIso value=''}
    {/if}
    {row}
        {col md=2}{/col}
        {col cols=12 md=5}
            {formgroup
                class="{if isset($fehlendeAngaben.land)} has-error{/if}"
                label-for="country"
                label="{lang key='country' section='account data'}"
            }
                {select name="land" id="country" class="country-input" required=true autocomplete="billing country"}
                    <option value="" disabled>{lang key='country' section='account data'}</option>
                    {foreach $laender as $land}
                        <option value="{$land->cISO}" {if $cIso === $land->cISO}selected="selected"{/if}>{$land->cName}</option>
                    {/foreach}
                {/select}
                {if isset($fehlendeAngaben.land)}
                    <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                        {lang key='fillOut' section='global'}
                    </div>
                {/if}
            {/formgroup}
        {/col}
        {if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland !== 'N'}
            {getStates cIso=$cIso assign='oStates'}
            {if isset($cPost_var['bundesland'])}
                {assign var=cState value=$cPost_var['bundesland']}
            {elseif !empty($Kunde->cBundesland)}
                {assign var=cState value=$Kunde->cBundesland}
            {else}
                {assign var=cState value=''}
            {/if}
            {col cols=12 md=5}
                {formgroup class="{if isset($fehlendeAngaben.bundesland)} has-error{/if}"
                    label-for="state"
                    label="{lang key='state' section='account data'}{if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland !== 'Y'}<span class='optional'> - {lang key='optional'}</span>{/if}"
                }
                    {if !empty($oStates)}
                        {select
                        title="{lang key=pleaseChoose}"
                        name="bundesland"
                        id="state"
                        class="state-input"
                        autocomplete="billing address-level1"
                        required=($Einstellungen.kunden.kundenregistrierung_abfragen_bundesland === 'Y')}
                            <option value="" selected disabled>{lang key='pleaseChoose'}</option>
                            {foreach $oStates as $oState}
                                <option value="{$oState->cCode}" {if $cState === $oState->cName || $cState === $oState->cCode}selected{/if}>{$oState->cName}</option>
                            {/foreach}
                        {/select}
                    {else}
                        {input
                        type="text"
                        title="{lang key=pleaseChoose}"
                        name="bundesland"
                        value=$cState
                        id="state"
                        placeholder="{lang key='state' section='account data'}"
                        autocomplete="billing address-level1"
                        required=($Einstellungen.kunden.kundenregistrierung_abfragen_bundesland === 'Y')
                        }
                    {/if}

                    {if isset($fehlendeAngaben.bundesland)}
                        <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                            {lang key='fillOut' section='global'}
                        </div>
                    {/if}
                {/formgroup}
            {/col}
        {/if}
    {/row}
    {* zip / city *}
    {row}
        {col md=2}{/col}
        {col cols=12 md=3}
            {formgroup
                class="{if isset($fehlendeAngaben.plz)} has-error{/if}"
                label-for="postcode"
                label={lang key='plz' section='account data'}
            }
                {input
                type="text"
                name="plz"
                value="{if isset($cPost_var['plz'])}{$cPost_var['plz']}{elseif isset($Kunde->cPLZ)}{$Kunde->cPLZ}{/if}"
                id="postcode"
                class="postcode_input"
                placeholder="{lang key='plz' section='account data'}"
                required=true
                autocomplete="billing postal-code"
                }
                {if isset($fehlendeAngaben.plz)}
                    <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                        {if $fehlendeAngaben.plz >= 2}
                            {lang key='checkPLZCity' section='checkout'}
                        {else}
                            {lang key='fillOut' section='global'}
                        {/if}
                    </div>
                {/if}
            {/formgroup}
        {/col}
        {col cols=12 md=5}
            {formgroup
                class="{if isset($fehlendeAngaben.ort)} has-error{/if}"
                label-for="city"
                label={lang key='city' section='account data'}
            }
                {input
                type="text"
                name="ort"
                value="{if isset($cPost_var['ort'])}{$cPost_var['ort']}{elseif isset($Kunde->cOrt)}{$Kunde->cOrt}{/if}"
                id="city"
                class="city_input typeahead"
                placeholder="{lang key='city' section='account data'}"
                required=true
                autocomplete="billing address-level2"
                }
                {if isset($fehlendeAngaben.ort)}
                    <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
                        {if $fehlendeAngaben.ort==3}
                             {lang key='cityNotNumeric' section='account data'}
                        {else}
                            {lang key='fillOut' section='global'}
                        {/if}
                    </div>
                {/if}
            {/formgroup}
        {/col}
    {/row}
    {* UStID *}
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_ustid !== 'N'}
    {row}
        {col md=2}{/col}
        {col cols=12 md=5}
            {formgroup
                class="{if isset($fehlendeAngaben.ustid)} has-error{/if}"
                label-for="ustid"
                label="{lang key='ustid' section='account data'}{if $Einstellungen.kunden.kundenregistrierung_abfragen_ustid !== 'Y'}<span class='optional'> - {lang key='optional'}</span>{/if}"
            }
                {input
                type="text"
                name="ustid"
                value="{if isset($cPost_var['ustid'])}{$cPost_var['ustid']}{elseif isset($Kunde->cUSTID)}{$Kunde->cUSTID}{/if}"
                id="ustid"
                placeholder="{lang key='ustid' section='account data'}"
                required=($Einstellungen.kunden.kundenregistrierung_abfragen_ustid === 'Y')
                }
                {if isset($fehlendeAngaben.ustid)}
                <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
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
            {/formgroup}
        {/col}
    {/row}
    {/if}
</fieldset>
<hr class="mt-3 mb-5">
<fieldset>
    {* E-Mail *}
    {row}
        {col md=2}
            <div class="h5">{lang key='contactInformation' section='account data'}</div>
        {/col}
        {col cols=12 md=5}
            {if isset($cPost_var['email'])}
                {assign var=inputVal_email value=$cPost_var['email']}
            {elseif isset($Kunde->cMail)}
                {assign var=inputVal_email value=$Kunde->cMail}
            {/if}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'email', 'email', 'email',
                    {$inputVal_email|default:null}, {lang key='email' section='account data'},
                    true, null, 'billing email'
                ]
            }
        {/col}
    {/row}
    {* phone & fax *}
    {if $Einstellungen.kunden.kundenregistrierung_abfragen_tel !== 'N' || $Einstellungen.kunden.kundenregistrierung_abfragen_fax !== 'N'}
        {row}
            {col md=2}{/col}
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_tel !== 'N'}
            {col cols=12 md=5}
                {if isset($cPost_var['tel'])}
                    {assign var=inputVal_tel value=$cPost_var['tel']}
                {elseif isset($Kunde->cTel)}
                    {assign var=inputVal_tel value=$Kunde->cTel}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'tel', 'tel', 'tel',
                        {$inputVal_tel|default:null}, {lang key='tel' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_tel, null, 'billing home tel'
                    ]
                }
            {/col}
            {/if}

            {if $Einstellungen.kunden.kundenregistrierung_abfragen_fax !== 'N'}
            {col cols=12 md=5}
                {if isset($cPost_var['fax'])}
                    {assign var=inputVal_fax value=$cPost_var['fax']}
                {elseif isset($Kunde->cFax)}
                    {assign var=inputVal_fax value=$Kunde->cFax}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'tel', 'fax', 'fax',
                        {$inputVal_fax|default:null}, {lang key='fax' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_fax, null, 'billing fax tel'
                    ]
                }
            {/col}
            {/if}
        {/row}
    {/if}

    {if $Einstellungen.kunden.kundenregistrierung_abfragen_mobil !== 'N' || $Einstellungen.kunden.kundenregistrierung_abfragen_www !== 'N'}
        {row}
            {col md=2}{/col}
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_mobil !== 'N'}
                {col cols=12 md=5}
                    {if isset($cPost_var['mobil'])}
                        {assign var=inputVal_mobile value=$cPost_var['mobil']}
                    {elseif isset($Kunde->cMobil)}
                        {assign var=inputVal_mobile value=$Kunde->cMobil}
                    {/if}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'tel', 'mobile', 'mobil',
                            {$inputVal_mobile|default:null}, {lang key='mobile' section='account data'},
                            $Einstellungen.kunden.kundenregistrierung_abfragen_mobil, null, 'billing mobile tel'
                        ]
                    }
                {/col}
            {/if}

            {if $Einstellungen.kunden.kundenregistrierung_abfragen_www !== 'N'}
                {col cols=12 md=5}
                    {if isset($cPost_var['www'])}
                        {assign var=inputVal_www value=$cPost_var['www']}
                    {elseif isset($Kunde->cWWW)}
                        {assign var=inputVal_www value=$Kunde->cWWW}
                    {/if}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'text', 'www', 'www',
                            {$inputVal_www|default:null}, {lang key='www' section='account data'},
                            $Einstellungen.kunden.kundenregistrierung_abfragen_www, null, 'billing url'
                        ]
                    }
                {/col}
            {/if}
        {/row}
    {/if}

    {if $Einstellungen.kunden.kundenregistrierung_abfragen_geburtstag !== 'N'}
        {row}
            {col md=2}{/col}
            {col cols=12 md=5}
                {if isset($cPost_var['geburtstag'])}
                    {assign var=inputVal_birthday value=$cPost_var['geburtstag']}
                {elseif isset($Kunde->dGeburtstag_formatted)}
                    {assign var=inputVal_birthday value=$Kunde->dGeburtstag_formatted}
                {/if}
                {include file='snippets/form_group_simple.tpl'
                    options=[
                        'text', 'birthday', 'geburtstag',
                        {$inputVal_birthday|default:null}, {lang key='birthday' section='account data'},
                        $Einstellungen.kunden.kundenregistrierung_abfragen_geburtstag, null, 'billing bday'
                    ]
                }
            {/col}
        {/row}
    {/if}
</fieldset>
{if $Einstellungen.kundenfeld.kundenfeld_anzeigen === 'Y' && !empty($oKundenfeld_arr)}
<hr class="mt-3 mb-5">
<fieldset>
    {row}
        {col md=2}
            <div class="h5">{lang key='miscellaneous'}</div>
        {/col}
        {col cols=12 md=5}
            {if $step === 'formular' || $step === 'edit_customer_address' || $step === 'Lieferadresse' || $step === 'rechnungsdaten'}
                {foreach $oKundenfeld_arr as $oKundenfeld}
                    {assign var=kKundenfeld value=$oKundenfeld->kKundenfeld}
                    {formgroup class="{if isset($fehlendeAngaben.custom[$kKundenfeld])} has-error{/if}"
                        label-for="custom_{$kKundenfeld}"
                        label="{$oKundenfeld->cName}{if $oKundenfeld->nPflicht != 1}<span class='optional'> - {lang key='optional'}</span>{/if}"
                    }
                        {if $oKundenfeld->cTyp !== 'auswahl'}
                            {input
                            type="{if $oKundenfeld->cTyp === 'zahl'}number{elseif $oKundenfeld->cTyp === 'datum'}date{else}text{/if}"
                            name="custom_{$kKundenfeld}"
                            id="custom_{$kKundenfeld}"
                            value="{if isset($cKundenattribut_arr[$kKundenfeld]->cWert) && ($step === 'formular' || $step === 'edit_customer_address')}{$cKundenattribut_arr[$kKundenfeld]->cWert}{else}{$Kunde->cKundenattribut_arr[$kKundenfeld]->cWert|default:''}{/if}"
                            placeholder=$oKundenfeld->cName
                            required=(($oKundenfeld->nPflicht == 1 && $oKundenfeld->nEditierbar == 1) || ($oKundenfeld->nEditierbar == 0 && !empty($cKundenattribut_arr[$kKundenfeld]->cWert)))
                            data-toggle="floatLabel"
                            data-value="no-js"
                            readonly=($oKundenfeld->nEditierbar == 0 && !empty($cKundenattribut_arr[$kKundenfeld]->cWert))
                            }
                        {else}
                            {select
                                name="custom_{$kKundenfeld}"
                                disabled=($oKundenfeld->nEditierbar == 0 && !empty($cKundenattribut_arr[$kKundenfeld]->cWert))
                                required=($oKundenfeld->nPflicht == 1)
                            }
                                <option value="" selected disabled>{lang key='pleaseChoose'}</option>
                                {foreach $oKundenfeld->oKundenfeldWert_arr as $oKundenfeldWert}
                                    <option value="{$oKundenfeldWert->cWert}" {if ($step === 'formular' || $step === 'edit_customer_address') && isset($cKundenattribut_arr[$kKundenfeld]->cWert) && ($oKundenfeldWert->cWert == $cKundenattribut_arr[$kKundenfeld]->cWert)}selected{elseif isset($Kunde->cKundenattribut_arr[$kKundenfeld]->cWert) && ($oKundenfeldWert->cWert == $Kunde->cKundenattribut_arr[$kKundenfeld]->cWert)}selected{/if}>{$oKundenfeldWert->cWert}</option>
                                {/foreach}
                            {/select}
                        {/if}
                        {if isset($fehlendeAngaben.custom[$kKundenfeld])}
                            <div class="form-error-msg text-danger"><i class="fas fa-exclamation-triangle"></i>
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
                    {/formgroup}
                {/foreach}
            {/if}
        {/col}
    {/row}
</fieldset>
{/if}
{if !isset($fehlendeAngaben)}
    {assign var=fehlendeAngaben value=array()}
{/if}
{if !isset($cPost_arr)}
    {assign var=cPost_arr value=array()}
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
    {formgroup class="{if isset($fehlendeAngaben.captcha) && $fehlendeAngaben.captcha != false} has-error{/if}"}
        {captchaMarkup getBody=true}
    {/formgroup}
    <hr>
{/if}
