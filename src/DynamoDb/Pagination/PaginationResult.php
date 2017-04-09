<?php

namespace ZfrAwsUtils\DynamoDb\Pagination;

/**
 * @author Michaël Gallego
 */
class PaginationResult
{
    /**
     * @var array
     */
    private $childrenResources = [];

    /**
     * @var null|string
     */
    private $cursorBefore;

    /**
     * @var null|string
     */
    private $cursorAfter;

    /**
     * @param array[]     $childrenResources
     * @param string|null $cursorBefore
     * @param string|null $cursorAfter
     */
    public function __construct(array $childrenResources, string $cursorBefore = null, string $cursorAfter = null)
    {
        $this->childrenResources = $childrenResources;
        $this->cursorBefore      = $cursorBefore;
        $this->cursorAfter       = $cursorAfter;
    }

    /**
     * @return array
     */
    public function getChildrenResources(): array
    {
        return $this->childrenResources;
    }

    /**
     * @return null|string
     */
    public function getCursorBefore(): ?string
    {
        return $this->cursorBefore;
    }

    /**
     * @return null|string
     */
    public function getCursorAfter(): ?string
    {
        return $this->cursorAfter;
    }
}
