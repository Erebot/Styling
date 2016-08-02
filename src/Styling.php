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

namespace Erebot;

/**
 * \brief
 *      Provides styling (formatting) features.
 *
 *  Given a format string (a template), this class can perform
 *  styling on that template to produce complex messages.
 *
 *  A template is composed of a single string, which may contain
 *  special markup to insert dynamic content, add formatting
 *  attributes to the text (like bold, underline, colors), etc.
 *
 *  <table>
 *      <caption>Special markup in templates</caption>
 *
 *      <tr>
 *          <th>Markup</th>
 *          <th>Role</th>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;b&gt;...&lt;/b&gt;</td>
 *          <td>The text is rendered in \b{bold}</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;u&gt;...&lt;/u&gt;</td>
 *          <td>The text is rendered \u{underlined}</td>
 *      </tr>
 *
 *      <tr>
 *          <td>&lt;var name="..."/&gt;</td>
 *          <td>This markup gets replaced by the content
 *              of the given variable</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;color<br/>
 *                  &nbsp;&nbsp;fg="..."<br/>
 *                  &nbsp;&nbsp;bg="..."&gt;<br/>
 *                  &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *              &lt;/color&gt;
 *          </td>
 *          <td>The text is rendered with the given foreground (\a fg)
 *              and background (\a bg) colors. The value of the \a fg and
 *              \a bg attributes may be either an integer (see the COLOR_*
 *              constants in this class) or the name of the color (again,
 *              supported colors are named after the COLOR_* constants).</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;for<br/>
 *                  &nbsp;&nbsp;from="..."<br/>
 *                  &nbsp;&nbsp;item="..."<br/>
 *                  &nbsp;&nbsp;key="..."<br/>
 *                  &nbsp;&nbsp;sep=&quot;,&nbsp;&quot;<br/>
 *                  &nbsp;&nbsp;last=&quot;&nbsp;&amp;amp;&nbsp;&quot;&gt;<br/>
 *                  &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *              &lt;/for&gt;
 *          </td>
 *          <td>This markup loops over the associative array in \a from.
 *              The key for each entry in that array is stored in the
 *              temporary variable named by the \a key attribute if given,
 *              while the associated value is stored in the temporary
 *              variable named by \a item. The value of \a sep (alias
 *              \a separator) is appended automatically between each entry
 *              of the array, except between the last two entries.
 *              The value of \a last (alias \a last_separator) is used
 *              to separate the last two entries.
 *              By default, no temporary variable is created for the key,
 *              ", " is used as the main \a separator and " & " is used as
 *              the \a last_separator.</td>
 *      </tr>
 *
 *      <tr>
 *          <td>
 *              &lt;plural var="..."&gt;<br/>
 *                  &nbsp;&nbsp;&lt;case form="..."&gt;<br/>
 *                      &nbsp;&nbsp;&nbsp;&nbsp;...<br/>
 *                  &nbsp;&nbsp;&lt;/case&gt;<br/>
 *              &lt;/plural&gt;
 *          </td>
 *          <td>Handles plurals. Depending on the value of the variable
 *              pointed by \a var, one of the cases will be used. The page at
 * http://unicode.org/cldr/data/charts/supplemental/language_plural_rules.html
 *              references every available form per language.</td>
 *      </tr>
 *  </table>
 */
class Styling implements \Erebot\StylingInterface
{
    /// Translator to use to improve rendering.
    protected $translator;

    /// Maps some scalar types to a typed variable.
    protected $cls;

    /**
     * Construct a new styling object.
     *
     * \param ::Erebot::IntlInterface $translator
     *      A translator object, used to determine the correct
     *      pluralization rules.
     */
    public function __construct(\Erebot\IntlInterface $translator)
    {
        $this->translator  = $translator;
        $this->cls = array(
            'int'       => '\\Erebot\\Styling\\Variables\\IntegerVariable',
            'float'     => '\\Erebot\\Styling\\Variables\\FloatVariable',
            'string'    => '\\Erebot\\Styling\\Variables\\StringVariable',
        );
    }

    /**
     * Returns the class used to wrap scalar types.
     *
     * \param string $type
     *      Name of a scalar type that can be wrapped
     *      by this class automatically. Must be one
     *      of "int", "string" or "float".
     *
     * \retval string
     *      Name of the class that can be used to wrap
     *      variables of the given type.
     */
    public function getClass($type)
    {
        if (!isset($this->cls[$type])) {
            throw new \InvalidArgumentException('Invalid type');
        }
        return $this->cls[$type];
    }

    /**
     * Sets the class to use to wrap a certain scalar type.
     *
     * \param string $type
     *      Name of a scalar type that can be wrapped
     *      by this class automatically. Must be one
     *      of "int", "string" or "float".
     *
     * \param string $cls
     *      Name of the class that can be used to wrap
     *      variables of the given type.
     */
    public function setClass($type, $cls)
    {
        if (!isset($this->cls[$type])) {
            throw new \InvalidArgumentException('Invalid type');
        }
        if (!is_string($cls)) {
            throw new \InvalidArgumentException(
                'Expected a string for the class'
            );
        }
        if (!class_exists($cls)) {
            throw new \InvalidArgumentException('Class not found');
        }
        if (!($cls instanceof \Erebot\Styling\VariableInterface)) {
            throw new \InvalidArgumentException(
                'Must be a subclass of \\Erebot\\Styling\\VariableInterface'
            );
        }
        $this->cls[$type] = $cls;
    }

    /**
     * Checks whether the given variable name is valid
     * and throws an exception if its not.
     *
     * \param string $var
     *      Variable name to test.
     *
     * \throw ::InvalidArgumentException
     *      The given variable name is invalid.
     *
     * \return
     *      This method does not return anything.
     */
    protected static function checkVariableName($var)
    {
        if (!preg_match('/^[a-zA-Z0-9_\.]+$/D', $var)) {
            throw new \InvalidArgumentException(
                'Invalid variable name "'.$var.'". '.
                'Variable names may only contain alphanumeric '.
                'characters, underscores ("_") and dots (".").'
            );
        }
    }

    // @codingStandardsIgnoreStart
    public function _($template, array $vars = array())
    {
        $source = $this->translator->_($template);
        return $this->render($source, $vars);
        // @codingStandardsIgnoreEnd
    }

    public function render($template, array $vars = array())
    {
        // For basic strings that don't contain any markup,
        // we try to be as efficient as possible.
        if (strpos($template, '<') === false &&
            strpos($template, '&') === false) {
            return $template;
        }

        $attributes = array(
            'underline' => 0,
            'bold'      => 0,
            'bg'        => null,
            'fg'        => null,
        );

        $variables = array();
        foreach ($vars as $name => $var) {
            $variables[$name] = $this->wrapScalar($var, $name);
        }

        $dom = self::parseTemplate($template);
        $result = $this->parseNode(
            $dom->documentElement,
            $attributes,
            $variables
        );

        $pattern =  '@'.
                    '\\003,(?![01])'.
                    '|'.
                    '\\003(?:[0-9]{2})?,(?:[0-9]{2})?(?:\\002\\002)?(?=\\003)'.
                    '|'.
                    '(\\003(?:[0-9]{2})?,)\\002\\002(?![0-9])'.
                    '|'.
                    '(\\003[0-9]{2})\\002\\002(?!,)'.
                    '@';
        $replace    = '\\1\\2';
        $result     = preg_replace($pattern, $replace, $result);
        return $result;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Wraps a scalar into the appropriate
     * styling object.
     *
     * \param mixed $var
     *      Either a scalar, an array or an object implementing
     *      the ::Erebot::Styling::VariableInterface interface.
     *      Scalar values will be wrapped with the appropriate
     *      object while arrays and objects are returned untouched.
     *
     * \param string $name
     *      Name of given variable.
     *
     * \retval array
     *      If \a $var referred to an array, it is returned
     *      without any modification.
     *
     * \retval ::Erebot::Styling::VariableInterface_Variable
     *      Objects that implement ::Erebot::Styling::VariableInterface
     *      are returned without any modification. Scalar values
     *      are wrapped into the appropriate object implementing
     *      the ::Erebot::Styling::VariableInterface interface and
     *      the resulting object is returned.
     *
     * \throw ::InvalidArgumentException
     *      Either the given name is invalid, or the given value
     *      was not a scalar.
     *
     * \note
     *      In the context of this method, objects that can be
     *      converted to a string (ie. implement __toString())
     *      are treated as if they were a string (and thus, are
     *      considered as scalar values).
     */
    protected function wrapScalar($var, $name)
    {
        self::checkVariableName($name);

        if (is_object($var)) {
            if ($var instanceof \Erebot\Styling\VariableInterface) {
                return $var;
            }

            if (!is_callable(array($var, '__toString'), false)) {
                throw new \InvalidArgumentException(
                    $name.' must be a scalar or an instance of '.
                    '\\Erebot\\Styling\\VariableInterface'
                );
            }
        }

        if (is_array($var)) {
            return $var;
        }

        if (is_string($var) || is_callable(array($var, '__toString'), false)) {
            $cls = $this->cls['string'];
        } elseif (is_int($var)) {
            $cls = $this->cls['int'];
        } elseif (is_float($var)) {
            $cls = $this->cls['float'];
        } else {
            throw new \InvalidArgumentException(
                'Unsupported scalar type ('.gettype($var).') for "'.$name.'"'
            );
        }
        return new $cls($var);
    }

    /**
     * Parses a template into a DOM.
     *
     * \param string $source
     *      Template to parse.
     *
     * \retval Erebot::DOM
     *      DOM object constructed
     *      from the template.
     *
     * \throw ::InvalidArgumentException
     *      The template was malformed
     *      or invalid.
     */
    protected static function parseTemplate($source)
    {
        $source =
            '<msg xmlns="http://www.erebot.net/xmlns/erebot/styling">'.
            $source.
            '</msg>';
        $schema = dirname(__DIR__) .
                    DIRECTORY_SEPARATOR . 'data' .
                    DIRECTORY_SEPARATOR . 'styling.rng';
        $dom    =   new \Erebot\DOM();
        $dom->substituteEntities    = true;
        $dom->resolveExternals      = false;
        $dom->recover               = true;
        $ue     = libxml_use_internal_errors(true);
        $dom->loadXML($source);
        $valid  = $dom->relaxNGValidate($schema);
        $errors = $dom->getErrors();
        libxml_use_internal_errors($ue);

        if (!$valid || count($errors)) {
            // Some unpredicted error occurred,
            // show some (hopefully) useful information.
            if (class_exists('\\Plop')) {
                $logger = \Plop::getInstance();
                $logger->error(print_r($errors, true));
            }
            throw new \InvalidArgumentException(
                'Error while validating the message'
            );
        }
        return $dom;
    }

    /**
     * This is the main parsing method.
     *
     * \param DOMNode $node
     *      The node being parsed.
     *
     * \param array $attributes
     *      Array of styling attributes.
     *
     * \param array $vars
     *      Template variables that can be injected in the return.
     *
     * \retval string
     *      Parsing result, with styles applied as appropriate.
     */
    protected function parseNode($node, &$attributes, $vars)
    {
        $result     = '';
        $saved      = $attributes;

        if ($node->nodeType == XML_TEXT_NODE) {
            return $node->nodeValue;
        }

        if ($node->nodeType != XML_ELEMENT_NODE) {
            return '';
        }

        // Pre-handling.
        switch ($node->tagName) {
            case 'var':
                $lexer = new \Erebot\Styling\Lexer(
                    $node->getAttribute('name'),
                    $vars
                );
                $var = $lexer->getResult();
                if (!($var instanceof \Erebot\Styling\VariableInterface)) {
                    return (string) $var;
                }
                return $var->render($this->translator);

            case 'u':
                if (!$attributes['underline']) {
                    $result .= self::CODE_UNDERLINE;
                }
                $attributes['underline'] = 1;
                break;

            case 'b':
                if (!$attributes['bold']) {
                    $result .= self::CODE_BOLD;
                }
                $attributes['bold'] = 1;
                break;

            case 'color':
                $colors     = array('', '');
                $mapping    = array('fg', 'bg');

                foreach ($mapping as $pos => $color) {
                    $value = $node->getAttribute($color);
                    if ($value != '') {
                        $value = str_replace(array(' ', '-'), '_', $value);
                        if (strspn($value, '1234567890') !== strlen($value)) {
                            $reflector = new \ReflectionClass('\\Erebot\\StylingInterface');
                            if (!$reflector->hasConstant('COLOR_'.strtoupper($value))) {
                                throw new \InvalidArgumentException(
                                    'Invalid color "'.$value.'"'
                                );
                            }
                            $value = $reflector->getConstant('COLOR_'.strtoupper($value));
                        }
                        $attributes[$color] = sprintf('%02d', $value);
                        if ($attributes[$color] != $saved[$color]) {
                            $colors[$pos] = $attributes[$color];
                        }
                    }
                }

                $code = implode(',', $colors);
                if ($colors[0] != '' && $colors[1] != '') {
                    $result .= self::CODE_COLOR.$code;
                } elseif ($code != ',') {
                    $result .= self::CODE_COLOR.rtrim($code, ',').
                               self::CODE_BOLD.self::CODE_BOLD;
                }
                break;
        }

        if ($node->tagName == 'for') {
            // Handle loops.
            $savedVariables = $vars;
            $separator      = array(', ', ' & ');

            foreach (array('separator', 'sep') as $attr) {
                $attrNode       = $node->getAttributeNode($attr);
                if ($attrNode !== false) {
                    $separator[0] = $separator[1] = $attrNode->nodeValue;
                    break;
                }
            }

            foreach (array('last_separator', 'last') as $attr) {
                $attrNode       = $node->getAttributeNode($attr);
                if ($attrNode !== false) {
                    $separator[1] = $attrNode->nodeValue;
                    break;
                }
            }

            $loopKey    = $node->getAttribute('key');
            $loopItem   = $node->getAttribute('item');
            $loopFrom   = $node->getAttribute('from');
            $count      = count($vars[$loopFrom]);
            reset($vars[$loopFrom]);

            for ($i = 1; $i < $count; $i++) {
                if ($i > 1) {
                    $result .= $separator[0];
                }

                $item = each($vars[$loopFrom]);
                if ($loopKey !== null) {
                    $cls = $this->cls['string'];
                    $vars[$loopKey] = new $cls($item['key']);
                }
                $vars[$loopItem] = $this->wrapScalar(
                    $item['value'],
                    $loopItem
                );

                $result .= $this->parseChildren(
                    $node,
                    $attributes,
                    $vars
                );
            }

            $item = each($vars[$loopFrom]);
            if ($item === false) {
                $item = array('key' => '', 'value' => '');
            }

            if ($loopKey !== null) {
                $cls = $this->cls['string'];
                $vars[$loopKey] = new $cls($item['key']);
            }

            $vars[$loopItem] = $this->wrapScalar($item['value'], $loopItem);
            if ($count > 1) {
                $result .= $separator[1];
            }

            $result .= $this->parseChildren($node, $attributes, $vars);
            $vars = $savedVariables;
        } elseif ($node->tagName == 'plural') {
            // Handle plurals.
            /* We don't need the full set of features/complexity/bugs
             * ICU contains. Here, we use a simple "plural" formatter
             * to detect the right plural form to use. The formatting
             * steps are done without relying on ICU. */
            $attrNode = $node->getAttributeNode('var');
            if ($attrNode === false) {
                throw new \InvalidArgumentException(
                    'No variable name given'
                );
            }

            $lexer = new \Erebot\Styling\Lexer($attrNode->nodeValue, $vars);
            $value = $lexer->getResult();
            if ($value instanceof \Erebot\Styling\VariableInterface) {
                $value = $value->getValue();
            }
            $value          = (int) $value;

            $subcontents    = array();
            $pattern        = '{0,plural,';
            for ($child = $node->firstChild; $child != null; $child = $child->nextSibling) {
                if ($child->nodeType != XML_ELEMENT_NODE ||
                    $child->tagName != 'case') {
                    continue;
                }

                // See this class documentation for a link
                // which lists available forms for each language.
                $form = $child->getAttribute('form');
                $subcontents[$form] = $this->parseNode($child, $attributes, $vars);
                $pattern .= $form.'{'.$form.'} ';
            }
            $pattern .= '}';
            $locale = $this->translator->getLocale(
                \Erebot\IntlInterface::LC_MESSAGES
            );
            $formatter = new \MessageFormatter($locale, $pattern);
            // HACK: PHP <= 5.3.3 returns null when the pattern in invalid
            // instead of throwing an exception.
            // See http://bugs.php.net/bug.php?id=52776
            if ($formatter === null) {
                throw new \InvalidArgumentException('Invalid plural forms');
            }
            $correctForm = $formatter->format(array($value));
            $result .= $subcontents[$correctForm];
        } else {
            // Handle children.
            $result .= $this->parseChildren($node, $attributes, $vars);
        }

        // Post-handling : restore old state.
        switch ($node->tagName) {
            case 'u':
                if (!$saved['underline']) {
                    $result .= self::CODE_UNDERLINE;
                }
                $attributes['underline'] = 0;
                break;

            case 'b':
                if (!$saved['bold']) {
                    $result .= self::CODE_BOLD;
                }
                $attributes['bold'] = 0;
                break;

            case 'color':
                $colors     = array('', '');
                $mapping    = array('fg', 'bg');

                foreach ($mapping as $pos => $color) {
                    if ($attributes[$color] != $saved[$color]) {
                        $colors[$pos] = $saved[$color];
                    }
                    $attributes[$color] = $saved[$color];
                }

                $code = implode(',', $colors);
                if ($colors[0] != '' && $colors[1] != '') {
                    $result .= self::CODE_COLOR.$code;
                } elseif ($code != ',') {
                    $result .= self::CODE_COLOR.rtrim($code, ',').
                               self::CODE_BOLD.self::CODE_BOLD;
                }
                break;
        }

        return $result;
    }

    /**
     * This method is used to apply the parsing method
     * to children of an XML node.
     *
     * \param DOMNode $node
     *      The node being parsed.
     *
     * \param array $attributes
     *      Array of styling attributes.
     *
     * \param array $vars
     *      Template variables that can be injected in the result.
     *
     * \retval string
     *      Parsing result, with styles applied as appropriate.
     */
    private function parseChildren($node, &$attributes, $vars)
    {
        $result = '';
        for ($child = $node->firstChild; $child != null; $child = $child->nextSibling) {
            $result .=  $this->parseNode($child, $attributes, $vars);
        }
        return $result;
    }
}
