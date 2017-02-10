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

namespace ZfrAwsUtils\DynamoDb;

use Aws\CommandInterface;

/**
 * This is middleware that hooks into DynamoDB commands and prepends table name with a prefix.
 * For instance, if we do a request for table "users", this middleware will automatically
 * modify the table name to "{prefix}.users". It does nothing if the table name already contains a dot (.)
 *
 * @author Daniel Gimenes
 */
class TableNamePrefixer
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param CommandInterface $command
     *
     * @return CommandInterface
     */
    public function __invoke(CommandInterface $command): CommandInterface
    {
        $commandName = $command->getName();

        // For batch requests, we need to modify the name of all tables within the batch
        if ($commandName === 'BatchWriteItem' || $commandName === 'BatchGetItem') {
            return $this->prefixBatchCommand($command);
        }

        $command['TableName'] = $this->resolveTableName($command['TableName']);

        return $command;
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    private function resolveTableName(string $tableName): string
    {
        // For tables that are already qualified, it returns the untouched name
        if (false !== strpos($tableName, '.')) {
            return $tableName;
        }

        return sprintf('%s.%s', $this->prefix, $tableName);
    }

    /**
     * @param CommandInterface $command
     *
     * @return CommandInterface
     */
    private function prefixBatchCommand(CommandInterface $command): CommandInterface
    {
        $newRequestItems = [];

        foreach ($command['RequestItems'] as $tableName => $requests) {
            $newTableName                = $this->resolveTableName($tableName);
            $newRequestItems[$newTableName] = $requests;
        }

        $command['RequestItems'] = $newRequestItems;

        return $command;
    }
}
