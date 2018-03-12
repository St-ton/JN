<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class JTLSmartyTemplateClass
 */
class JTLSmartyTemplateClass extends Smarty_Internal_Template
{
    /**
     * @var JTLSmarty
     */
    public $smarty;

    /**
     * Runtime function to render sub-template
     *
     * @param string  $template       template name
     * @param mixed   $cache_id       cache id
     * @param mixed   $compile_id     compile id
     * @param integer $caching        cache mode
     * @param integer $cache_lifetime life time of cache data
     * @param array   $data           passed parameter template variables
     * @param int     $scope          scope in which {include} should execute
     * @param bool    $forceTplCache  cache template object
     * @param string  $uid            file dependency uid
     * @param string  $content_func   function name
     *
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
        return parent::_subTemplateRender(
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
     * @param bool $no_output_filter
     * @param null|int $display
     * @return string
     */
    public function render($no_output_filter = true, $display = null)
    {
        if ($no_output_filter === false && $display !== 1) {
            $no_output_filter = true;
        }

        return parent::render($no_output_filter, $display);
    }
}
