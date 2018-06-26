<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class DateHelper
 * @since 5.0.0
 */
class DateHelper
{
    /**
     * @param DateTime|string|int $date
     * @param int $weekdays
     * @return DateTime
     * @since 5.0.0
     */
    public static function dateAddWeekday($date, $weekdays): DateTime
    {
        try {
            if (is_string($date)) {
                $resDate = new DateTime($date);
            } elseif (is_numeric($date)) {
                $resDate = new DateTime();
                $resDate->setTimestamp($date);
            } elseif (is_object($date) && is_a($date, 'DateTime')) {
                /** @var DateTime $date */
                $resDate = new DateTime($date->format(DateTime::ATOM));
            } else {
                $resDate = new DateTime();
            }
        } catch (Exception $e) {
            Jtllog::writeLog($e->getMessage());
            $resDate = new DateTime();
        }

        if ((int)$resDate->format('w') === 0) {
            // Add one weekday if startdate is on sunday
            $resDate->add(DateInterval::createFromDateString('1 weekday'));
        }

        // Add $weekdays as normal days
        $resDate->add(DateInterval::createFromDateString($weekdays . ' day'));

        if ((int)$resDate->format('w') === 0) {
            // Add one weekday if enddate is on sunday
            $resDate->add(DateInterval::createFromDateString('1 weekday'));
        }

        return $resDate;
    }

    /**
     * YYYY-MM-DD HH:MM:SS, YYYY-MM-DD, now oder now()
     *
     * @param string $cDatum
     * @return array
     * @former gibDatumTeile()
     * @since 5.0.0
     */
    public static function getDateParts(string $cDatum): array
    {
        $date_arr = [];
        if (strlen($cDatum) > 0) {
            if ($cDatum === 'now()') {
                $cDatum = 'now';
            }
            try {
                $date                 = new DateTime($cDatum);
                $date_arr['cDatum']   = $date->format('Y-m-d');
                $date_arr['cZeit']    = $date->format('H:m:s');
                $date_arr['cJahr']    = $date->format('Y');
                $date_arr['cMonat']   = $date->format('m');
                $date_arr['cTag']     = $date->format('d');
                $date_arr['cStunde']  = $date->format('H');
                $date_arr['cMinute']  = $date->format('i');
                $date_arr['cSekunde'] = $date->format('s');
            } catch (Exception $e) {
            }
        }

        return $date_arr;
    }
}
