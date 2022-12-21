{if count($mailTemplates) > 0}
    <div class="card">
        <div class="card-header">
            <div class="subheading1">{$heading}</div>
            <hr class="mb-n3">
        </div>
        <div class="card-body table-responsive">
            <table class="list table table-sm table-hover">
                <thead>
                <tr><th class="th-1">&nbsp;</th>
                    <th class="text-left">{__('template')}</th>
                    <th class="text-center">{__('type')}</th>
                    <th class="text-center">{__('active')}</th>
                    <th class="text-center">{__('options')}</th>
                </tr>
                </thead>
                <tbody>
                {$jtl_token}
                {foreach $mailTemplates as $template}
                    <tr>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="kEmailvorlage[]" id="cb-check-{$template@index}" type="checkbox" value="{$template->getID()}" />
                                <label class="custom-control-label" for="cb-check-{$template@index}"></label>
                            </div>
                        </td>
                        <td><label for="cb-check-{$template@index}">{if $isPlugin|default:false}{$template->getName()}{else}{__('name_'|cat:$template->getModuleID())}{/if}</label></td>
                        <td class="text-center">{$template->getType()}</td>
                        <td class="text-center" id="tplState_{$template->getID()}">
                            {include file='snippets/mailtemplate_state.tpl' template=$template}
                        </td>
                        <td class="text-center">
                            <div>
                                {if $template->getPluginID() > 0}
                                    <input type="hidden" name="kPlugin" value="{$template->getPluginID()}" />
                                {/if}
                                <div class="btn-group">
                                    <button type="button" data-id="{$template->getID()}" class="btn btn-link px-2 btn-syntaxcheck" title="{__('Check syntax')}" data-toggle="tooltip" data-placement="top" >
                                        <span class="icon-hover">
                                            <span class="fal fa-check"></span>
                                            <span class="fas fa-check"></span>
                                        </span>
                                    </button>
                                    <button type="button" data-id="{$template->getID()}" class="btn btn-link px-2 reset btn-reset" title="{__('reset')}" data-toggle="tooltip" data-placement="top" >
                                        <span class="icon-hover">
                                            <span class="fal fa-refresh"></span>
                                            <span class="fas fa-refresh"></span>
                                        </span>
                                    </button>
                                    <button type="submit" name="preview" value="{$template->getID()}" title="{__('testmail')}" class="btn btn-link px-2 mail" data-toggle="tooltip" data-placement="top" >
                                        <span class="icon-hover">
                                            <span class="fal fa-envelope"></span>
                                            <span class="fas fa-envelope"></span>
                                        </span>
                                    </button>
                                    <button type="button"  data-id="{$template->getID()}" class="btn btn-link px-2 btn-edit" title="{__('modify')}" data-toggle="tooltip" data-placement="top" >
                                        <a href="{$adminURL}{$route}?kEmailvorlage={$template->getID()}&token={$smarty.session.jtl_token}"
                                           class="btn btn-link px-2"
                                           title="{__('modify')}"
                                           data-toggle="tooltip">
                                                    <span class="icon-hover">
                                                        <span class="fal fa-edit"></span>
                                                        <span class="fas fa-edit"></span>
                                                    </span>
                                        </a>
                                     </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}
