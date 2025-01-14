<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface TransformableSearchHandlerInterface extends SearchHandlerInterface
{
    /**
     * Returns an iterable that will be used to fill the index when doing a full
     * index fill.
     *
     * @return iterable|mixed[]
     */
    public function getFillIterable(): iterable;

    /**
     * Get the class this search handler will support.
     *
     * @return string[] Fully Qualified Class Names that implement the Search Handler interface
     */
    public function getHandledClasses(): array;

    /**
     * Returns the identifier used externally for the transformed object.
     *
     * @param object $object
     *
     * @return mixed|null
     */
    public function getSearchId(object $object);

    /**
     * Transforms objects supplied into serialized search arrays that
     * should be indexed.
     *
     * @param mixed $object
     *
     * @return mixed[]|null
     */
    public function transform($object): ?array;
}
