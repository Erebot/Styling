<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Erebot\Styling\Variables;

/**
 * \brief
 *      A class used to format dates/times.
 */
class DateTime implements \Erebot\Styling\Variables\DateTimeInterface
{
    /// A value expressing a date/time.
    protected $value;

    /// Type of rendering to apply to dates.
    protected $datetype;

    /// Type of rendering to apply to times.
    protected $timetype;

    /// Timezone to use during the rendering prcess.
    protected $timezone;

    /**
     * Constructor.
     *
     * \param mixed $value
     *      A value representing a date/time that will
     *      be formatted. This may be a DateTime object,
     *      an integer representing a Unix timestamp
     *      value (seconds since epoch, UTC) or an array
     *      in the format output by localtime().
     *
     * \param opaque $datetype
     *      The type of rendering to apply to dates.
     *      This is one of the constants defined in
     *      http://php.net/manual/en/class.intldateformatter.php
     *
     * \param opaque $timetype
     *      The type of rendering to apply to times.
     *      This is one of the constants defined in
     *      http://php.net/manual/en/class.intldateformatter.php
     *
     * \param string|null $timezone
     *      (optional) Timezone to use when rendering dates/times,
     *      eg. "Europe/Paris".
     *      The default is to use the system's default timezone,
     *      as returned by date_default_timezone_get().
     */
    public function __construct($value, $datetype, $timetype, $timezone = null)
    {
        $this->value    = $value;
        $this->datetype = $datetype;
        $this->timetype = $timetype;
        $this->timezone = $timezone;
    }

    public function render(\Erebot\IntlInterface $translator)
    {
        $timezone   =   ($this->timezone !== null)
                        ? $this->timezone
                        : date_default_timezone_get();

        $formatter  = new \IntlDateFormatter(
            $translator->getLocale(\Erebot\IntlInterface::LC_TIME),
            $this->datetype,
            $this->timetype,
            $timezone
        );
        return (string) $formatter->format($this->value);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDateType()
    {
        return $this->datetype;
    }

    public function getTimeType()
    {
        return $this->timetype;
    }

    public function getTimeZone()
    {
        return $this->timezone;
    }
}
