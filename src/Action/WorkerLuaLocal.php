<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2024 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Adapter\Lua\Action;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Engine\Worker\ExecuteBuilderInterface;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * WorkerLuaLocal
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class WorkerLuaLocal extends ActionAbstract
{
    private ExecuteBuilderInterface $executeBuilder;

    public function __construct(RuntimeInterface $runtime, ExecuteBuilderInterface $executeBuilder)
    {
        parent::__construct($runtime);

        $this->executeBuilder = $executeBuilder;
    }

    public function getName(): string
    {
        return 'Worker-LUA-Local';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        if (!class_exists(\LuaSandbox::class)) {
            throw new \RuntimeException('PHP extension LuaSandbox is not installed s. https://pecl.php.net/package/LuaSandbox');
        }

        $code = $configuration->get('code');

        $sandbox = new \LuaSandbox();
        $sandbox->setMemoryLimit(50 * 1024 * 1024);
        $sandbox->setCPULimit(10);

        [$function] = $sandbox->loadString($code)->call();

        if (!$function instanceof \LuaSandboxFunction) {
            throw new ConfigurationException('Provided LUA code must return a function');
        }

        $execute = $this->executeBuilder->build($request, $context);

        return $function->call(
            $execute->getRequest(),
            $execute->getContext(),
            $this->connector,
            $this->response,
            $this->dispatcher,
            $this->logger
        );
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newTextArea('code', 'Code', 'php', 'The LUA code of this action'));
    }
}
