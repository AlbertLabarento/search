<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Laravel\Providers;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManagerInterface;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use EoneoPay\Externals\Logger\Logger;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use Illuminate\Contracts\Foundation\Application;
use LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider;
use LoyaltyCorp\Search\Client;
use LoyaltyCorp\Search\Exceptions\BindingResolutionException;
use LoyaltyCorp\Search\Helpers\EntityManagerHelper;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandler;
use LoyaltyCorp\Search\Interfaces\ClientInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\EntityManagerHelperInterface;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\IndexerInterface;
use LoyaltyCorp\Search\Interfaces\ManagerInterface;
use LoyaltyCorp\Search\Manager;
use Tests\LoyaltyCorp\Search\Stubs\ClientStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\HandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Handlers\Searches\NotSearchableStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine\EntityManagerStub as DoctrineEntityManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine\RegistryStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\EntityManagerStub as EoneoPayEntityManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;
use Tests\LoyaltyCorp\Search\TestCase;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Laravel\Providers\SearchServiceProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Required for testing all services
 */
final class SearchServiceProviderTest extends TestCase
{
    /**
     * Test register binds manager into container
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException If item requested from container doesn't exist
     */
    public function testContainerBindings(): void
    {
        $application = $this->createApplication();

        // Run registration
        (new SearchServiceProvider($application))->register();

        // Ensure services are bound
        self::assertInstanceOf(Client::class, $application->make(ClientInterface::class));
        self::assertInstanceOf(Manager::class, $application->make(ManagerInterface::class));
        self::assertInstanceOf(EntityManagerHelper::class, $application->make(EntityManagerHelperInterface::class));
        self::assertInstanceOf(
            RegisteredSearchHandler::class,
            $application->make(RegisteredSearchHandlerInterface::class)
        );
    }

    /**
     * Test service provider returns manager as a deferred service
     *
     * @return void
     */
    public function testDeferredProvider(): void
    {
        self::assertSame([
            ClientInterface::class,
            EntityManagerHelperInterface::class,
            IndexerInterface::class,
            ManagerInterface::class,
            RegisteredSearchHandlerInterface::class
        ], (new SearchServiceProvider(new ApplicationStub()))->provides());
    }

    /**
     * Ensure the Doctrine EntityManager resolution will throw our Exception if it cannot resolve
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function testDoctrineEntityManagerResolutionThrowsException(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Could not resolve Entity Manager from application container');

        $application = $this->createApplication();
        $application->singleton('registry', ClientStub::class);

        (new SearchServiceProvider($application))->register();

        $application->make(EntityManagerHelperInterface::class);
    }

    /**
     * Test handlers are correctly filtered by service provider
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException If item requested from container doesn't exist
     */
    public function testRegisteredSearchHandlerInterfaceFiltering(): void
    {
        $application = $this->createApplication();

        // Tag handler for service provider
        $application->tag([HandlerStub::class, NotSearchableStub::class], ['search_handler']);
        // The only available handler is when using get should beHandlerStub
        $expected = [new HandlerStub()];

        // Run registration
        (new SearchServiceProvider($application))->register();

        // Load manager from interface
        $registeredHandlers = $application->make(RegisteredSearchHandlerInterface::class);

        self::assertEquals($expected, $registeredHandlers->getAll());
    }

    /**
     * Create configured application instance for service provider testing
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    private function createApplication(): Application
    {
        $application = new ApplicationStub();

        // Bind logger to container so app->make on interface works
        $application->singleton(LoggerInterface::class, static function (): LoggerInterface {
            return new Logger();
        });

        // Bind Doctrine EntityManager to container so app->make on interface works
        $application->singleton(DoctrineEntityManagerInterface::class, static function (): DoctrineEntityManagerStub {
            return new DoctrineEntityManagerStub();
        });

        // Bind eoneopay EntityManager to container so app->make on interface works
        $application->singleton(EoneoPayEntityManagerInterface::class, static function (): EoneoPayEntityManagerStub {
            return new EoneoPayEntityManagerStub();
        });

        $application->singleton('registry', RegistryStub::class);

        return $application;
    }
}
