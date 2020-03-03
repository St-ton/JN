{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='account-customers-recruiting'}
    {block name='account-customers-recruiting-heading'}
        <h1>{lang key='kwkName' section='login'}</h1>
    {/block}
    {block name='account-customers-recruiting-form'}
        {row}
            {col md=6}
                {form id="kwk" action="{get_static_route id='jtl.php'}" method="post" class="jtl-validate" slide=true}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'text', 'kwkFirstName', 'cVorname', null,
                            {lang key='kwkFirstName' section='login'}, true
                        ]
                    }
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'text', 'kwkLastName', 'cNachname', null,
                            {lang key='kwkLastName' section='login'}, true
                        ]
                    }
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                            'email', 'kwkEmail', 'cEmail', null,
                            {lang key='kwkEmail' section='login'}, true
                        ]
                    }
                    {block name='account-customers-recruiting-form-submit'}
                    {formgroup}
                        {input type="hidden" name="KwK" value="1"}
                        {input type="hidden" name="kunde_werben" value="1"}
                        {button type="submit" value="1" variant="primary" class="submit_once"}{lang key='kwkSend' section='login'}{/button}
                    {/formgroup}
                    {/block}
                {/form}
            {/col}
        {/row}
    {/block}
{/block}
