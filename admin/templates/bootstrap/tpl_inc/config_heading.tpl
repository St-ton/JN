<div class="config-card-wrapper">
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
        <hr class="mb-n3">
    </div>
    <div class="card-body">
