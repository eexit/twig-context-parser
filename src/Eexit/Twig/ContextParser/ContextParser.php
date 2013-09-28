<?php

namespace Eexit\Twig\ContextParser;

/**
 * ContextParser.php
 *
 * Allows to return a Twig template static variables without actually rendering it.
 *
 * @author Joris Berthelot <joris@berthelot.tel>
 * @copyright Copyright (c) 2013, Joris Berthelot
 * @licence http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ContextParser
{
    /**
     * Twig_Environment
     */
    protected $env;

    /**
     * Extracted context from the node
     */
    protected $context;

    /**
     * @param \Twig_Environment $env
     */
    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
    }

    /**
     * Parses the template node recursively
     *
     * @param \Twig_NodeInterface $node
     */
    public function parse(\Twig_NodeInterface $node)
    {
        foreach ($node as $subNode) {

            if (! $subNode instanceof \Twig_NodeInterface) {
                continue;
            }

            $nodeClass = get_class($subNode);

            switch ($nodeClass) {
                case 'Twig_Node_Set':
                    $this->context[] = $this->env->getCompiler()->compile($subNode)->getSource();
                    break;
                
                default:
                    $this->parse($subNode);
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns the node context
     *
     * @return array $context
     */
    public function getContext()
    {
        if (empty($this->context)) {
            return;
        }

        array_unshift($this->context, "<?php \$context = array(); \n");
        $this->context[] = "return \$context;";
        $context = implode(null, $this->context);

        // Erases context to make sure the next template context won't append to
        $this->context = null;

        return eval('?>' . $context);
    }
}
