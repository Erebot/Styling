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

namespace Erebot\Styling;

/**
 * \brief
 *      Interface for a typed variable to use
 *      in a template.
 */
interface VariableInterface
{
    /**
     * Renders the content of this variable.
     *
     * \param ::Erebot::Intl::TranslatorInterface $translator
     *      A translator that can be used to change the rendering
     *      depending on the target locale.
     *
     * \retval string
     *      The variable rendered as a string.
     */
    public function render(\Erebot\Intl\TranslatorInterface $translator);

    /**
     * Returns the value stored in this object.
     *
     * \retval mixed
     *      Value stored in this object.
     */
    public function getValue();
}
