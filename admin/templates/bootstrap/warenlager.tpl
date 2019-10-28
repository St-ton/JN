{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='warenlager'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('warenlager') cBeschreibung=__('warenlagerDesc') cDokuURL=__('warenlagerURL')}
{if $step === 'uebersicht'}
    {include file='tpl_inc/warenlager_uebersicht.tpl'}
{/if}
{include file='tpl_inc/footer.tpl'}
