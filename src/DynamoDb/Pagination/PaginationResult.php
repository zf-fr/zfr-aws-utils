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
