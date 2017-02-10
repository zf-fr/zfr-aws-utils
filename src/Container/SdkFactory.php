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

use Aws\CacheInterface;
use Aws\Sdk;
use Interop\Container\ContainerInterface;

/**
 * @author Daniel Gimenes
 */
final class SdkFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return Sdk
     */
    public function __invoke(ContainerInterface $container): Sdk
    {
        $awsConfig = $container->get('config')['aws'] ?? [];

        // In development, we hard-code the credentials directly. However on production we always use instance roles,
        // hence leaving the "credentials" property undefined. When this happen, we set up a cache so that instance
        // credentials are not fetched from Amazon servers on each request.
        if (! isset($awsConfig['credentials'])) {
            $awsConfig['credentials'] = $container->get(CacheInterface::class);
        }

        return new Sdk($awsConfig);
    }
}
