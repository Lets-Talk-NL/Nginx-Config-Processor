<?php

/**
 * This file is part of the Nginx Config Processor package.
 *
 * (c) Roman Piták <roman@pitak.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace RomanPitak\Nginx\Config;

class Scope
{

    /** @var Directive $parentDirective */
    private $parentDirective = null;

    /** @var Directive[] $directives */
    private $directives = array();

    /**
     * Create new Scope from the configuration string.
     *
     * @param \RomanPitak\Nginx\Config\String $configString
     * @return Scope
     * @throws Exception
     */
    public static function fromString(String $configString)
    {
        $scope = new Scope();
        while (false === $configString->eof()) {

            $configString->skipComment();

            $c = $configString->getChar();

            if (('a' <= $c) && ('z' >= $c)) {
                $scope->addDirective(Directive::fromString($configString));
                continue;
            }

            if ('}' === $configString->getChar()) {
                break;
            }

            $configString->inc();
        }
        return $scope;
    }

    /**
     * Create new Scope from a file.
     *
     * @param $filePath
     * @return Scope
     */
    public static function fromFile($filePath)
    {
        return self::fromString(new File($filePath));
    }

    /**
     * Add a Directive to the list of this Scopes directives
     *
     * Adds the Directive and sets the Directives parent Scope to $this.
     *
     * @param Directive $directive
     * @return $this
     */
    public function addDirective(Directive $directive)
    {
        if ($directive->getParentScope() !== $this) {
            $directive->setParentScope($this);
        }

        $this->directives[] = $directive;

        return $this;
    }

    /**
     * Get parent Directive.
     *
     * @return Directive|null
     */
    public function getParentDirective()
    {
        return $this->parentDirective;
    }

    /**
     * Set parent directive for this Scope.
     *
     * Sets parent directive for this Scope and also
     * sets the $parentDirective->setChildScope($this)
     *
     * @param Directive $parentDirective
     * @return $this
     */
    public function setParentDirective(Directive $parentDirective)
    {
        $this->parentDirective = $parentDirective;

        if ($parentDirective->getChildScope() !== $this) {
            $parentDirective->setChildScope($this);
        }

        return $this;
    }

    /**
     * Pretty print with indentation.
     *
     * @param $indentLevel
     * @param int $spacesPerIndent
     * @return string
     */
    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $rs = "";
        foreach ($this->directives as $directive) {
            $rs .= $directive->prettyPrint($indentLevel + 1, $spacesPerIndent);
        }

        return $rs;
    }

    public function __toString()
    {
        return $this->prettyPrint(-1);
    }

}
