<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Adapter\Lua\Tests\Action;

use Fusio\Adapter\Lua\Action\WorkerLuaLocal;
use Fusio\Adapter\Lua\Tests\LuaTestCase;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * WorkerLuaLocalTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org/
 */
class WorkerLuaLocalTest extends LuaTestCase
{
    public function testHandle()
    {
        $code = <<<LUA
return function (request, context, connector, response, dispatcher, logger)
  data = {}
  data["request"] = request
  data["context"] = context

  return response.build(200, {}, data)
end
LUA;

        $parameters = $this->getParameters([
            'code' => $code,
        ]);

        $action   = $this->getActionFactory()->factory(WorkerLuaLocal::class);
        $response = $action->handle($this->getRequest(), $parameters, $this->getContext());

        $this->assertInstanceOf(HttpResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals(['a' => true], $response->getBody());
    }
}
