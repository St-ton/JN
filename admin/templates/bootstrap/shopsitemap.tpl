{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('shopsitemap') cBeschreibung=__('shopsitemapDesc') cDokuURL=__('shopsitemapURL')}
<div id="content">
    {include file='tpl_inc/config_section.tpl'
    name='einstellen'
    action='shopsitemap.php'
    buttonCaption=__('saveWithIcon')
    title=__('settings')
    tab='einstellungen'
    }
</div>
{include file='tpl_inc/footer.tpl'}
