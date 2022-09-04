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
            if (version_compare(PHP_VERSION, '8.0.0', 'lt')) {
                if ($param->getClass() && $param->getClass()->isInstance($this->app)) {
                    $request->attributes->set($param->getName(), $this->app);
    
                    break;
                }
            } else {
                // php8.0
                $refClass = $this->getClass($param);
                if ($refClass && $refClass->isInstance($this->app)) {
                    $request->attributes->set($param->getName(), $this->app);
    
                    break;
                }
            }
        }

        return parent::doGetArguments($request, $controller, $parameters);
    }

    private function getClass(\ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        if (!$type || $type->isBuiltin())
            return NULL;

        if(!class_exists($type->getName()))
            return NULL;

      
        return  new \ReflectionClass($type->getName());
    }
}
