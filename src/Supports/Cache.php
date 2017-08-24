<?php

namespace Core\Supports;

use Illuminate\Cache\Repository;

class Cache
{
    /**
     * Related tags will get flushed
     * if current tag doing flush.
     *
     * @var array
     */
    protected $relatedTags = array();

    /**
     * Cache manager with tags.
     *
     * @var \Illuminate\Cache\TaggedCache
     */
    protected $tagged;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Cache constructor.
     *
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Make new instances TaggedCache
     *
     * @param $tag
     * @return \Illuminate\Cache\TaggedCache
     */
    public function setTag($tag)
    {
        return $this->tags($tag);
    }

    /**
     * Set related tagged.
     *
     * @param array $relatedTags
     * @return void
     */
    public function setRelatedTags(array $relatedTags)
    {
        $this->relatedTags = $relatedTags;
    }

    /**
     * Make new instances tagged.
     *
     * @param string $tag
     * @return $this
     */
    public function makeCache($tag)
    {
        $this->tagged = $this->setTag($tag);

        return $this;
    }

    /**
     * Get taggedd content
     *
     * @param string $key
     * @return mixed
     */
    public function read($key)
    {
        return ($this->tagged->has($key)) ? $this->tagged->get($key) : false;
    }

    /**
     * Saving data into tagged
     *
     * @param string $key
     * @param string $value
     * @param string $time
     * @return mixed
     */
    public function save($key, $value, $time = 'forever')
    {
        if ($time == 'forever') {
            $this->tagged->forever($key, $value);
        } else {
            $this->tagged->add($key, $value, $time);
        }

        return $value;
    }

    /**
     * Remove data from tagged.
     *
     * @param $key
     * @return bool
     */
    public function remove($key)
    {
        return $this->tagged->forget($key);
    }

    /**
     * Flushing tagged for current tag
     *
     * @param array $related_tag
     * @return bool
     */
    public function flush($related_tag = array())
    {
        $this->repository->flush();

        $related_tag = array_merge($related_tag, $this->relatedTags);
        if (!empty($related_tag)) {
            foreach ($related_tag as $tag) {
                $this->setTag($tag)->flush();
            }
        }

        return true;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string $method
     * @param  array  $parameters
     * @return \Qasico\Rubricate\Builder
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->repository, $method], $parameters);
    }
}