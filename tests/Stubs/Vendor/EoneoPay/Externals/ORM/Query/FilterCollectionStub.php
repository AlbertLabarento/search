<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\Query;

use EoneoPay\Externals\ORM\Interfaces\Query\FilterCollectionInterface;

class FilterCollectionStub implements FilterCollectionInterface
{
    /**
     * {@inheritdoc}
     */
    public function disable($name): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function enable($name): void
    {
    }
}
