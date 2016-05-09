{if isset($oBox->anzeigen) && $oBox->anzeigen === 'Y' && $oBox->cLogoURL|strlen > 0}
    <section class="panel panel-default box box-trustedshops-seal" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="safety" section="global"}</h5>
        </div>
        <div class="panel-body">
            <a href="{$oBox->cLogoURL}"><img src="{$oBox->cBild}" alt="{lang key="ts_signtitle" section="global"}" /></a>
            <br />
            <small class="description">
                <a title="{lang key='ts_info_classic_title' section='global'} {$cShopName}" href="{$oBox->cLogoSiegelBoxURL}">{$cShopName} {lang key="ts_classic_text" section="global"}</a>
            </small>
        </div>
    </section>
{elseif isset($Boxen.TrustedShopsSiegelbox) && $Boxen.TrustedShopsSiegelbox->anzeigen === 'Y' && $Boxen.TrustedShopsSiegelbox->cLogoURL|strlen > 0}
    <section class="panel panel-default box box-trustedshops-seal" id="sidebox{$oBox->kBox}">
        <div class="panel-heading">
            <h5 class="panel-title">{lang key="safety" section="global"}</h5>
        </div>
        <div class="panel-body">
            <a href="{$Boxen.TrustedShopsSiegelbox->cLogoURL}"><img src="{$Boxen.TrustedShopsSiegelbox->cBild}" alt="{lang key="ts_signtitle" section="global"}" /></a>
            <br />
            <small class="description">
                <a title="{lang key='ts_info_classic_title' section='global'} {$cShopName}" href="{$Boxen.TrustedShopsSiegelbox->cLogoSiegelBoxURL}">{$cShopName} {lang key="ts_classic_text" section="global"}</a>
            </small>
        </div>
    </section>
{/if}