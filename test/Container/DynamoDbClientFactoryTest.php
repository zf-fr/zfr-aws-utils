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

use Aws\Sdk;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ZfrAwsUtils\Container\DynamoDbClientFactory;

/**
 * @author Daniel Gimenes
 */
final class DynamoDbClientFactoryTest extends TestCase
{
    public function testCreatesWithTablePrefixer()
    {
        $sdk       = new Sdk(['region' => 'us-east-1', 'DynamoDb' => ['version' => '2012-08-10']]);
        $config    = ['zfr_aws_utils' => ['dynamodb' => ['table_prefix' => 'dev']]];
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(Sdk::class)->shouldBeCalled()->willReturn($sdk);
        $container->get('config')->shouldBeCalled()->willReturn($config);

        (new DynamoDbClientFactory())($container->reveal());
    }

    public function testCreatesWithoutTablePrefixer()
    {
        $sdk       = new Sdk(['region' => 'us-east-1', 'DynamoDb' => ['version' => '2012-08-10']]);
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(Sdk::class)->shouldBeCalled()->willReturn($sdk);
        $container->get('config')->shouldBeCalled()->willReturn([]);

        (new DynamoDbClientFactory())($container->reveal());
    }
}
