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

use Aws\CacheInterface;
use Aws\DoctrineCacheAdapter;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Sdk;
use Doctrine\Common\Cache\ApcuCache;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;
use ZfrAwsUtils\Container\DynamoDbClientFactory;
use ZfrAwsUtils\Container\SdkFactory;

return [
    'dependencies' => [
        'aliases' => [
            CacheInterface::class => DoctrineCacheAdapter::class,
        ],

        'factories' => [
            ApcuCache::class            => InvokableFactory::class,
            DoctrineCacheAdapter::class => ConfigAbstractFactory::class,
            DynamoDbClient::class       => DynamoDbClientFactory::class,
            Marshaler::class            => InvokableFactory::class,
            Sdk::class                  => SdkFactory::class,
        ],
    ],

    ConfigAbstractFactory::class => [
        DoctrineCacheAdapter::class => [ApcuCache::class],
    ],
];
