<?php
namespace CB;

class System
{
    /**
     * get countries list with their phone codes
     *
     * this function returns an array of records for arrayReader
     *     first column is id
     *     second is name
     *     third is phone code
     * @return json response
     */
    public function getCountries()
    {
        $rez = array();
        $res = DB\dbQuery(
            'SELECT
                id
                ,name
                ,phone_codes
            FROM casebox.country_phone_codes
            ORDER BY name'
        ) or die(DB\dbQueryError());

        while ($r = $res->fetch_assoc()) {
            $rez[] = array_values($r);
        }

        return array('success' => true, 'data' => $rez);
    }

    /**
     * get defined timezones
     *
     * returns an array of records for arrayReader
     * record contains two fields: caption, gmt offset
     * @return json response
     */
    public function getTimezones()
    {

        $timezones = [];
        $offsets = [];
        $now = new \DateTime();

        foreach (\DateTimeZone::listIdentifiers() as $timezone) {
            $now->setTimezone(new \DateTimeZone($timezone));
            $offsets[] = $offset = $now->getOffset();
            $timezones[$timezone] = array(
                $timezone
                ,$offset
                ,'(GMT'.$this->formatGmtOffset($offset).') '.$this->formatTimezoneName($timezone)
            );
        }

        array_multisort($offsets, $timezones);

        return array('success' => true, 'data' => array_values($timezones));
    }

    /**
     * check a given timezon to be valid
     * @param  varchar $timezone valid php timezone
     * @return boolean
     */
    public static function isValidTimezone ($timezone)
    {
        return in_array($timezone, \DateTimeZone::listIdentifiers());
    }

    /**
     * get gmt offset in minutes
     * @param  varchar $timezone php compatible timezone
     * @return int
     */
    public static function getGmtOffset($timezone)
    {
        $now = new \DateTime();
        if (System::isValidTimezone($timezone)) {
            $now->setTimezone(new \DateTimeZone($timezone));
        }

        return ($now->getOffset() / 60);
    }

    public function formatGmtOffset($offset)
    {
        $hours = intval($offset / 3600);
        $minutes = abs(intval($offset % 3600 / 60));

        return ($offset ? sprintf('%+03d:%02d', $hours, $minutes) : '');
    }

    public function formatTimezoneName($name)
    {
        $name = str_replace('/', ', ', $name);
        $name = str_replace('_', ' ', $name);
        $name = str_replace('St ', 'St. ', $name);

        return $name;
    }
}
