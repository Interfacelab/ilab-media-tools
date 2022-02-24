<?php

namespace MediaCloud\Vendor\Illuminate\Filesystem;
use MediaCloud\Vendor\Illuminate\Contracts\Cache\Repository;
use League\Flysystem\Cached\Storage\AbstractCache;

class Cache extends AbstractCache
{
    /**
     * The cache repository implementation.
     *
     * @var \MediaCloud\Vendor\Illuminate\Contracts\Cache\Repository
     */
    protected $repository;

    /**
     * The cache key.
     *
     * @var string
     */
    protected $key;

    /**
     * The cache expiration time in seconds.
     *
     * @var int|null
     */
    protected $expire;

    /**
     * Create a new cache instance.
     *
     * @param \MediaCloud\Vendor\Illuminate\Contracts\Cache\Repository  $repository
     * @param  string  $key
     * @param  int|null  $expire
     * @return void
     */
    public function __construct(Repository $repository, $key = 'flysystem', $expire = null)
    {
        $this->key = $key;
        $this->expire = $expire;
        $this->repository = $repository;
    }

    /**
     * Load the cache.
     *
     * @return void
     */
    public function load()
    {
        $contents = $this->repository->get($this->key);

        if (! is_null($contents)) {
            $this->setFromStorage($contents);
        }
    }

    /**
     * Persist the cache.
     *
     * @return void
     */
    public function save()
    {
        $contents = $this->getForStorage();

        $this->repository->put($this->key, $contents, $this->expire);
    }
}
