{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl'
        cTitel=__('configurePriceFlow')
        cBeschreibung=__('configurePriceFlowDesc')
        cDokuURL=__('configurePriceFlowURL')}
<div id="content">
    <div class="card">
        <div class="card-body">
            {include file='tpl_inc/config_section.tpl'
                    name='einstellen'
                    a='saveSettings'
                    action=$adminURL|cat:$route
                    title=__('settings')
                    skipHeading=true
                    tab='einstellungen'}
        </div>
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
