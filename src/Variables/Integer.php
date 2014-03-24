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
 *      A class used to format integers.
 */
class Integer implements \Erebot\Styling\Variables\IntegerInterface
{
    /// Integer to format.
    protected $value;

    /**
     * Constructor.
     *
     * \param int $value
     *      Integer value to format.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function render(\Erebot\Intl\IntlInterface $translator)
    {
        $locale = $translator->getLocale(\Erebot\Intl\IntlInterface::LC_NUMERIC);
        $formatter = new \NumberFormatter($locale, \NumberFormatter::IGNORE);
        $result = (string) $formatter->format(
            $this->value,
            \NumberFormatter::TYPE_INT32
        );
        return $result;
    }

    public function getValue()
    {
        return $this->value;
    }
}
