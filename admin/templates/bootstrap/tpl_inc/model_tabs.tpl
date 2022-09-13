{include file='tpl_inc/seite_header.tpl' cTitel=__('pageTitle') cBeschreibung=__('pageDesc') cDokuURL=__('docURL')}
{$select = $select|default:true}
{$edit = $edit|default:true}
{$delete = $delete|default:false}
{$save = $save|default:false}
{$enable = $enable|default:false}
{$disable = $disable|default:false}
{$action = $action|default:($shopURL|cat:$smarty.server.PHP_SELF)}
{$search = $search|default:false}
{$searchQuery = $searchQuery|default:null}
{$pagination = $pagination|default:null}
{$method = $method|default:'post'}
{$settings = $settings|default:null}
{$tabs = $settings|default:null}

<div id="content">
    <div class="tabs">
        <nav class="tavs-nav">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {if $tab === 'overview'} active{/if}" data-toggle="tab" role="tab" href="#overview">
                        {__('modelHeader')}
                    </a>
                </li>
                {if $settings !== null}
                <li class="nav-item">
                    <a class="nav-link {if $tab === 'settings'} active{/if}" data-toggle="tab" role="tab" href="#config">
                        {__('settings')}
                    </a>
                </li>
                {/if}
            </ul>
        </nav>
        <div class="tab-content">
            <div id="overview" class="tab-pane fade{if $tab === 'overview'} active show{/if}">
                {include file='tpl_inc/model_list.tpl'}
            </div>
            {if $settings !== null}
                <div id="config" class="tab-pane fade{if $tab === 'settings'} active show{/if}">
                    {include file='tpl_inc/config_section.tpl'
                    config=$settings
                    name='einstellen'
                    a='saveSettings'
                    action=$adminURL|cat:'/consent'
                    buttonCaption=__('saveWithIcon')
                    tab='einstellungen'
                    title=__('settings')}
                </div>
            {/if}
        </div>
    </div>
</div>
