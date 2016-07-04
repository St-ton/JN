<?php

/**
 * @link http://www.phpinsider.com/php/code/SmartyPaginate/
 * @copyright 2001-2005 New Digital Group, Inc.
 */
class Paginate
{
    /**
     * initialize the session data
     *
     * @param string $id the pagination id
     * @param string $formvar the variable containing submitted pagination information
     */
    public function connect($id = 'default', $formvar = null)
    {
        if (!isset($_SESSION['Paginate'][$id])) {
            $this->reset($id);
        }

        // use $_GET by default unless otherwise specified
        $_formvar = isset($formvar) ? $formvar : $_GET;

        // set the current page
        $_total = $this->getTotal($id);
        if (isset($_formvar[$this->getUrlVar($id)]) && $_formvar[$this->getUrlVar($id)] > 0 && (!isset($_total) || $_formvar[$this->getUrlVar($id)] <= $_total)) {
            $_SESSION['Paginate'][$id]['current_item'] = $_formvar[$_SESSION['Paginate'][$id]['urlvar']];
        }
    }

    /**
     * see if session has been initialized
     *
     * @param string $id the pagination id
     */
    public function isConnected($id = 'default')
    {
        return isset($_SESSION['Paginate'][$id]);
    }

    /**
     * reset/init the session data
     *
     * @param string $id the pagination id
     */
    public function reset($id = 'default')
    {
        $_SESSION['Paginate'][$id] = array(
            'item_limit'   => 10,
            'item_total'   => null,
            'current_item' => 1,
            'urlvar'       => 'page',
            'url'          => $_SERVER['PHP_SELF'],
            'prev_text'    => '&laquo;',
            'next_text'    => '&raquo;',
            'first_text'   => 'first',
            'last_text'    => 'last'
            );
    }

    /**
     * clear the Paginate session data
     *
     * @param string $id the pagination id
     */
    public function disconnect($id = null)
    {
        if (isset($id)) {
            unset($_SESSION['Paginate'][$id]);
        } else {
            unset($_SESSION['Paginate']);
        }
    }

    /**
     * set maximum number of items per page
     *
     * @param string $id the pagination id
     */
    public function setLimit($limit, $id = 'default')
    {
        if (!preg_match('!^\d+$!', $limit)) {
            trigger_error('Paginate setLimit: limit must be an integer.');

            return false;
        }
        settype($limit, 'integer');
        if ($limit < 1) {
            trigger_error('Paginate setLimit: limit must be greater than zero.');

            return false;
        }
        $_SESSION['Paginate'][$id]['item_limit'] = $limit;
    }

    /**
     * get maximum number of items per page
     *
     * @param string $id the pagination id
     */
    public function getLimit($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['item_limit'];
    }

    /**
     * set the total number of items
     *
     * @param int $total the total number of items
     * @param string $id the pagination id
     */
    public function setTotal($total, $id = 'default')
    {
        if (!preg_match('!^\d+$!', $total)) {
            trigger_error('Paginate setTotal: total must be an integer.');

            return false;
        }
        settype($total, 'integer');
        if ($total < 0) {
            trigger_error('Paginate setTotal: total must be positive.');

            return false;
        }
        $_SESSION['Paginate'][$id]['item_total'] = $total;
    }

    /**
     * get the total number of items
     *
     * @param string $id the pagination id
     */
    public function getTotal($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['item_total'];
    }

    /**
     * set the url used in the links, default is $PHP_SELF
     *
     * @param string $url the pagination url
     * @param string $id the pagination id
     */
    public function setUrl($url, $id = 'default')
    {
        $_SESSION['Paginate'][$id]['url'] = $url;
    }

    /**
     * get the url variable
     *
     * @param string $id the pagination id
     */
    public function getUrl($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['url'];
    }

    /**
     * set the url variable ie. ?next=10
     *                           ^^^^
     * @param string $url url pagination varname
     * @param string $id the pagination id
     */
    public function setUrlVar($urlvar, $id = 'default')
    {
        $_SESSION['Paginate'][$id]['urlvar'] = $urlvar;
    }

    /**
     * get the url variable
     *
     * @param string $id the pagination id
     */
    public function getUrlVar($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['urlvar'];
    }

    /**
     * set the current item (usually done automatically by next/prev links)
     *
     * @param int $item index of the current item
     * @param string $id the pagination id
     */
    public function setCurrentItem($item, $id = 'default')
    {
        $_SESSION['Paginate'][$id]['current_item'] = $item;
    }

    /**
     * get the current item
     *
     * @param string $id the pagination id
     */
    public function getCurrentItem($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['current_item'];
    }

    /**
     * get the current item index
     *
     * @param string $id the pagination id
     */
    public function getCurrentIndex($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['current_item'] - 1;
    }

    /**
     * get the last displayed item
     *
     * @param string $id the pagination id
     */
    public function getLastItem($id = 'default')
    {
        $_total = $this->getTotal($id);
        $_limit = $this->getLimit($id);
        $_last  = $this->getCurrentItem($id) + $_limit - 1;

        return ($_last <= $_total) ? $_last : $_total;
    }

    /**
     * assign $paginate var values
     *
     * @param obj &$smarty the smarty object reference
     * @param string $var the name of the assigned var
     * @param string $id the pagination id
     */
    public function assign(&$smarty, $var = 'paginate', $id = 'default')
    {
        if (is_object($smarty) && (strtolower(get_class($smarty)) == 'smarty' || is_subclass_of($smarty, 'smarty'))) {
            $smarty->assign($var, $this->getData());
        } else {
            trigger_error("Paginate: [assign] I need a valid Smarty object.");
        }
    }

    public function getData($id = 'default')
    {
        $_paginate['total']        = $this->getTotal($id);
        $_paginate['first']        = $this->getCurrentItem($id);
        $_paginate['last']         = $this->getLastItem($id);
        $_paginate['page_current'] = ceil($this->getLastItem($id) / $this->getLimit($id));
        $_paginate['page_total']   = ceil($this->getTotal($id) / $this->getLimit($id));
        $_paginate['size']         = $_paginate['last'] - $_paginate['first'];
        $_paginate['url']          = $this->getUrl($id);
        $_paginate['urlvar']       = $this->getUrlVar($id);
        $_paginate['current_item'] = $this->getCurrentItem($id);
        $_paginate['prev_text']    = $this->getPrevText($id);
        $_paginate['next_text']    = $this->getNextText($id);
        $_paginate['limit']        = $this->getLimit($id);

        $_item = 1;
        $_page = 1;
        while ($_item <= $_paginate['total']) {
            $_paginate['page'][$_page]['number']     = $_page;
            $_paginate['page'][$_page]['item_start'] = $_item;
            $_paginate['page'][$_page]['item_end']   = ($_item + $_paginate['limit'] - 1 <= $_paginate['total']) ? $_item + $_paginate['limit'] - 1 : $_paginate['total'];
            $_paginate['page'][$_page]['is_current'] = ($_item == $_paginate['current_item']);
            $_item += $_paginate['limit'];
            $_page++;
        }

        return $_paginate;
    }

    /**
     * set the default text for the "previous" page link
     *
     * @param string $text index of the current item
     * @param string $id the pagination id
     */
    public function setPrevText($text, $id = 'default')
    {
        $_SESSION['Paginate'][$id]['prev_text'] = $text;
    }

    /**
     * get the default text for the "previous" page link
     *
     * @param string $id the pagination id
     */
    public function getPrevText($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['prev_text'];
    }

    /**
     * set the text for the "next" page link
     *
     * @param string $text index of the current item
     * @param string $id the pagination id
     */
    public function setNextText($text, $id = 'default')
    {
        $_SESSION['Paginate'][$id]['next_text'] = $text;
    }

    /**
     * get the default text for the "next" page link
     *
     * @param string $id the pagination id
     */
    public function getNextText($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['next_text'];
    }

    /**
     * set the text for the "first" page link
     *
     * @param string $text index of the current item
     * @param string $id the pagination id
     */
    public function setFirstText($text, $id = 'default')
    {
        $_SESSION['Paginate'][$id]['first_text'] = $text;
    }

    /**
     * get the default text for the "first" page link
     *
     * @param string $id the pagination id
     */
    public function getFirstText($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['first_text'];
    }

    /**
     * set the text for the "last" page link
     *
     * @param string $text index of the current item
     * @param string $id the pagination id
     */
    public function setLastText($text, $id = 'default')
    {
        $_SESSION['Paginate'][$id]['last_text'] = $text;
    }

    /**
     * get the default text for the "last" page link
     *
     * @param string $id the pagination id
     */
    public function getLastText($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['last_text'];
    }

    /**
     * set default number of page groupings in {paginate_middle}
     *
     * @param string $id the pagination id
     */
    public function setPageLimit($limit, $id = 'default')
    {
        if (!preg_match('!^\d+$!', $limit)) {
            trigger_error('Paginate setPageLimit: limit must be an integer.');

            return false;
        }
        settype($limit, 'integer');
        if ($limit < 1) {
            trigger_error('Paginate setPageLimit: limit must be greater than zero.');

            return false;
        }
        $_SESSION['Paginate'][$id]['page_limit'] = $limit;
    }

    /**
     * get default number of page groupings in {paginate_middle}
     *
     * @param string $id the pagination id
     */
    public function getPageLimit($id = 'default')
    {
        return $_SESSION['Paginate'][$id]['page_limit'];
    }

    /**
     * get the previous page of items
     *
     * @param string $id the pagination id
     */
    public function _getPrevPageItem($id = 'default')
    {
        $_prev_item = $_SESSION['Paginate'][$id]['current_item'] - $_SESSION['Paginate'][$id]['item_limit'];

        return ($_prev_item > 0) ? $_prev_item : false;
    }

    /**
     * get the previous page of items
     *
     * @param string $id the pagination id
     */
    public function _getNextPageItem($id = 'default')
    {
        $_next_item = $_SESSION['Paginate'][$id]['current_item'] + $_SESSION['Paginate'][$id]['item_limit'];

        return ($_next_item <= $_SESSION['Paginate'][$id]['item_total']) ? $_next_item : false;
    }
}
