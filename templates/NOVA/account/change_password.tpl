{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='changePassword' section='login'}</h1>

{include file='snippets/extension.tpl'}

{block name='change-password-form'}
    {card}
        {alert variant="info"}{lang key='changePasswordDesc' section='login'}{/alert}
        {row}
            {col md=5 lg=4}
                {form id="password" action="{get_static_route id='jtl.php'}" method="post" class="evo-validate"}
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
                    {input type="hidden" name="pass_aendern" value="1"}
                    {button type="submit" value="1" class="w-auto" variant="primary"}
                        {lang key='changePassword' section='login'}
                    {/button}
                {/form}
            {/col}
        {/row}
    {/card}
{/block}
