{block name='snippets-image'}
    {block name='snippets-image-variables'}
        {$square  = $square|default:true}
        {$fluid   = $fluid|default:true}
        {$lazy    = $lazy|default:true}
        {$webp    = $webp|default:true}
        {$sizes   = $sizes|default:'auto'}
        {$class   = $class|default:''}
        {$srcSize = $srcSize|default:'md'}
        {$center  = $center|default:false}

        {if $srcSize === 'xs'}
            {$srcSize = \JTL\Media\Image::SIZE_XS}
        {elseif $srcSize === 'sm'}
            {$srcSize = \JTL\Media\Image::SIZE_SM}
        {elseif $srcSize === 'md'}
            {$srcSize = \JTL\Media\Image::SIZE_MD}
        {else}
            {$srcSize = \JTL\Media\Image::SIZE_LG}
        {/if}


        {$imageType = $item->getImageType()}

        {if $imageType === \JTL\Media\Image::TYPE_CHARACTERISTIC_VALUE}
            {$mini   = $Einstellungen.bilder.bilder_merkmalwert_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_merkmalwert_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_merkmalwert_normal_breite}
            {$gross  = $Einstellungen.bilder.bilder_merkmalwert_gross_breite}
            {$alt    = $alt|default:$item->getValue()}
        {elseif $imageType === \JTL\Media\Image::TYPE_CHARACTERISTIC}
            {$mini   = $Einstellungen.bilder.bilder_merkmal_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_merkmal_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_merkmal_normal_breite}
            {$gross  = $Einstellungen.bilder.bilder_merkmal_gross_breite}
            {$alt    = $alt|default:$item->getName()}
        {elseif $imageType === \JTL\Media\Image::TYPE_PRODUCT}
            {$mini   = $Einstellungen.bilder.bilder_artikel_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_artikel_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_artikel_normal_breite}
            {$gross  = $Einstellungen.bilder.bilder_artikel_gross_breite}
        {elseif $imageType === \JTL\Media\Image::TYPE_VARIATION}
            {$mini   = $Einstellungen.bilder.bilder_variationen_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_variationen_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_variationen_breite}
            {$gross  = $Einstellungen.bilder.bilder_variationen_gross_breite}
            {$alt    = $alt|default:$item->cName}
        {elseif $imageType === \JTL\Media\Image::TYPE_NEWS}
            {$mini   = $Einstellungen.bilder.bilder_news_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_news_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_news_normal_breite}
            {$gross  = $Einstellungen.bilder.bilder_news_gross_breite}
        {elseif $imageType === \JTL\Media\Image::TYPE_NEWSCATEGORY}
            {$mini   = $Einstellungen.bilder.bilder_newskategorie_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_newskategorie_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_newskategorie_normal_breite}
            {$gross  = $Einstellungen.bilder.bilder_newskategorie_gross_breite}
            {$alt    = $alt|default:$item->getName()}
        {elseif $imageType === \JTL\Media\Image::TYPE_CONFIGGROUP}
            {$mini   = $Einstellungen.bilder.bilder_konfiggruppe_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_konfiggruppe_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_konfiggruppe_normal_breite}
            {$gross  = $Einstellungen.bilder.bilder_konfiggruppe_gross_breite}
            {$alt    = $alt|default:$item->getSprache()->getName()}
        {elseif $imageType === \JTL\Media\Image::TYPE_MANUFACTURER}
            {$mini   = $Einstellungen.bilder.bilder_hersteller_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_hersteller_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_hersteller_normal_breite}
            {$gross  = $Einstellungen.bilder.bilder_hersteller_gross_breite}
            {$alt    = $alt|default:$item->getName()}
        {else}
            {$mini   = $Einstellungen.bilder.bilder_kategorien_mini_breite}
            {$klein  = $Einstellungen.bilder.bilder_kategorien_klein_breite}
            {$normal = $Einstellungen.bilder.bilder_kategorien_breite}
            {$gross  = $Einstellungen.bilder.bilder_kategorien_gross_breite}
            {$alt    = $alt|default:$item->getName()}
        {/if}
        {$alt = $alt|default:''}
    {/block}

    {block name='snippets-image-main'}
        {if $square}
        <div class="square square-image">
            <div class="inner">
        {/if}
            {if $item->getImage(\JTL\Media\Image::SIZE_XS)|default:null !== null}
                {block name='snippets-image-main-image'}
                    {image fluid=$fluid lazy=$lazy webp=$webp center=$center
                        src=$item->getImage($srcSize)
                        srcset="{$item->getImage(\JTL\Media\Image::SIZE_XS)} {$mini}w,
                                {$item->getImage(\JTL\Media\Image::SIZE_SM)} {$klein}w,
                                {$item->getImage(\JTL\Media\Image::SIZE_MD)} {$normal}w,
                                {$item->getImage(\JTL\Media\Image::SIZE_LG)} {$gross}w"
                        alt=$alt|escape:'quotes'|escape:'html'
                        sizes=$sizes
                        class=$class
                    }
                {/block}
            {/if}
        {if $square}
            </div>
        </div>
        {/if}
    {/block}
{/block}
