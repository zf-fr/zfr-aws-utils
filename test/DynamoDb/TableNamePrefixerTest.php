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

namespace ZfrAwsUtilsTest\DynamoDb;

use Aws\Command;
use PHPUnit\Framework\TestCase;
use Traversable;
use ZfrAwsUtils\DynamoDb\TableNamePrefixer;

/**
 * @author Daniel Gimenes
 */
final class TableNamePrefixerTest extends TestCase
{
    public function testKeepCommandUntouchedIfTableAlreadyPrefixed()
    {
        $prefixer = new TableNamePrefixer('test');
        $result   = $prefixer(new Command('PutItem', ['TableName' => 'prefix.table_name']));

        $this->assertSame('prefix.table_name', $result['TableName']);
    }

    public function testPrefixesTableName()
    {
        $prefixer = new TableNamePrefixer('test');
        $result   = $prefixer(new Command('PutItem', ['TableName' => 'table_name']));

        $this->assertEquals('test.table_name', $result['TableName']);
    }

    /**
     * @dataProvider provideBatchCommandNames
     *
     * @param string $commandName
     */
    public function testPrefixesAllTableNamesOfBatchCommands(string $commandName)
    {
        $prefixer = new TableNamePrefixer('test');
        $result   = $prefixer(new Command($commandName, [
            'RequestItems' => [
                'table_1' => [],
                'table_2' => [],
            ],
        ]));

        $requestItems = $result['RequestItems'];

        $this->assertCount(2, $requestItems);
        $this->assertArrayHasKey('test.table_1', $requestItems);
        $this->assertArrayHasKey('test.table_2', $requestItems);
    }

    /**
     * @return Traversable
     */
    public function provideBatchCommandNames(): Traversable
    {
        yield ['BatchWriteItem'];
        yield ['BatchGetItem'];
    }
}
