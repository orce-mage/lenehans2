<?php

namespace Searchanise\SearchAutocomplete\Indexer;

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;

/**
 * Indexing operation handler for searchanise
 */
class GenericIndexerHandler implements IndexerInterface
{
    /**
     * {@inheritDoc}
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function cleanIndex($dimensions)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }
}
