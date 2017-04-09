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

namespace ZfrAwsUtilsTest\DynamoDb\Pagination;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Result;
use JsonSerializable;
use PHPUnit\Framework\TestCase;
use ZfrAwsUtils\DynamoDb\Pagination\CursorStrategyInterface;
use ZfrAwsUtils\DynamoDb\Pagination\DynamoDbPaginator;
use ZfrAwsUtils\DynamoDb\ResourceHydratorInterface;

/**
 * @author Daniel Gimenes
 */
final class DynamoDbPaginatorTest extends TestCase
{
    public function testReturnsFirstPageIfNoCursorIsProvided()
    {
        $dynamoDbClient = $this->prophesize(DynamoDbClient::class);
        $marshaler      = new Marshaler();
        $paginator      = new DynamoDbPaginator($dynamoDbClient->reveal(), $marshaler);

        $query = [
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
        ];

        $dynamoDbClient->query([
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ScanIndexForward'          => true, // ASC order
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
            'Limit' => 4, // it will query with limit + 1
        ])->shouldBeCalled()->willReturn(new Result([
            'Count' => 4,
            'Items' => [
                $marshaler->marshalItem(['id' => '1', 'created_at' => 10001]),
                $marshaler->marshalItem(['id' => '2', 'created_at' => 10002]),
                $marshaler->marshalItem(['id' => '3', 'created_at' => 10003]),
                $marshaler->marshalItem(['id' => '4', 'created_at' => 10004]), // Last item will be removed
            ],
            'LastEvaluatedKey' => $marshaler->marshalItem([ // It returns the LastEvaluatedKey (item 4)
                'shop_domain' => 'test.myshopify.com',
                'created_at'  => 10004,
                'id'          => '4',
            ]),
        ]));

        $paginationResult = $paginator->paginate(
            $query,
            3,
            DynamoDbPaginator::ORDER_ASC,
            $this->getCursorStrategy(),
            $this->getResourceHydrator()
        );

        $this->assertCount(3, $paginationResult->getChildrenResources());
        $this->assertNull($paginationResult->getCursorBefore());
        $this->assertEquals('10003-3', $paginationResult->getCursorAfter());
    }

    public function testPaginatesToLastPage()
    {
        $dynamoDbClient = $this->prophesize(DynamoDbClient::class);
        $marshaler      = new Marshaler();
        $paginator      = new DynamoDbPaginator($dynamoDbClient->reveal(), $marshaler);

        $query = [
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
        ];

        $dynamoDbClient->query([
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ScanIndexForward'          => true, // ASC order
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
            'Limit' => 4, // it will query with limit + 1
            'ExclusiveStartKey' => $marshaler->marshalItem([ // it will extract ExclusiveStartKey from cursor
                'shop_domain' => 'test.myshopify.com',
                'created_at'  => 10003,
                'id'          => '3',
            ]),
        ])->shouldBeCalled()->willReturn(new Result([
            'Count' => 2, // Reached last page, so only 2 items
            'Items' => [
                $marshaler->marshalItem(['id' => '4', 'created_at' => 10004]),
                $marshaler->marshalItem(['id' => '5', 'created_at' => 10005]), // Last item should NOT be removed
            ],
            // No LastEvaluatedKey because it reached the end of table
        ]));

        $paginationResult = $paginator->paginate(
            $query,
            3,
            DynamoDbPaginator::ORDER_ASC,
            $this->getCursorStrategy(),
            $this->getResourceHydrator(),
            '10003-3',
            DynamoDbPaginator::DIRECTION_NEXT
        );

        $this->assertCount(2, $paginationResult->getChildrenResources());
        $this->assertEquals('10004-4', $paginationResult->getCursorBefore());
        $this->assertNull($paginationResult->getCursorAfter());
    }

    public function testPaginatesToPreviousPage()
    {
        $dynamoDbClient = $this->prophesize(DynamoDbClient::class);
        $marshaler      = new Marshaler();
        $paginator      = new DynamoDbPaginator($dynamoDbClient->reveal(), $marshaler);

        $query = [
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
        ];

        $dynamoDbClient->query([
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ScanIndexForward'          => false, // It reverts the ordering
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
            'Limit' => 4, // it will query with limit + 1
            'ExclusiveStartKey' => $marshaler->marshalItem([ // it will extract ExclusiveStartKey from cursor
                'shop_domain' => 'test.myshopify.com',
                'created_at'  => 10004,
                'id'          => '4',
            ]),
        ])->shouldBeCalled()->willReturn(new Result([
            'Count' => 3, // Reached FIRST page, so 3 items
            'Items' => [ // Results come in reversed order
                $marshaler->marshalItem(['id' => '3', 'created_at' => 10003]),
                $marshaler->marshalItem(['id' => '2', 'created_at' => 10002]),
                $marshaler->marshalItem(['id' => '1', 'created_at' => 10001]),
            ],
            // No LastEvaluatedKey because it reached the FIRST page
        ]));

        $paginationResult = $paginator->paginate(
            $query,
            3,
            DynamoDbPaginator::ORDER_ASC,
            $this->getCursorStrategy(),
            $this->getResourceHydrator(),
            '10004-4',
            DynamoDbPaginator::DIRECTION_PREVIOUS
        );

        $this->assertCount(3, $paginationResult->getChildrenResources());
        $this->assertNull($paginationResult->getCursorBefore());
        $this->assertEquals('10003-3', $paginationResult->getCursorAfter());
    }

    public function testReturnsEmptyCollectionIfNoItemFound()
    {
        $dynamoDbClient = $this->prophesize(DynamoDbClient::class);
        $marshaler      = new Marshaler();
        $paginator      = new DynamoDbPaginator($dynamoDbClient->reveal(), $marshaler);

        $query = [
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
        ];

        $dynamoDbClient->query([
            'TableName'                 => 'sms',
            'IndexName'                 => 'shop-domain-created-at-index',
            'KeyConditionExpression'    => 'shop_domain = :shopDomain',
            'ScanIndexForward'          => false, // DESC order
            'ExpressionAttributeValues' => [
                ':shopDomain' => ['S' => 'test.myshopify.com'],
            ],
            'Limit' => 4, // it will query with limit + 1
        ])->shouldBeCalled()->willReturn(new Result([
            'Count' => 0, // No item found
            'Items' => [],
            // No LastEvaluatedKey because it reached the end of table
        ]));

        $paginationResult = $paginator->paginate(
            $query,
            3,
            DynamoDbPaginator::ORDER_DESC,
            $this->getCursorStrategy(),
            $this->getResourceHydrator()
        );

        $this->assertEmpty($paginationResult->getChildrenResources());
    }

    /**
     * @return CursorStrategyInterface
     */
    private function getCursorStrategy()
    {
        return new class implements CursorStrategyInterface
        {
            public function buildCursorFromKey(array $dynamoDbItem): string
            {
                return $dynamoDbItem['created_at'] . '-' . $dynamoDbItem['id'];
            }

            public function buildKeyFromCursor(string $cursor): array
            {
                $cursorParts = explode('-', $cursor, 2);

                return [
                    'shop_domain' => 'test.myshopify.com',
                    'created_at'  => (int) reset($cursorParts),
                    'id'          => end($cursorParts),
                ];
            }
        };
    }

    /**
     * @return ResourceHydratorInterface
     */
    private function getResourceHydrator()
    {
        return new class implements ResourceHydratorInterface
        {
            public function __invoke(array $dynamoDbItem): array
            {
               return $dynamoDbItem;
            }
        };
    }
}
