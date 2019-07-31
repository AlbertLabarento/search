<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Console\Commands;

use LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\OtherHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Helpers\RegisteredSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\IndexerStub;
use Tests\LoyaltyCorp\Search\TestCases\SearchIndexCommandTestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for thorough testing
 */
class SearchIndexCreateCommandTest extends SearchIndexCommandTestCase
{
    /**
     * Ensure the number of indices created matches the number of registered search handlers via container tagging
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testIndicesCreated(): void
    {
        $indexer = new IndexerStub();
        $handlers = [new HandlerStub(), new OtherHandlerStub()];
        // Two search handlers registered should result in 2 'created' calls
        $command = $this->createInstance($indexer, new RegisteredSearchHandlerStub($handlers));
        $this->bootstrapCommand($command);

        $command->handle();

        self::assertSame($handlers, $indexer->getCreatedHandlers());
    }

    /**
     * Create command instance
     *
     * @param \LoyaltyCorp\Search\Interfaces\IndexerInterface $indexer
     * @param \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface $registeredHandlers
     *
     * @return \LoyaltyCorp\Search\Bridge\Laravel\Console\Commands\SearchIndexCreateCommand
     */
    private function createInstance(
        IndexerInterface $indexer,
        RegisteredSearchHandlerInterface $registeredHandlers
    ): SearchIndexCreateCommand {
        return new SearchIndexCreateCommand(
            $indexer,
            $registeredHandlers
        );
    }
}