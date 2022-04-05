{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('fs') cBeschreibung=__('fsDesc') cDokuURL=__('fsUrl')}

<div id="content">
    <div id="settings">
        {include file='tpl_inc/config_section.tpl'
                    name='einstellen'
                    a='saveSettings'
                    action=$shopURL|cat:$route
                    title=__('settings')
                    tab='einstellungen'}
    </div>
</div>

{include file='tpl_inc/footer.tpl'}
