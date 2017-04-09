<?php

namespace ZfrAwsUtils\DynamoDb;

interface ResourceHydratorInterface
{
    /**
     * Builds an array from a DynamoDB item
     *
     * @param array $dynamoDbItem
     * @return array
     */
    public function __invoke(array $dynamoDbItem): array;
}
