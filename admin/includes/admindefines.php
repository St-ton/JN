<?php declare(strict_types=1);

error_reporting(ADMIN_LOG_LEVEL);
date_default_timezone_set(SHOP_TIMEZONE);

const ADMINGROUP                          = 1;
const MAX_LOGIN_ATTEMPTS                  = 3;
const LOCK_TIME                           = 5;
const SHIPPING_CLASS_MAX_VALIDATION_COUNT = 10;
