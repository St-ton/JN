<?php declare(strict_types=1);
/**
 * This file is only intended to deliver HTML,
 * read from a Markdown-file,
 * via the jquery-function .load().
 *
 * Parameters are:
 * ('jtl_token': '', 'path': '')
 */

use JTL\Helpers\Form;
use JTL\Helpers\Text;

require_once __DIR__ . '/includes/admininclude.php';
/** @global \JTL\Backend\AdminAccount $oAccount */
