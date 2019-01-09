{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='preisverlauf'}
{include file='tpl_inc/seite_header.tpl'
        cTitel=__('configurePriceFlow')
        cBeschreibung=__('configurePriceFlowDesc')
        cDokuURL=__('configurePriceFlowURL')}
<div id="content" class="container-fluid">
    {include file='tpl_inc/config_section.tpl'
            config=$oConfig_arr
            name='einstellen'
            a='saveSettings'
            action='preisverlauf.php'
            buttonCaption=__('save')
            title='Einstellungen'
            tab='einstellungen'}
</div>
{include file='tpl_inc/footer.tpl'}