{if isset($section) && $section->getName()}
    {assign var=cTitel value=__('settings')|cat:': '|cat:$section->getName()}
{else}
    {assign var=cTitel value=__('settings')}
{/if}
{if isset($cSearch) && $cSearch|strlen  > 0}
    {assign var=cTitel value=$cSearch}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=__('settings') cBeschreibung=__('preferencesDesc') cDokuURL=__('preferencesURL')}
<div id="content">
    <div class="table-responsive">
        <table class="list table">
            <tbody>
            {foreach $sectionOverview as $section}
                <tr>
                    <td>{$section->getName()}</td>
                    <td>{$section->getConfigCount()} {__('settings')}</td>
                    <td>
                        <a href="{$adminURL}{$route}/{$section->getID()}" class="btn btn-primary">{__('configure')}</a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
