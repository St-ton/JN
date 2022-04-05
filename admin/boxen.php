<?php

use JTL\Backend\Revision;
use JTL\Boxes\Admin\BoxAdmin;
use JTL\Boxes\Type;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Link\LinkGroupInterface;
use JTL\Shop;
use function Functional\map;
use function Functional\reindex;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
