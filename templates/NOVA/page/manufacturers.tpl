{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-manufacturers'}
    {container}
        {opcMountPoint id='opc_before_manufacturers'}
        {block name='page-manufacturers-content'}
            <div class="card-columns manufacturers-columns">
                {foreach $oHersteller_arr as $mft}
                    {link href=$mft->cURL title=$mft->cMetaTitle}
                        {card class="text-center p-2 mb-5 border-0" img-src=$mft->getImage(\JTL\Media\Image::SIZE_MD) img-alt=$mft->getName()}
                            {$mft->getName()}
                        {/card}
                    {/link}
                {/foreach}
            </div>
        {/block}
    {/container}
{/block}
