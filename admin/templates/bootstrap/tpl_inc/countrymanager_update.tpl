<div id="content">
    <form id="country-update-form" method="post">
        {$jtl_token}
        <input type="hidden" name="action" value="{$step}" />
        <input type="hidden" name="save" value="1" />
        <div class="card">
            <div class="card-header">
                <div class="subheading1">{if $step === 'update'}{__('updateCountry')}{else}{__('addCountry')}{/if}</div>
                <hr class="mb-n3">
            </div>
            <div class="card-body">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cISO">{__('ISO')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" id="cISO" name="cISO" value="{if !empty($country)}{$country->getISO()}{/if}" tabindex="1" required {if !empty($country)}readonly{/if}/>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cDeutsch">{__('DBcDeutsch')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" id="cDeutsch" name="cDeutsch" value="{if !empty($country)}{$country->getNameDE()}{/if}" tabindex="1" required/>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cEnglisch">{__('DBcEnglisch')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" type="text" id="cEnglisch" name="cEnglisch" value="{if !empty($country)}{$country->getNameEN()}{/if}" tabindex="1" required/>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="nEU">{__('isEU')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="nEU" id="nEU" class="custom-select">
                                <option value="0" {if !empty($country) && !$country->isEU()}selected{/if}>{__('no')}</option>
                                <option value="1" {if !empty($country) && $country->isEU()}selected{/if}>{__('yes')}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="cKontinent">{__('Continent')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="cKontinent" id="cKontinent" class="custom-select">
                            {foreach $continents as $continent}
                                <option value="{$continent}" {if !empty($country) && $country->getContinent() === $continent}selected{/if}>
                                    {__($continent)}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="bPermitRegistration">{__('isPermitRegistration')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="bPermitRegistration" id="bPermitRegistration" class="custom-select">
                            <option value="0" {if !empty($country) && !$country->isPermitRegistration()}selected{/if}>{__('no')}</option>
                            <option value="1" {if !empty($country) && $country->isPermitRegistration()}selected{/if}>{__('yes')}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="bRequireStateDefinition">{__('isRequireStateDefinition')}:</label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <select name="bRequireStateDefinition" id="bRequireStateDefinition" class="custom-select">
                            <option value="0" {if !empty($country) && !$country->isRequireStateDefinition()}selected{/if}>{__('no')}</option>
                            <option value="1" {if !empty($country) && $country->isRequireStateDefinition()}selected{/if}>{__('yes')}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                        <a class="btn btn-outline-primary btn-block" href="countrymanager.php">
                            {__('cancelWithIcon')}
                        </a>
                    </div>
                    <div class="col-sm-6 col-lg-auto ">
                        <button type="submit" class="btn btn-primary btn-block">
                            {__('saveWithIcon')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>