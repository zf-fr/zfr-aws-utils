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

namespace ZfrAwsUtils\Container;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Middleware;
use Aws\Sdk;
use Psr\Container\ContainerInterface;
use ZfrAwsUtils\DynamoDb\TableNamePrefixer;

/**
 * @author Michaël Gallego
 */
final class DynamoDbClientFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return DynamoDbClient
     */
    public function __invoke(ContainerInterface $container): DynamoDbClient
    {
        /** @var DynamoDbClient $dynamoDbClient */
        $dynamoDbClient = $container->get(Sdk::class)->createDynamoDb();
        $config         = $container->get('config')['zfr_aws_utils'] ?? [];

        // If a table prefix is configured, we attach a middleware to auto prefix table names
        if (! empty($config['dynamodb']['table_prefix'])) {
            $tableNamePrefixer = new TableNamePrefixer($config['dynamodb']['table_prefix']);

            $dynamoDbClient->getHandlerList()->prependInit(
                Middleware::mapCommand($tableNamePrefixer),
                'prefix-table'
            );
        }

        return $dynamoDbClient;
    }
}
