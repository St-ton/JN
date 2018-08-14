{if $oBox->show()}
    <section class="panel panel-default box box-trustedshops-seal" id="sidebox{$oBox->getID()}">
        {if $oBox->getPosition() !== \Boxes\Position::BOTTOM}
            <div class="panel-heading">
                <div class="panel-title">{lang key='safety'}</div>
            </div>
        {/if}
        <div class="box-body panel-body text-center">
            <p><a href="{$oBox->getLogoURL()}"><img src="{$oBox->getImageURL()}" alt="{lang key='ts_signtitle'}" /></a></p>
            <small class="description">
                <a title="{lang key='ts_info_classic_title'} {$cShopName}" href="{$oBox->getLogoSealURL()}">{$cShopName} {lang key='ts_classic_text'}</a>
            </small>
        </div>
    </section>
{/if}
