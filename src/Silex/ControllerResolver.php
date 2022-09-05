<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\HttpFoundation\Request;

/**
 * Adds Application as a valid argument for controllers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerResolver extends BaseControllerResolver
{
    protected $app;

    /**
     * Constructor.
     *
     * @param Application     $app    An Application instance
     * @param LoggerInterface $logger A LoggerInterface instance
     */
    public function __construct(Application $app, LoggerInterface $logger = null)
    {
        $this->app = $app;

        parent::__construct($logger);
    }

    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
        foreach ($parameters as $param) {
            if ($this->typeMatchesAppClass($param)) {
                $request->attributes->set($param->getName(), $this->app);
    
                break;
            }
        }

        return parent::doGetArguments($request, $controller, $parameters);
    }

    /**
     * @return bool
     */
    private function typeMatchesAppClass(\ReflectionParameter $param)
    {
        if (!method_exists($param, 'getType')) {
            return $param->getClass() && $param->getClass()->isInstance($this->app);
        }

        if (!($type = $param->getType()) || $type->isBuiltin()) {
            return false;
        }

        $class = new \ReflectionClass($type instanceof \ReflectionNamedType ? $type->getName() : (string) $type);

        return $class && $class->isInstance($this->app);
    }
}
