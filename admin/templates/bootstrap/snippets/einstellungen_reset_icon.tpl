{if $account->oGroup->kAdminlogingruppe === 1}
    <button type="button"
            name="resetSetting"
            value="{$cnf->cWertName}"
            class="btn btn-link p-0 {if $cnf->gesetzterWert === $cnf->defaultValue}hidden{/if} delete-confirm btn-submit"
            title="{__('settingReset')}"
            data-toggle="tooltip"
            data-placement="top"
            data-modal-body="{__('confirmResetLog')|sprintf:__("{$cnf->cWertName}_name")}"
            data-modal-title="{__('confirmResetLogTitle')}"
            data-modal-submit="{__('reset')}"
    >
        <span class="icon-hover">
            <span class="fal fa-refresh"></span>
            <span class="fas fa-refresh"></span>
        </span>
    </button>
{/if}
