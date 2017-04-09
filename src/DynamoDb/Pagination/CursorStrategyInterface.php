<?php

namespace ZfrAwsUtils\DynamoDb\Pagination;

interface CursorStrategyInterface
{
    /**
     * Extracts the primary key from a DynamoDB item and creates a cursor
     *
     * @param array $dynamoDbItem
     *
     * @return string
     */
    public function buildCursorFromKey(array $dynamoDbItem): string;

    /**
     * Builds an unmarshaled ExclusiveStartKey from a cursor
     *
     * @param string $cursor
     *
     * @return array
     */
    public function buildKeyFromCursor(string $cursor): array;
}
