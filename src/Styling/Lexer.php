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
 *      A lexer (tokenizer) for variables used
 *      in styling templates.
 */
class Lexer
{
    /// Formula to be tokenized.
    protected $formula;

    /// Length of the formula.
    protected $length;

    /// Current position in the formula.
    protected $position;

    /// Parser for the formula.
    protected $parser;


    /// Allow stuff such as "1234".
    const PATT_INTEGER  = '/^[0-9]+/';

    /// Allow stuff such as "1.23", "1." or ".23".
    const PATT_REAL     = '/^[0-9]*\.[0-9]+|^[0-9]+\.[0-9]*/';

    /// Pattern for variable names.
    const PATT_VAR_NAME = '/^[a-zA-Z0-9_\\.]+/';


    /**
     * Constructs a new lexer for some formula.
     *
     * \param string $formula
     *      Some formula to tokenize.
     *
     * \param array $vars
     *      An array of variables that may be used
     *      in the formula.
     */
    public function __construct($formula, array $vars)
    {
        $this->formula  = $formula;
        $this->length   = strlen($formula);
        $this->position = 0;
        $this->parser   = new \Erebot\Styling\Parser($vars);
        $this->tokenize();
    }

    /**
     * Returns the result of the formula.
     *
     * \retval mixed
     *      Result of the formula.
     */
    public function getResult()
    {
        return $this->parser->getResult();
    }

    /// This method does the actual work.
    protected function tokenize()
    {
        $operators = array(
            '(' =>  \Erebot\Styling\Parser::TK_PAR_OPEN,
            ')' =>  \Erebot\Styling\Parser::TK_PAR_CLOSE,
            '+' =>  \Erebot\Styling\Parser::TK_OP_ADD,
            '-' =>  \Erebot\Styling\Parser::TK_OP_SUB,
            '#' =>  \Erebot\Styling\Parser::TK_OP_COUNT,
        );

        while ($this->position < $this->length) {
            $c          = $this->formula[$this->position];
            $subject    = substr($this->formula, $this->position);

            // Operators ("(", ")", "+", "-" & "#").
            if (isset($operators[$c])) {
                $this->parser->doParse($operators[$c], $c);
                $this->position++;
                continue;
            }

            // Real numbers (eg. "3.14").
            if (preg_match(self::PATT_REAL, $subject, $matches)) {
                $this->position += strlen($matches[0]);
                $this->parser->doParse(
                    \Erebot\Styling\Parser::TK_NUMBER,
                    (double) $matches[0]
                );
                continue;
            }

            // Integers (eg. "42").
            if (preg_match(self::PATT_INTEGER, $subject, $matches)) {
                $this->position += strlen($matches[0]);
                $this->parser->doParse(
                    \Erebot\Styling\Parser::TK_NUMBER,
                    (int) $matches[0]
                );
                continue;
            }

            // Whitespace.
            if (strpos(" \t", $c) !== false) {
                $this->position++;
                continue;
            }

            // Variable names.
            if (preg_match(self::PATT_VAR_NAME, $subject, $matches)) {
                $this->position += strlen($matches[0]);
                $this->parser->doParse(
                    \Erebot\Styling\Parser::TK_VARIABLE,
                    $matches[0]
                );
                continue;
            }

            // Raise an exception.
            $this->parser->doParse(
                \Erebot\Styling\Parser::YY_ERROR_ACTION,
                $c
            );
        }

        // End of tokenization.
        $this->parser->doParse(0, 0);
    }
}
