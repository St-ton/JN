{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-change-password'}
    {block name='account-change-password-heading'}
        <h1>{lang key='changePassword' section='login'}</h1>
    {/block}
    {block name='account-change-password-change-password-form'}
        {block name='account-change-password-alert'}
            {alert variant="info"}{lang key='changePasswordDesc' section='login'}{/alert}
        {/block}
        {row}
            {col md=5 lg=4}
                {block name='account-change-password-form-password'}
                    {form id="password" action="{get_static_route id='jtl.php'}" method="post" class="evo-validate label-slide"}
                        {block name='account-change-password-form-password-content'}
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'password', 'currentPassword', 'altesPasswort', null,
                                    {lang key='currentPassword' section='login'}, true, null, "current-password"
                                ]
                            }
                            <div class="form-group d-flex flex-column" role="group">
                                {input type="password"
                                       class="form-control"
                                       placeholder=" "
                                       id="newPassword"
                                       value=""
                                       required=true
                                       name="neuesPasswort1"
                                       autocomplete="new-password"}
                                <label for="newPassword" class="col-form-label pt-0">
                                    {lang key='newPassword' section='login'}
                                </label>
                            </div>
                            {block name='account-change-password-include-password-check'}
                                {include file='snippets/password_check.tpl' id='#newPassword'}
                            {/block}
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'password', 'newPasswordRpt', 'neuesPasswort2', null,
                                    {lang key='newPasswordRpt' section='login'}, true, null, "new-password"
                                ]
                            }
                            {block name='account-change-password-form-submit'}
                                {row}
                                    {col cols=12 class='col-md'}
                                        {link class="btn btn-outline-primary btn-block mb-3" href="{get_static_route id='jtl.php'}"}
                                            {lang key='back'}
                                        {/link}
                                    {/col}
                                    {col class='ml-auto col-md-auto'}
                                        {input type="hidden" name="pass_aendern" value="1"}
                                        {button type="submit" value="1" block=true variant="primary"}
                                            {lang key='changePassword' section='login'}
                                        {/button}
                                    {/col}
                                {/row}
                            {/block}
                        {/block}
                    {/form}
                {/block}
            {/col}
        {/row}
    {/block}
{/block}
