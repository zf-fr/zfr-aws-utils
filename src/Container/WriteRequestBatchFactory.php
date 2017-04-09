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
use Aws\DynamoDb\WriteRequestBatch;
use Aws\Exception\AwsException;
use Psr\Container\ContainerInterface;
use ZfrAwsUtils\DynamoDb\TableNamePrefixer;

/**
 * @author Daniel Gimenes
 */
final class WriteRequestBatchFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return WriteRequestBatch
     */
    public function __invoke(ContainerInterface $container): WriteRequestBatch
    {
        $dynamoDbClient = $container->get(DynamoDbClient::class);
        $config         = $container->get('config')['zfr_aws_utils'] ?? [];

        $batchConfig = [
            // By default if an error occurs during a batch request, DynamoDb do nothing.
            // We make sure here to rethrow the exception
            'error' => function (AwsException $exception) {
                throw $exception;
            },
        ];

        // If a table prefix is configured, we attach a middleware to auto prefix table names
        if (! empty($config['dynamodb']['table_prefix'])) {
            $batchConfig['before'] = new TableNamePrefixer($config['dynamodb']['table_prefix']);
        }

        return new WriteRequestBatch($dynamoDbClient, $batchConfig);
    }
}
