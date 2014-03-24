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
 *      Interface for a date and/or time embedded in a template.
 */
interface DateTimeInterface extends \Erebot\Styling\VariableInterface
{
    /**
     * Returns the type of rendering used
     * for dates.
     *
     * \retval opaque
     *      The type of rendering used for dates.
     *      This is one of the constants defined in
     *      http://php.net/manual/en/class.intldateformatter.php
     */
    public function getDateType();

    /**
     * Returns the type of rendering used
     * for times.
     *
     * \retval opaque
     *      The type of rendering used for dates.
     *      This is one of the constants defined in
     *      http://php.net/manual/en/class.intldateformatter.php
     */
    public function getTimeType();

    /**
     * Returns the timezone used to format values.
     *
     * \retval string
     *      The name of the timezone used to format
     *      dates and times (eg. "Europe/Paris").
     *
     * \retval NULL
     *      NULL is returned in case no timezone
     *      was specified during this object's
     *      creation. In that case, the current
     *      timezone of the system is used to
     *      render dates/times.
     */
    public function getTimeZone();
}
