<div id="probleme" class="tab-pane fade {if isset($cTab) && $cTab === 'probleme'} active in{/if}">
    {if $PluginErrorCount > 0}
    <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
        {$jtl_token}
        <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#pluginListProblems#}</h3>
            </div>
            <div class="table-responsive">
                <table class="list table">
                <thead>
                <tr>
                    <th></th>
                    <th class="tleft">{#pluginName#}</th>
                    <th>{#status#}</th>
                    <th>{#pluginVersion#}</th>
                    <th>{#pluginInstalled#}</th>
                    <th>{#pluginFolder#}</th>
                    <th>{#pluginEditLocales#}</th>
                    <th>{#pluginEditLinkgrps#}</th>
                    <th>{#pluginBtnLicence#}</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$PluginInstalliertByStatus_arr.status_3 item=PluginInstalliert}
                    <tr {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$PluginInstalliert->kPlugin}" id="plugin-problem-{$PluginInstalliert->kPlugin}" />
                        </td>
                        <td>
                            <label for="plugin-problem-{$PluginInstalliert->kPlugin}">{$PluginInstalliert->cName}</label>
                            {if $PluginInstalliert->updateAvailable === true || (isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0)}
                                <p>
                                    {if $PluginInstalliert->cFehler === ''}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}
                                    {else}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}. <br />{#pluginUpdateExistsError#}: <br />{$PluginInstalliert->cUpdateFehler}
                                    {/if}
                                </p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                                <span class="label {if $PluginInstalliert->nStatus === \Plugin\Plugin::PLUGIN_ACTIVATED}success label-success{elseif $PluginInstalliert->nStatus == 1}success label-info{elseif $PluginInstalliert->nStatus == 3}success label-default{elseif $PluginInstalliert->nStatus == 4 || $PluginInstalliert->nStatus == 5}info label-info{elseif $PluginInstalliert->nStatus == 6}danger label-danger{/if}">
                                    {$mapper->map($PluginInstalliert->nStatus)}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter">{number_format($PluginInstalliert->nVersion / 100, 2)}{if $PluginInstalliert->updateAvailable === true} <span class="error">{number_format((float)$PluginInstalliert->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter">{$PluginInstalliert->dInstalliert_DE}</td>
                        <td class="tcenter">{$PluginInstalliert->cVerzeichnis}</td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->oPluginSprachvariableAssoc_arr) && $PluginInstalliert->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$PluginInstalliert->kPlugin}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i></a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->oPluginFrontendLink_arr) && $PluginInstalliert->oPluginFrontendLink_arr|@count > 0}
                                <a href="links.php?kPlugin={$PluginInstalliert->kPlugin}"
                                   class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i></a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->cLizenzKlasse) && $PluginInstalliert->cLizenzKlasse|strlen > 0}
                                {if $PluginInstalliert->cLizenz && $PluginInstalliert->cLizenz|strlen > 0}
                                    <strong>{#pluginBtnLicence#}:</strong> {$PluginInstalliert->cLizenz}
                                    <button name="lizenzkey" type="submit" class="btn btn-default" value="{$PluginInstalliert->kPlugin}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceChange#}</button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary" value="{$PluginInstalliert->kPlugin}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceAdd#}</button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}
                                <a onclick="ackCheck({$PluginInstalliert->kPlugin}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach from=$PluginInstalliertByStatus_arr.status_4 item=PluginInstalliert}
                    <tr {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$PluginInstalliert->kPlugin}" id="plugin-problem-{$PluginInstalliert->kPlugin}" />
                        </td>
                        <td>
                            <label for="plugin-problem-{$PluginInstalliert->kPlugin}">{$PluginInstalliert->cName}</label>
                            {if $PluginInstalliert->updateAvailable === true || (isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0)}
                                <p>
                                    {if $PluginInstalliert->cFehler === ''}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}
                                    {else}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}. <br />{#pluginUpdateExistsError#}: <br />{$PluginInstalliert->cUpdateFehler}
                                    {/if}
                                </p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                            <span class="label {if $PluginInstalliert->nStatus === \Plugin\Plugin::PLUGIN_ACTIVATED}success label-success{elseif $PluginInstalliert->nStatus == 1}success label-info{elseif $PluginInstalliert->nStatus == 3}success label-default{elseif $PluginInstalliert->nStatus == 4 || $PluginInstalliert->nStatus == 5}info label-info{elseif $PluginInstalliert->nStatus == 6}danger label-danger{/if}">
                                {$mapper->map($PluginInstalliert->nStatus)}
                            </span>
                            </h4>
                        </td>
                        <td class="tcenter">{number_format($PluginInstalliert->nVersion / 100, 2)}{if $PluginInstalliert->updateAvailable === true} <span class="error">{number_format((float)$PluginInstalliert->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter">{$PluginInstalliert->dInstalliert_DE}</td>
                        <td class="tcenter">{$PluginInstalliert->cVerzeichnis}</td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->oPluginSprachvariableAssoc_arr) && $PluginInstalliert->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$PluginInstalliert->kPlugin}" class="btn btn-default">{#modify#}</a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->oPluginFrontendLink_arr) && $PluginInstalliert->oPluginFrontendLink_arr|@count > 0}
                                <a href="links.php?kPlugin={$PluginInstalliert->kPlugin}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->cLizenzKlasse) && $PluginInstalliert->cLizenzKlasse|strlen > 0}
                                {if $PluginInstalliert->cLizenz && $PluginInstalliert->cLizenz|strlen > 0}
                                    {$PluginInstalliert->cLizenz|truncate:35:'...':true}
                                    <button name="lizenzkey" type="submit" class="btn btn-default" value="{$PluginInstalliert->kPlugin}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceChange#}
                                    </button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary" value="{$PluginInstalliert->kPlugin}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceAdd#}
                                    </button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}
                                <a onclick="ackCheck({$PluginInstalliert->kPlugin}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach from=$PluginInstalliertByStatus_arr.status_5 item=PluginInstalliert}
                    <tr {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$PluginInstalliert->kPlugin}" id="plugin-problem-{$PluginInstalliert->kPlugin}"/>
                        </td>
                        <td>
                            <label for="plugin-problem-{$PluginInstalliert->kPlugin}">{$PluginInstalliert->cName}</label>
                            {if $PluginInstalliert->updateAvailable === true || (isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0)}
                                <p>
                                    {if $PluginInstalliert->cFehler === ''}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}
                                    {else}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}. <br />{#pluginUpdateExistsError#}: <br />{$PluginInstalliert->cUpdateFehler}
                                    {/if}
                                </p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                                <span class="label {if $PluginInstalliert->nStatus === \Plugin\Plugin::PLUGIN_ACTIVATED}success label-success{elseif $PluginInstalliert->nStatus == 1}success label-info{elseif $PluginInstalliert->nStatus == 3}success label-default{elseif $PluginInstalliert->nStatus == 4 || $PluginInstalliert->nStatus == 5}info label-info{elseif $PluginInstalliert->nStatus == 6}danger label-danger{/if}">
                                    {$mapper->map($PluginInstalliert->nStatus)}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter">{number_format($PluginInstalliert->nVersion / 100, 2)}{if $PluginInstalliert->updateAvailable === true} <span class="error">{number_format((float)$PluginInstalliert->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter">{$PluginInstalliert->dInstalliert_DE}</td>
                        <td class="tcenter">{$PluginInstalliert->cVerzeichnis}</td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->oPluginSprachvariableAssoc_arr) && $PluginInstalliert->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$PluginInstalliert->kPlugin}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->oPluginFrontendLink_arr) && $PluginInstalliert->oPluginFrontendLink_arr|@count > 0}
                                <a href="links.php?kPlugin={$PluginInstalliert->kPlugin}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if isset($PluginInstalliert->cLizenzKlasse) && $PluginInstalliert->cLizenzKlasse|strlen > 0}
                                {if $PluginInstalliert->cLizenz && $PluginInstalliert->cLizenz|strlen > 0}
                                    <strong>{#pluginBtnLicence#}:</strong> {$PluginInstalliert->cLizenz}
                                    <button name="lizenzkey" type="submit" class="btn btn-default"
                                            value="{$PluginInstalliert->kPlugin}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceChange#}
                                    </button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary"
                                            value="{$PluginInstalliert->kPlugin}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceAdd#}
                                    </button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}
                                <a onclick="ackCheck({$PluginInstalliert->kPlugin}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach from=$PluginInstalliertByStatus_arr.status_6 item=PluginInstalliert}
                    <tr {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$PluginInstalliert->kPlugin}" id="plugin-problem-{$PluginInstalliert->kPlugin}" />
                        </td>
                        <td>
                            <label for="plugin-problem-{$PluginInstalliert->kPlugin}">{$PluginInstalliert->cName}</label>
                            {if $PluginInstalliert->updateAvailable === true || (isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0)}
                                <p>
                                    {if $PluginInstalliert->cFehler === ''}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}
                                    {else}
                                        {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{#pluginUpdateExists#}. <br />{#pluginUpdateExistsError#}: <br />{$PluginInstalliert->cUpdateFehler}
                                    {/if}
                                </p>
                            {/if}
                        </td>
                        <td class="tcenter plugin-status">
                            <h4 class="label-wrap">
                                <span class="label {if $PluginInstalliert->nStatus === \Plugin\Plugin::PLUGIN_ACTIVATED}success label-success{elseif $PluginInstalliert->nStatus == 1}success label-info{elseif $PluginInstalliert->nStatus == 3}success label-default{elseif $PluginInstalliert->nStatus == 4 || $PluginInstalliert->nStatus == 5}info label-info{elseif $PluginInstalliert->nStatus == 6}danger label-danger{/if}">
                                    {$mapper->map($PluginInstalliert->nStatus)}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter plugin-version">{number_format($PluginInstalliert->nVersion / 100, 2)}{if $PluginInstalliert->updateAvailable === true} <span class="label label-danger error">{number_format((float)$PluginInstalliert->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter plugin-install-date">{$PluginInstalliert->dInstalliert_DE}</td>
                        <td class="tcenter plugin-folder">{$PluginInstalliert->cVerzeichnis}</td>
                        <td class="tcenter plugin-lang-vars">
                            {if isset($PluginInstalliert->oPluginSprachvariableAssoc_arr) && $PluginInstalliert->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$PluginInstalliert->kPlugin}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter plugin-frontend-links">
                            {if isset($PluginInstalliert->oPluginFrontendLink_arr) && $PluginInstalliert->oPluginFrontendLink_arr|@count > 0}
                                <a href="links.php?kPlugin={$PluginInstalliert->kPlugin}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter plugin-license">
                            {if isset($PluginInstalliert->cLizenzKlasse) && $PluginInstalliert->cLizenzKlasse|strlen > 0}
                                {if $PluginInstalliert->cLizenz && $PluginInstalliert->cLizenz|strlen > 0}
                                    <strong>{#pluginBtnLicence#}:</strong> {$PluginInstalliert->cLizenz}
                                    <button name="lizenzkey" type="submit" class="btn btn-default"
                                            value="{$PluginInstalliert->kPlugin}" title="{#modify#}">
                                        <i class="fa fa-edit"></i></button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary"
                                            value="{$PluginInstalliert->kPlugin}" title="{#modify#}">
                                        <i class="fa fa-edit"></i></button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}
                                <a onclick="ackCheck({$PluginInstalliert->kPlugin}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
                <tfoot>
                <tr>
                    <td class="check"><input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" /></td>
                    <td colspan="10"><label for="ALLMSGS3">{#pluginSelectAll#}</label></td>
                </tr>
                </tfoot>
                </table>
            </div>
            <div class="panel-footer">
                <div class="save btn-group">
                    {*<button name="aktivieren" type="submit" class="btn btn-primary">{#pluginBtnActivate#}</button>*}
                    <button name="deaktivieren" type="submit" class="btn btn-warning">{#pluginBtnDeActivate#}</button>
                    <button name="deinstallieren" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {#pluginBtnDeInstall#}</button>
                </div>
            </div>
        </div>
    </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>