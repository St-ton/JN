{include file='tpl_inc/seite_header.tpl' cTitel=__('configureContactform') cBeschreibung=__('contanctformDesc') cDokuURL=__('cURL')}
<div id="content">
    <div class="tabs">
        <nav class="tabs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if !isset($cTab) || $cTab === 'config'} active{/if}" data-toggle="tab" role="tab" href="#config">
                        {__('settings')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'subjects'} active{/if}" data-toggle="tab" role="tab" href="#subjects">
                        {__('subjects')}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if isset($cTab) && $cTab === 'content'} active{/if}" data-toggle="tab" role="tab" href="#contents">
                        {__('contents')}
                    </a>
                </li>
            </ul>
        </nav>
        <div class="tab-content">
            <div id="config" class="tab-pane fade {if !isset($cTab) || $cTab === 'config'} active show{/if}">
                <form name="einstellen" method="post" action="kontaktformular.php">
                    {$jtl_token}
                    <input type="hidden" name="einstellungen" value="1" />
                    <div class="settings">
                        <div class="subheading1">{__('settings')}</div>
                        <hr class="mb-3">
                        <div>
                            {foreach $Conf as $cnf}
                                {if $cnf->cConf === 'Y'}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="{$cnf->cWertName}">{$cnf->cName}:</label>
                                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            {if $cnf->cInputTyp === 'selectbox'}
                                                <select name="{$cnf->cWertName}" id="{$cnf->cWertName}" class="custom-select combo">
                                                    {foreach $cnf->ConfWerte as $wert}
                                                        <option value="{$wert->cWert}" {if $cnf->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                    {/foreach}
                                                </select>
                                            {else}
                                                <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                            {/if}
                                        </div>
                                        {if isset($cnf->cBeschreibung)}
                                            <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=$cnf->cBeschreibung}</div>
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                        <div class="card-footer save-wrapper">
                            <button type="submit" value="{__('save')}" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="subjects" class="tab-pane fade {if isset($cTab) && $cTab === 'subjects'} active show{/if}">
                <div class="alert alert-info">{__('contanctformSubjectDesc')}</div>
                <div>
                    <div class="subheading1">{__('subjects')}</div>
                    <hr class="mb-3">
                    <div>
                        <div class="table-responsive">
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="tleft">{__('subject')}</th>
                                    <th class="tleft">{__('mail')}</th>
                                    <th>{__('customerGroup')}</th>
                                    <th>{__('actions')}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $Betreffs as $Betreff}
                                    <tr>
                                        <td>
                                            <a href="kontaktformular.php?kKontaktBetreff={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}">{$Betreff->cName}</a>
                                        </td>
                                        <td>{$Betreff->cMail}</td>
                                        <td class="tcenter">{$Betreff->Kundengruppen}</td>
                                        <td class="tcenter">
                                            <a href="kontaktformular.php?del={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}" class="btn btn-danger btn-circle" title="{__('delete')}"><i class="fas fa-trash-alt"></i></a>
                                            <a href="kontaktformular.php?kKontaktBetreff={$Betreff->kKontaktBetreff}&token={$smarty.session.jtl_token}"
                                               class="btn btn-primary btn-circle" title="{__('modify')}"><i class="fal fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer save-wrapper">
                        <a class="btn btn-primary" href="kontaktformular.php?neu=1&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> {__('newSubject')}</a>
                    </div>
                </div>
            </div>
            <div id="contents" class="tab-pane fade {if isset($cTab) && $cTab === 'content'} active show{/if}">
                <form name="einstellen" method="post" action="kontaktformular.php">
                    {$jtl_token}
                    <input type="hidden" name="content" value="1" />
                    <div>
                        <div class="subheading1">{__('contents')}</div>
                        <hr class="mb-3">
                        <div>
                            <div class="settings">
                                {foreach $sprachen as $language}
                                    {assign var=cISO value=$language->getIso()}
                                    {assign var=cISOcat value=$cISO|cat:'_titel'}
                                    <div class="form-group form-row align-items-center">
                                        <label class="col col-sm-4 col-form-label text-sm-right" for="cTitle_{$cISO}">{__('title')} ({$language->getLocalizedName()}):</label>
                                        <span class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                                            <input class="form-control" type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}" value="{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}" tabindex="1" />
                                        </span>
                                    </div>
                                {/foreach}
                                {foreach $sprachen as $language}
                                    {assign var=cISO value=$language->getIso()}
                                    {assign var=cISOcat value=$cISO|cat:'_oben'}
                                    <div class="category">{__('topContent')} ({$language->getLocalizedName()})</div>
                                    <textarea class="ckeditor form-control" name="cContentTop_{$cISO}" id="cContentTop_{$cISO}">{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}</textarea>
                                {/foreach}
                                {foreach $sprachen as $language}
                                    {assign var=cISO value=$language->getIso()}
                                    {assign var=cISOcat value=$cISO|cat:'_unten'}
                                    <div class="category">{__('bottomContent')} ({$language->getLocalizedName()})</div>
                                    <textarea class="ckeditor form-control" name="cContentBottom_{$cISO}" id="cContentBottom_{$cISO}">{if !empty($Content[$cISOcat])}{$Content[$cISOcat]}{/if}</textarea>
                                {/foreach}
                            </div>
                        </div>
                        <div class="card-footer save-wrapper">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
