<?php

namespace ZfrAwsUtils\DynamoDb\Pagination;

use Assert\Assertion;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Result;
use ZfrAwsUtils\DynamoDb\ResourceHydratorInterface;
use ZfrAwsUtils\Exception\InvalidArgumentException;

/**
 * @author Daniel Gimenes
 */
class DynamoDbPaginator
{
    const ORDER_ASC          = 'ASC';
    const ORDER_DESC         = 'DESC';
    const DIRECTION_PREVIOUS = 'previous';
    const DIRECTION_NEXT     = 'next';

    /**
     * @var DynamoDbClient
     */
    private $dynamoDbClient;

    /**
     * @var Marshaler
     */
    private $marshaler;

    /**
     * @param DynamoDbClient $dynamoDbClient
     * @param Marshaler      $marshaler
     */
    public function __construct(DynamoDbClient $dynamoDbClient, Marshaler $marshaler)
    {
        $this->dynamoDbClient = $dynamoDbClient;
        $this->marshaler      = $marshaler;
    }

    /**
     * @param array                     $query
     * @param int                       $limit
     * @param string                    $order
     * @param CursorStrategyInterface   $cursorStrategy
     * @param ResourceHydratorInterface $resourceHydrator
     * @param string|null               $cursor
     * @param string|null               $direction
     *
     * @return PaginationResult
     */
    public function paginate(
        array $query,
        int $limit,
        string $order,
        CursorStrategyInterface $cursorStrategy,
        ResourceHydratorInterface $resourceHydrator,
        string $cursor = null,
        string $direction = null
    ): PaginationResult {
        Assertion::greaterOrEqualThan($limit, 1, 'Limit must be greater or equal than 1');
        Assertion::choice($order, [self::ORDER_ASC, self::ORDER_DESC], 'Order must be either ASC or DESC');

        // Setup ordering, true for ASC and false for DESC
        $query['ScanIndexForward'] = self::ORDER_ASC === $order;

        // If no cursor is present, then get the first page
        if (null === $cursor) {
            $result = $this->runQuery($query, $limit);

            return new PaginationResult(
                $this->hydrateResources($resourceHydrator, $result),
                null,
                $this->buildCursorFromLastItem($cursorStrategy, $result)
            );
        }

        // Otherwise, set exclusive start key from cursor
        $query['ExclusiveStartKey'] = $this->marshaler->marshalItem($cursorStrategy->buildKeyFromCursor($cursor));

        if (self::DIRECTION_PREVIOUS === $direction) {
            // Reverse query ordering when paginating to previous page
            $query['ScanIndexForward'] = ! $query['ScanIndexForward'];

            $result = $this->runQuery($query, $limit);

            return new PaginationResult(
                array_reverse($this->hydrateResources($resourceHydrator, $result)),
                $this->buildCursorFromLastItem($cursorStrategy, $result),
                $this->buildCursorFromFirstItem($cursorStrategy, $result)
            );
        }

        if (self::DIRECTION_NEXT === $direction) {
            $result = $this->runQuery($query, $limit);

            return new PaginationResult(
                $this->hydrateResources($resourceHydrator, $result),
                $this->buildCursorFromFirstItem($cursorStrategy, $result),
                $this->buildCursorFromLastItem($cursorStrategy, $result)
            );
        }

        throw new InvalidArgumentException('Invalid pagination direction, it must be either previous or next');
    }

    /**
     * Runs the DynamoDB query with limit + 1
     * and removes the last item from result if item count exceeds page limit
     *
     * @param array $query
     * @param int   $limit
     *
     * @return Result
     */
    private function runQuery(array $query, int $limit): Result
    {
        $query['Limit']  = $limit + 1;
        $result          = $this->dynamoDbClient->query($query);
        $result['Items'] = array_slice($result['Items'], 0, $limit);

        return $result;
    }

    /**
     * @param ResourceHydratorInterface $resourceHydrator
     * @param Result                    $result
     *
     * @return array
     */
    private function hydrateResources(ResourceHydratorInterface $resourceHydrator, Result $result): array
    {
        return array_map(function (array $item) use ($resourceHydrator) {
            return $resourceHydrator($this->marshaler->unmarshalItem($item));
        }, $result->get('Items'));
    }

    /**
     * @param CursorStrategyInterface $cursorStrategy
     * @param Result                  $result
     *
     * @return null|string
     */
    private function buildCursorFromFirstItem(CursorStrategyInterface $cursorStrategy, Result $result)
    {
        if (0 === $result->get('Count')) {
            return null;
        }

        $items     = $result->get('Items');
        $firstItem = $this->marshaler->unmarshalItem(reset($items));

        return $cursorStrategy->buildCursorFromKey($firstItem);
    }

    /**
     * @param CursorStrategyInterface $cursorStrategy
     * @param Result                  $result
     *
     * @return null|string
     */
    private function buildCursorFromLastItem(CursorStrategyInterface $cursorStrategy, Result $result)
    {
        if (! $result->hasKey('LastEvaluatedKey')) {
            return null;
        }

        $items    = $result->get('Items');
        $lastItem = $this->marshaler->unmarshalItem(end($items));

        return $cursorStrategy->buildCursorFromKey($lastItem);
    }
}
