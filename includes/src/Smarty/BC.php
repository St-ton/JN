<?php declare(strict_types=1);

namespace JTL\Smarty;

use Smarty;
use SmartyException;

/**
 * Class BC
 * @package \JTL\Smarty
 */
class BC extends Smarty
{
    /**
     * @param string $tpl_var the template variable name
     * @param mixed  &$value  the referenced value to assign
     */
    public function assign_by_ref($tpl_var, &$value): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use assignByRef() instead.', \E_USER_DEPRECATED);
        $this->assignByRef($tpl_var, $value);
    }

    /**
     * @param string $tpl_var the template variable name
     * @param mixed  &$value  the referenced value to append
     * @param bool   $merge   flag if array elements shall be merged
     */
    public function append_by_ref($tpl_var, &$value, $merge = false): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use appendByRef() instead.', \E_USER_DEPRECATED);
        $this->appendByRef($tpl_var, $value, $merge);
    }

    /**
     * @param string $tpl_var the template variable to clear
     */
    public function clear_assign($tpl_var): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use clearAssign() instead.', \E_USER_DEPRECATED);
        $this->clearAssign($tpl_var);
    }

    /**
     * @param string $function      the name of the template function
     * @param string $function_impl the name of the PHP function to register
     * @param bool   $cacheable
     * @param mixed  $cache_attrs
     * @throws SmartyException
     */
    public function register_function($function, $function_impl, $cacheable = true, $cache_attrs = null): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerPlugin() instead.', \E_USER_DEPRECATED);
        $this->registerPlugin('function', $function, $function_impl, $cacheable, $cache_attrs);
    }

    /**
     * @param string $function name of template function
     */
    public function unregister_function($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use assignByRef() instead.', \E_USER_DEPRECATED);
        $this->unregisterPlugin('function', $function);
    }

    /**
     * @param string $object name of template object
     * @param object $object_impl the referenced PHP object to register
     * @param array  $allowed list of allowed methods (empty = all)
     * @param bool   $smarty_args smarty argument format, else traditional
     * @param array  $block_methods list of methods that are block format
     * @throws   SmartyException
     * @internal param array $block_functs list of methods that are block format
     */
    public function register_object(
        $object,
        $object_impl,
        $allowed = [],
        $smarty_args = true,
        $block_methods = []
    ): void {
        \trigger_error(__METHOD__ . ' is deprecated. Use assignByRef() instead.', \E_USER_DEPRECATED);
        $this->registerObject($object, $object_impl, (array)$allowed, (bool)$smarty_args, $block_methods);
    }

    /**
     * @param string $object name of template object
     */
    public function unregister_object($object): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterObject() instead.', \E_USER_DEPRECATED);
        $this->unregisterObject($object);
    }

    /**
     * @param string $block      name of template block
     * @param string $block_impl PHP function to register
     * @param bool   $cacheable
     * @param mixed  $cache_attrs
     * @throws SmartyException
     */
    public function register_block($block, $block_impl, $cacheable = true, $cache_attrs = null): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerPlugin() instead.', \E_USER_DEPRECATED);
        $this->registerPlugin('block', $block, $block_impl, $cacheable, $cache_attrs);
    }

    /**
     * @param string $block name of template function
     */
    public function unregister_block($block): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterPlugin() instead.', \E_USER_DEPRECATED);
        $this->unregisterPlugin('block', $block);
    }

    /**
     * @param string $function      name of template function
     * @param string $function_impl name of PHP function to register
     * @param bool   $cacheable
     * @throws SmartyException
     */
    public function register_compiler_function($function, $function_impl, $cacheable = true): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerPlugin() instead.', \E_USER_DEPRECATED);
        $this->registerPlugin('compiler', $function, $function_impl, $cacheable);
    }

    /**
     * @param string $function name of template function
     */
    public function unregister_compiler_function($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterPlugin() instead.', \E_USER_DEPRECATED);
        $this->unregisterPlugin('compiler', $function);
    }

    /**
     * @param string $modifier      name of template modifier
     * @param string $modifier_impl name of PHP function to register
     *
     * @throws SmartyException
     */
    public function register_modifier($modifier, $modifier_impl): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerPlugin() instead.', \E_USER_DEPRECATED);
        $this->registerPlugin('modifier', $modifier, $modifier_impl);
    }

    /**
     * @param string $modifier name of template modifier
     */
    public function unregister_modifier($modifier): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterPlugin() instead.', \E_USER_DEPRECATED);
        $this->unregisterPlugin('modifier', $modifier);
    }

    /**
     * @param string $type      name of resource
     * @param array  $functions array of functions to handle resource
     */
    public function register_resource($type, $functions): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerResource() instead.', \E_USER_DEPRECATED);
        $this->registerResource($type, $functions);
    }

    /**
     * @param string $type name of resource
     */
    public function unregister_resource($type): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterResource() instead.', \E_USER_DEPRECATED);
        $this->unregisterResource($type);
    }

    /**
     * @param callable $function
     * @throws SmartyException
     */
    public function register_prefilter($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerFilter() instead.', \E_USER_DEPRECATED);
        $this->registerFilter('pre', $function);
    }

    /**
     * @param callable $function
     */
    public function unregister_prefilter($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterFilter() instead.', \E_USER_DEPRECATED);
        $this->unregisterFilter('pre', $function);
    }

    /**
     * @param callable $function
     * @throws SmartyException
     */
    public function register_postfilter($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerFilter() instead.', \E_USER_DEPRECATED);
        $this->registerFilter('post', $function);
    }

    /**
     * @param callable $function
     */
    public function unregister_postfilter($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterFilter() instead.', \E_USER_DEPRECATED);
        $this->unregisterFilter('post', $function);
    }

    /**
     * @param callable $function
     * @throws SmartyException
     */
    public function register_outputfilter($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use registerFilter() instead.', \E_USER_DEPRECATED);
        $this->registerFilter('output', $function);
    }

    /**
     * @param callable $function
     */
    public function unregister_outputfilter($function): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use unregisterFilter() instead.', \E_USER_DEPRECATED);
        $this->unregisterFilter('output', $function);
    }

    /**
     * @param string $type filter type
     * @param string $name filter name
     * @throws SmartyException
     */
    public function load_filter($type, $name): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use loadFilter() instead.', \E_USER_DEPRECATED);
        $this->loadFilter($type, $name);
    }

    /**
     * @param string $tpl_file   name of template file
     * @param string $cache_id   name of cache_id
     * @param string $compile_id name of compile_id
     * @param string $exp_time   expiration time
     * @return int
     */
    public function clear_cache($tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null)
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use clearCache() instead.', \E_USER_DEPRECATED);
        return $this->clearCache($tpl_file, $cache_id, $compile_id, $exp_time);
    }

    /**
     * @param string $exp_time expire time
     * @return int
     */
    public function clear_all_cache($exp_time = null)
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use clearCache() instead.', \E_USER_DEPRECATED);
        return $this->clearCache(null, null, null, $exp_time);
    }

    /**
     * @param string $tpl_file name of template file
     * @param string $cache_id
     * @param string $compile_id
     *
     * @return bool
     * @throws \Exception
     * @throws SmartyException
     */
    public function is_cached($tpl_file, $cache_id = null, $compile_id = null): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use isCached() instead.', \E_USER_DEPRECATED);
        return $this->isCached($tpl_file, $cache_id, $compile_id);
    }

    /**
     *
     */
    public function clear_all_assign(): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use clearAllAssign() instead.', \E_USER_DEPRECATED);
        $this->clearAllAssign();
    }

    /**
     * @param string $tpl_file
     * @param string $compile_id
     * @param string $exp_time
     * @return int
     */
    public function clear_compiled_tpl($tpl_file = null, $compile_id = null, $exp_time = null)
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use clearCompiledTemplate() instead.', \E_USER_DEPRECATED);
        return $this->clearCompiledTemplate($tpl_file, $compile_id, $exp_time);
    }

    /**
     * @param string $tpl_file
     * @return bool
     * @throws SmartyException
     */
    public function template_exists($tpl_file): bool
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use templateExists() instead.', \E_USER_DEPRECATED);
        return $this->templateExists($tpl_file);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get_template_vars($name = null): mixed
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use getTemplateVars() instead.', \E_USER_DEPRECATED);
        return $this->getTemplateVars($name);
    }

    /**
     * @param string $name
     * @return array
     */
    public function get_config_vars($name = null): array
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use getConfigVars() instead.', \E_USER_DEPRECATED);
        return $this->getConfigVars($name);
    }

    /**
     * @param string $file
     * @param string $section
     * @param string $scope
     */
    public function config_load($file, $section = null, $scope = 'global'): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use ConfigLoad() instead.', \E_USER_DEPRECATED);
        $this->configLoad($file, $section, $scope);
    }

    /**
     * @param string $name
     * @return object
     */
    public function get_registered_object($name)
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use getRegisteredObject() instead.', \E_USER_DEPRECATED);
        return $this->getRegisteredObject($name);
    }

    /**
     * @param string $var
     */
    public function clear_config($var = null): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use clearConfig() instead.', \E_USER_DEPRECATED);
        $this->clearConfig($var);
    }

    /**
     * @param string $error_msg
     * @param int    $error_type
     */
    public function trigger_error($error_msg, $error_type = \E_USER_WARNING): void
    {
        \trigger_error(__METHOD__ . ' is deprecated. Use \trigger_error() instead.', \E_USER_DEPRECATED);
        \trigger_error("Smarty error: $error_msg", $error_type);
    }
}
