{if $idx !== 0}
    </div>
    </div>
{/if}
<div class="card">
    <div class="card-header">
        <span class="subheading1" id="{$subsection->getValueName()}">
            {$subsection->getName()}
            {if !empty($subsection->cSektionsPfad)}
                <span class="path float-right">
                    <strong>{__('settingspath')}:</strong> {$cnf->subsection}
                </span>
            {/if}
        </span>
        {*                            @TODO!*}
        {*                            {if isset($oSections[$cnf->kEinstellungenSektion])*}
        {*                                && $oSections[$cnf->kEinstellungenSektion]->hasSectionMarkup}*}
        {*                                    {$oSections[$cnf->kEinstellungenSektion]->getSectionMarkup()}*}
        {*                            {/if}*}
        <hr class="mb-n3">
    </div>
    <div class="card-body">
