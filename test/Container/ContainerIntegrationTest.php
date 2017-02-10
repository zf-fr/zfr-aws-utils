<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrAwsUtilsTest\Container;

use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;
use ZfrAwsUtils\ConfigProvider;

/**
 * @author Daniel Gimenes
 */
final class ContainerIntegrationTest extends TestCase
{
    public function testCreatesAllRegisteredServices()
    {
        $config = (new ConfigProvider())();

        // Add default config
        $config['aws'] = [
            'region'   => 'us-east-1',
            'DynamoDb' => ['version' => '2012-08-10'],
        ];

        /** @var ServiceManager $container */
        $container = new ServiceManager($config['dependencies']);

        $container->setService('config', $config);

        foreach ($config['dependencies']['aliases'] as $aliasName => $serviceName) {
            $this->assertInstanceOf($serviceName, $container->get($aliasName));
        }

        foreach ($config['dependencies']['factories'] as $serviceName => $factory) {
            $this->assertInstanceOf($serviceName, $container->get($serviceName));
        }
    }
}
