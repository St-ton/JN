{block name='page-manufacturers'}
    {opcMountPoint id='opc_before_manufacturers' inContainer=false}
    {block name='page-manufacturers-content'}
        {container}
            <div class="card-columns manufacturers-columns">
                {foreach $oHersteller_arr as $mft}
                    {link href=$mft->cURL title=$mft->cMetaTitle}
                        {card class="text-center p-2 mb-5 border-0" img-src=$mft->getImage(\JTL\Media\Image::SIZE_MD) img-alt=$mft->getName()}
                            {$mft->getName()}
                        {/card}
                    {/link}
                {/foreach}
            </div>
        {/container}
    {/block}
{/block}
