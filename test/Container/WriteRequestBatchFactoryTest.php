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

use Aws\DynamoDb\DynamoDbClient;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ZfrAwsUtils\Container\WriteRequestBatchFactory;

/**
 * @author Daniel Gimenes
 */
final class WriteRequestBatchFactoryTest extends TestCase
{
    public function testCreatesWithTablePrefixer()
    {
        $container      = $this->prophesize(ContainerInterface::class);
        $dynamoDbClient = $this->prophesize(DynamoDbClient::class);
        $config         = ['zfr_aws_utils' => ['dynamodb' => ['table_prefix' => 'dev']]];

        $container->get(DynamoDbClient::class)->shouldBeCalled()->willReturn($dynamoDbClient);
        $container->get('config')->shouldBeCalled()->willReturn($config);

        (new WriteRequestBatchFactory())($container->reveal());
    }

    public function testCreatesWithoutTablePrefixer()
    {
        $container      = $this->prophesize(ContainerInterface::class);
        $dynamoDbClient = $this->prophesize(DynamoDbClient::class);

        $container->get(DynamoDbClient::class)->shouldBeCalled()->willReturn($dynamoDbClient);
        $container->get('config')->shouldBeCalled()->willReturn([]);

        (new WriteRequestBatchFactory())($container->reveal());
    }
}
