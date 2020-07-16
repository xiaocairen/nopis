<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nopis\Lib\Http\Session\Storage\Handler;

/**
 * Description of RedisSessionHandler
 *
 * @author wangbin
 */
class RedisSessionHandler implements \SessionHandlerInterface
{

    private $redis;

    /**
     * @var int     Time to live in seconds
     */
    private $ttl;

    /**
     * @var string Key prefix for shared environments.
     */
    private $prefix;

    /**
     * Constructor.
     *
     * List of available options:
     *  * prefix: The prefix to use for the memcache keys in order to avoid collision
     *  * expiretime: The time to live in seconds
     *
     * @param \Memcache $redis A \Memcache instance
     * @param array     $options  An associative array of Memcache options
     *
     * @throws \InvalidArgumentException When unsupported options are passed
     */
    public function __construct(\Redis $redis, array $options = array())
    {
        if (($diff = array_diff(array_keys($options), array('prefix', 'expiretime')))) {
            throw new \InvalidArgumentException(sprintf(
                'The following options are not supported "%s"', implode(', ', $diff)
            ));
        }

        $this->redis = $redis;
        $this->ttl = isset($options['expiretime']) ? (int) $options['expiretime'] : 86400;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : 'eY2s';
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return $this->redis->close();
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return $this->redis->get($this->prefix.$sessionId) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        return $this->redis->set($this->prefix.$sessionId, $data, 0, time() + $this->ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return $this->redis->delete($this->prefix.$sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        // not required here because memcache will auto expire the records anyhow.
        return true;
    }
}
