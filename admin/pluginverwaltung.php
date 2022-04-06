<?php

use JTL\Filesystem\Filesystem;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\Mapper\PluginState as StateMapper;
use JTL\Mapper\PluginValidation as ValidationMapper;
use JTL\Minify\MinifyService;
use JTL\Plugin\Admin\Installation\Extractor;
use JTL\Plugin\Admin\Installation\Installer;
use JTL\Plugin\Admin\Installation\Uninstaller;
use JTL\Plugin\Admin\Listing;
use JTL\Plugin\Admin\ListingItem;
use JTL\Plugin\Admin\StateChanger;
use JTL\Plugin\Admin\Updater;
use JTL\Plugin\Admin\Validation\LegacyPluginValidator;
use JTL\Plugin\Admin\Validation\PluginValidator;
use JTL\Plugin\Helper;
use JTL\Plugin\InstallCode;
use JTL\Plugin\LegacyPluginLoader;
use JTL\Plugin\PluginLoader;
use JTL\Plugin\State;
use JTL\Shop;
use JTL\XMLParser;
use JTLShop\SemVer\Version;
use League\Flysystem\MountManager;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use function Functional\first;
use function Functional\group;
use function Functional\select;

require_once __DIR__ . '/includes/admininclude.php';

/** @global \JTL\Smarty\JTLSmarty $smarty */
/** @global \JTL\Backend\AdminAccount $oAccount */
