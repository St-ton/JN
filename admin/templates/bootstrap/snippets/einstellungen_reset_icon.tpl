{if $account->oGroup->kAdminlogingruppe === 1}
    <button type="submit"
            name="resetSetting"
            value="{$cnf->cWertName}"
            class="btn btn-link p-0 {if $cnf->gesetzterWert === $cnf->defaultValue}hidden{/if}"
            title="{__('reset')}"
            data-toggle="tooltip"
            data-placement="top"
            onclick="return confirm('{__('confirmResetLog')}');"
    >
        <span class="icon-hover">
            <span class="fal fa-refresh"></span>
            <span class="fas fa-refresh"></span>
        </span>
    </button>
{/if}
