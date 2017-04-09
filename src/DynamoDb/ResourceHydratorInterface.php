<?php

namespace ZfrAwsUtils\DynamoDb;

use JsonSerializable;

interface ResourceHydratorInterface
{
    /**
     * Builds a JsonSerializable resource from a DynamoDB item
     *
     * @param array $dynamoDbItem
     *
     * @return JsonSerializable
     */
    public function __invoke(array $dynamoDbItem): JsonSerializable;
}
