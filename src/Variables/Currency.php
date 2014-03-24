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
 *      A class used to format currencies.
 */
class Currency implements \Erebot\Styling\Variables\CurrencyInterface
{
    /// Amount.
    protected $value;

    /// Currency the amount is expressed in.
    protected $currency;

    /**
     * Constructor.
     *
     * \param float $value
     *      An amount of some currency to format.
     *
     * \param string|null $currency
     *      (optional) The currency the amount is expressed in.
     *      This is used to pick the right monetary symbol and
     *      conventions to format the amount.
     *      The default is to use the currency associated with
     *      the translator given during the actual formatting
     *      operation.
     */
    public function __construct($value, $currency = null)
    {
        $this->value       = $value;
        $this->currency    = $currency;
    }

    /**
     * \copydoc ::Erebot::Styling::VariableInterface::render()
     *
     * \note
     *      If no currency was passed to this class' constructor,
     *      the currency associated with the translator's locale
     *      is used.
     */
    public function render(\Erebot\Intl\IntlInterface $translator)
    {
        $locale = $translator->getLocale(\Erebot\Intl\IntlInterface::LC_MONETARY);
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $currency = ($this->currency !== null)
                    ? $this->currency
                    : $formatter->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL);
        return (string) $formatter->formatCurrency($this->value, $currency);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getCurrency()
    {
        return $this->currency;
    }
}
