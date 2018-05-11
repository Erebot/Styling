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
 *      A class used to format durations.
 */
class DurationVariable implements \Erebot\Styling\Variables\DurationInterface
{
    /// The duration to format (in seconds).
    protected $value;

    /**
     * Constructor.
     *
     * \param int $value
     *      A duration to format, given in seconds.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function render(\Erebot\Intl\TranslatorInterface $translator)
    {
        $locale = $translator->getLocale();
        $localedir = dirname(dirname(dirname(__DIR__))) .
                    DIRECTORY_SEPARATOR . 'data' .
                    DIRECTORY_SEPARATOR . 'i18n';
        $coreTranslator = \Erebot\Intl\GettextFactory::translation('Erebot_Styling', $localedir, array($locale));

        // DO NOT CHANGE THE CODE BELOW, ESPECIALLY COMMENTS & WHITESPACES.
        // It has all been carefully crafted to make both xgettext and
        // PHP_CodeSniffer happy! Also, it avoids relying on OS line endings
        // as it breaks xgettext on at least Windows platforms.

        $rule = $coreTranslator->_(
            // I18N: ICU rule used to format durations (using words).
            // Eg. 12345 is equal to "3 hours, 25 minutes and 45 seconds".
            // For examples of valid rules, see: http://goo.gl/q94xS
            // For the complete syntax, see also: http://goo.gl/jp2Bd
            //
            // The main rule is called "%with-words". It finds the highest
            // unit (week, day, hour, etc.) that fits into the value.
            // If the value is not an even multiple of the unit, we jump
            // to a "%%<unit>-sub" rule to format the remainder of the value
            // divided by the unit. This process is repeated until the whole
            // value has been processed.
            "%with-words:\n".
            "    0: 0 seconds;\n".
            "    1: 1 second;\n".
            "    2: =#0= seconds;\n".
            "    60/60: <%%min<;\n".
            "    61/60: <%%min<>%%min-sub>;\n".
            "    3600/3600: <%%hr<;\n".
            "    3601/3600: <%%hr<>%%hr-sub>;\n".
            "    86400/86400: <%%day<;\n".
            "    86401/86400: <%%day<>%%day-sub>;\n".
            "    604800/604800: <%%week<;\n".
            "    604801/604800: <%%week<>%%week-sub>;\n".
            "%%min:\n".
            "    1: 1 minute;\n".
            "    2: =#0= minutes;\n".
            "%%min-sub:\n".
            "    1: ' and <%with-words<;\n".
            "%%hr:\n".
            "    1: 1 hour;\n".
            "    2: =#0= hours;\n".
            "%%hr-sub:\n".
            "    1: <%%min-sub<;\n".
            "    60/60: ' and <%%min<;\n".
            "    61/60: , <%%min<>%%min-sub>;\n".
            "%%day:\n".
            "    1: 1 day;\n".
            "    2: =#0= days;\n".
            "%%day-sub:\n".
            "    1: <%%hr-sub<;\n".
            "    3600/3600: ' and <%%hr<;\n".
            "    3601/3600: , <%%hr<>%%hr-sub>;\n".
            "%%week:\n".
            "    1: 1 week;\n".
            "    2: =#0= weeks;\n".
            "%%week-sub:\n".
            "    1: <%%day-sub<;\n".
            "    86400/86400: ' and <%%day<;\n".
            "    86401/86400: , <%%day<>%%day-sub>;\n"
        );

        $formatter = new \NumberFormatter(
            $locale,
            \NumberFormatter::PATTERN_RULEBASED,
            $rule
        );
        return (string) $formatter->format($this->value);
    }

    public function getValue()
    {
        return $this->value;
    }
}
