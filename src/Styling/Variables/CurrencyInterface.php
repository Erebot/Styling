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
 *      Interface for a monetary value embedded in a template.
 */
interface CurrencyInterface extends \Erebot\Styling\VariableInterface
{
    /**
     * Returns the currency the amount
     * is expressed in.
     *
     * \retval string
     *      The name of the currency used
     *      to express the amount.
     *
     * \retval NULL
     *      NULL is returned in case no currency
     *      was given at construction, meaning
     *      that the currency will automatically
     *      be selected at runtime.
     */
    public function getCurrency();
}
