<?php

/**
 * @param int $nUnit
 * @return string
 */
function fmtUnit($nUnit)
{
    return sprintf('%0.2f', $nUnit / 100);
}
