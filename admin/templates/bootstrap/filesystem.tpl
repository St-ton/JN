{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=__('fs') cBeschreibung=__('fsDesc') cDokuURL=__('fsURL')}

<div id="content">
    <div id="settings">
        {capture name=testButton}
            <div class="ml-auto col-sm-6 col-xl-auto">
                <button name="test" type="submit" value="1" class="btn btn-default btn-block">
                    <i class="fal fa-play-circle"></i> {__('methodTest')}
                </button>
            </div>
        {/capture}
        {include file='tpl_inc/config_section.tpl'
            name='einstellen'
            a='saveSettings'
            action=$adminURL|cat:$route
            title=__('settings')
            additionalButtons=$smarty.capture.testButton
            tab='einstellungen'
        }
    </div>
</div>

{include file='tpl_inc/footer.tpl'}
