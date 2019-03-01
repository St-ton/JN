{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='rss'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('rssSettings') cBeschreibung=__('rssDescription') cDokuURL=__('rssURL')}
<div id="content" class="container-fluid">
    {if !$alertError}
        <a href="rss.php?f=1&token={$smarty.session.jtl_token}"><span class="btn btn-primary" style="margin-bottom: 15px;">{__('xmlCreate')}</span></a>
    {/if}
    {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='rss.php' buttonCaption=__('save') title=__('settings') tab='einstellungen'}
</div>
{include file='tpl_inc/footer.tpl'}
