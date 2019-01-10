{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='changePassword' section='login'}</h1>

{include file='snippets/extension.tpl'}

{block name='change-password-form'}
<div class="panel-wrap">
    {if empty($hinweis)}
        <p class="alert alert-info">{lang key='changePasswordDesc' section='login'}</p>
    {/if}
    {if !empty($cFehler)}
        <p class="alert alert-danger">{$cFehler}</p>
    {/if}
    <div class="row">
        <form id="password" action="{get_static_route id='jtl.php'}" method="post" class="col-xs-8 col-md-5 col-lg-4 evo-validate">
            {$jtl_token}
            {include file='snippets/form_group_simple.tpl'
                options=[
                    'password', 'currentPassword', 'altesPasswort', null,
                    {lang key='currentPassword' section='login'}, true
                ]
            }

            {include file='snippets/form_group_simple.tpl'
                options=[
                    'password', 'newPassword', 'neuesPasswort1', null,
                    {lang key='newPassword' section='login'}, true
                ]
            }

            {include file='snippets/form_group_simple.tpl'
                options=[
                    'password', 'newPasswordRpt', 'neuesPasswort2', null,
                    {lang key='newPasswordRpt' section='login'}, true
                ]
            }

            <div class="form-group">
                <input type="hidden" name="pass_aendern" value="1">
                <input type="submit" value="{lang key='changePassword' section='login'}" class="submit btn btn-primary btn-block">
            </div>
        </form>
    </div>
</div>
{/block}
