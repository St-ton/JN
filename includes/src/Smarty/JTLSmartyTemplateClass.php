<?php declare(strict_types=1);

namespace JTL\Smarty;

/**
 * Class JTLSmartyTemplateClass
 * @package JTL\Smarty
 */
class JTLSmartyTemplateClass extends \Smarty_Internal_Template
{
    /**
     * @var JTLSmarty
     */
    public $smarty;

    /**
     * @var bool
     */
    public bool $noOutputFilter = true;

    /**
     * @inheritdoc
     */
    public function _subTemplateRender(
        $template,
        $cache_id,
        $compile_id,
        $caching,
        $cache_lifetime,
        $data,
        $scope,
        $forceTplCache,
        $uid = null,
        $content_func = null
    ) {
        parent::_subTemplateRender(
            $this->smarty->getResourceName($template),
            $cache_id,
            $compile_id,
            $caching,
            $cache_lifetime,
            $data,
            $scope,
            $forceTplCache,
            $uid,
            $content_func
        );
    }

    /**
     * @inheritdoc
     */
    public function render($no_output_filter = true, $display = null)
    {
        if ($no_output_filter === false && $display !== 1) {
            $no_output_filter = $this->noOutputFilter;
        }

        return parent::render($no_output_filter, $display);
    }
}
