{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
<h1>{lang key='kwkName' section='login'}</h1>
{block name='customers-recruiting'}
    {row}
        {col md=6}
            {form id="kwk" action="{get_static_route id='jtl.php'}" method="post" class="evo-validate"}
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

                {formgroup}
                    {input type="hidden" name="KwK" value="1"}
                    {input type="hidden" name="kunde_werben" value="1"}
                    {button type="submit" value="1" variant="primary" class="submit_once"}{lang key='kwkSend' section='login'}{/button}
                {/formgroup}
            {/form}
        {/col}
    {/row}
{/block}
