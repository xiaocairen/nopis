<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Redis;

/**
 * Description of Redis
 *
 * @author wangbin
 */
class PhpRedis
{

    /**
     * @var \Nopis\Lib\Redis\PhpRedis
     */
    private static $instance;

    /**
     * @var \Nopis\Lib\Redis\_dbm_
     */
    private $dbm;

    /**
     * @var \Redis
     */
    private $cur;

    /**
     * Constructor.
     *
     * @param array $dbconfig
     */
    private function __construct(array $dbconfig)
    {
        $this->dbm = new _dbm_($dbconfig);
    }

    private function __clone(){}

    /**
     * 返回数据库链接实例
     *
     * @param array $dbconfig
     *
     * @return self
     *
     * @api
     */
    public static function getInstance(array $dbconfig)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($dbconfig);
        }

        return self::$instance;
    }

    public function readOrWrite(bool $writeable)
    {
        $this->cur = $writeable ? $this->getWriteableRedis() : $this->getReadableRedis();
    }

    /**
     * @return \Nopis\Lib\Redis\PhpRedis
     */
    public function changeReadable()
    {
        $this->cur = $this->getReadableRedis();
        return $this;
    }

    /**
     * @return \Nopis\Lib\Redis\PhpRedis
     */
    public function changeWriteable()
    {
        $this->cur = $this->getWriteableRedis();
        return $this;
    }

    /**
     * @return \Redis
     */
    public function getReadableRedis()
    {
        return $this->dbm->getReadableServer();
    }

    /**
     * @return \Redis
     */
    public function getWriteableRedis()
    {
        return $this->dbm->getWriteableServer();
    }

    /**
     * @param string $host
     * @param int $port
     * @param float $timeout    value in seconds (optional, default is 0 meaning unlimited)
     * @param type $reserved    should be NULL if retry_interval is specified
     * @param int $retry_interval   value in milliseconds (optional)
     * @param float $read_timeout   value in seconds (optional, default is 0 meaning unlimited)
     * @return boolean
     */
    /*public function connect(string $host, int $port, float $timeout = 0, $reserved = null, int $retry_interval = 100, float $read_timeout = 0)
    {
        return $this->cur->connect($host, $port, $timeout, $reserved, $retry_interval, $read_timeout);
    }*/

    /**
     * @param string $host
     * @param int $port
     * @param float $timeout    value in seconds (optional, default is 0 meaning unlimited)
     * @param type $persistent_id   identity for the requested persistent connection
     * @param int $retry_interval   value in milliseconds (optional)
     * @param float $read_timeout   value in seconds (optional, default is 0 meaning unlimited)
     * @return boolean
     */
    /*public function pconnect(string $host, int $port, float $timeout = 0, string $persistent_id = '', int $retry_interval = 100, float $read_timeout = 0)
    {
        return $this->cur->pconnect($host, $port, $timeout, $persistent_id, $retry_interval, $read_timeout);
    }*/

    /**
     * @return mixed  This method returns TRUE on success, or the passed string if called with an argument
     *                  Prior to PhpRedis 5.0.0 this command simply returned the string +PONG.
     */
    public function ping()
    {
        return $this->cur->ping();
    }

    /**
     * Change the selected database for the current connection.
     *
     * @param int $dbindex  the database number to switch to.
     * @return boolean  TRUE in case of success, FALSE in case of failure.
     */
    public function select(int $dbindex = 0)
    {
        return $this->cur->select($dbindex);
    }

    /**
     * @param string $password
     * @return boolean
     */
    /*public function auth(string $password)
    {
        return $this->cur->auth($password);
    }*/

    /**
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);	  // Don't serialize data
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);	  // Use built-in serialize/unserialize
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY); // Use igBinary serialize/unserialize
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_MSGPACK);  // Use msgpack serialize/unserialize
     *
     * $redis->setOption(Redis::OPT_PREFIX, 'myAppName:');	// use custom prefix on all keys
     *
     * Options for the SCAN family of commands, indicating whether to abstract
     * empty results from the user.  If set to SCAN_NORETRY (the default), phpredis
     * will just issue one SCAN command at a time, sometimes returning an empty
     * array of results.  If set to SCAN_RETRY, phpredis will retry the scan command
     * until keys come back OR Redis returns an iterator of zero
     *
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_NORETRY);
     * $redis->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY);
     *
     * @param type $name
     * @param type $value
     * @return boolean TRUE on success, FALSE on error.
     */
    public function setOption($name, $value)
    {
        return $this->cur->setOption($name, $value);
    }

    /**
     * Get client option.
     * // return Redis::SERIALIZER_NONE, Redis::SERIALIZER_PHP,
     * //        Redis::SERIALIZER_IGBINARY, or Redis::SERIALIZER_MSGPACK
     * $redis->getOption(Redis::OPT_SERIALIZER);
     *
     * @param type $name
     * @return mixed Parameter value
     */
    public function getOption($name)
    {
        return $this->cur->getOption($name);
    }

    /**
     * Swap one Redis database with another atomically
     *
     * @param int $db1
     * @param int $db2
     * @return boolean TRUE on success and FALSE on failure.
     */
    public function swapdb(int $db1, int $db2)
    {
        return $this->cur->swapdb($db1, $db2);
    }

    /**
     * Get or Set the Redis server configuration parameters
     *
     * @param string $operation     either GET or SET
     * @param string $key           key string for SET, glob-pattern for GET. See http://redis.io/commands/config-get for examples
     * @param string $value         only for SET
     * @return mixed    Associative array for GET, key -> value; bool for SET
     */
    public function config(string $operation, string $key, string $value = '')
    {
        return $this->cur->config($operation, $key, $value);
    }

    /**
     * Append specified string to the string stored in specified key
     *
     * @param string $key
     * @param string $val
     * @return int  Size of the value after the append
     */
    public function append(string $key, string $val)
    {
        return $this->cur->append($key, $val);
    }

    /**
     * Asynchronously save the dataset to disk (in background)
     *
     * @return boolean  TRUE in case of success, FALSE in case of failure. If a save is already running, this command will fail and return FALSE
     */
    public function bgSave()
    {
        return $this->cur->bgSave();
    }

    /**
     * Start the background rewrite of AOF (Append-Only File)
     *
     * @return boolean  TRUE in case of success, FALSE in case of failure.
     */
    public function bgrewriteaof()
    {
        return $this->cur->bgrewriteaof();
    }

    /**
     * Count bits in a string
     *
     * @param string $key
     * @return int The number of bits set to 1 in the value behind the input key
     */
    public function bitcount(string $key)
    {
        return $this->cur->bitcount($key);
    }

    /**
     * Bitwise operation on multiple keys
     *
     * @param string $operation     either "AND", "OR", "NOT", "XOR"
     * @param type $ret_key         return key
     * @param string $keys          multiple keys
     * @return int The size of the string stored in the destination key
     */
    public function bitop(string $operation, &$ret_key, string ...$keys)
    {
        return $this->cur->bitop($operation, $ret_key, ...$keys);
    }

    /**
     * Is a blocking lPop(rPop) primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * If all the list identified by the keys passed in arguments are empty,
     * blPop will block during the specified timeout until an element is pushed to one of those lists.
     * This element will be popped
     *
     * @param array $keys   containing the keys of the lists
     * @param int $timeout  blocking for in seconds
     * @return array
     */
    public function blPop(array $keys, int $timeout)
    {
        return $this->cur->blPop($keys, $timeout);
    }

    /**
     * Is a blocking lPop(rPop) primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * If all the list identified by the keys passed in arguments are empty,
     * blPop will block during the specified timeout until an element is pushed to one of those lists.
     * This element will be popped
     *
     * @param array $keys   containing the keys of the lists
     * @param int $timeout  blocking for in seconds
     * @return array
     */
    public function brPop(array $keys, int $timeout)
    {
        return $this->cur->brPop($keys, $timeout);
    }

    /**
     * A blocking version of rPopLPush, with an integral timeout in the third parameter
     *
     * @param string $srckey
     * @param string $dstkey
     * @param int $timeout
     * @return string|boolean The element that was moved in case of success, FALSE in case of timeout
     */
    public function brpoplpush(string $srckey, string $dstkey, int $timeout)
    {
        return $this->cur->brpoplpush($srckey, $dstkey, $timeout);
    }

    /**
     * Issue the CLIENT command with various arguments
     *
     * The Redis CLIENT command can be used in four ways
     *
     *    CLIENT LIST<br>
     *    CLIENT GETNAME<br>
     *    CLIENT SETNAME [name]<br>
     *    CLIENT KILL [ip:port]
     *
     * $redis->client('list'); // Get a list of clients<br>
     * $redis->client('getname'); // Get the name of the current connection<br>
     * $redis->client('setname', 'somename'); // Set the name of the current connection<br>
     * $redis->client('kill', <ip:port>); // Kill the process at ip:port
     *
     * @param string $command
     * @param string $arg
     * @return mixed  This will vary depending on which client command was executed
     *          CLIENT LIST will return an array of arrays with client information.<br>
     *          CLIENT GETNAME will return the client name or false if none has been set<br>
     *          CLIENT SETNAME will return true if it can be set and false if not<br>
     *          CLIENT KILL will return true if the client can be killed, and false if not<br>
     */
    public function client(string $command, string $arg = '')
    {
        return $this->cur->client($command, $arg);
    }

    /**
     * Return the number of keys in selected database
     *
     * @return int  DB size, in number of keys
     */
    public function dbSize()
    {
        return $this->cur->dbSize();
    }

    /**
     * Decrement the number stored at key by one. If the second argument is filled,
     * it will be used as the integer value of the decrement
     *
     * @param string $key
     * @return int  the new value
     */
    public function decr(string $key)
    {
        return $this->cur->decr($key);
    }

    /**
     * Decrement the number stored at key by one. If the second argument is filled,
     * it will be used as the integer value of the decrement
     *
     * @param string $key
     * @param int $val
     * @return int  the new value
     */
    public function decrBy(string $key, int $val)
    {
        return $this->cur->decrBy($key, $val);
    }

    /**
     * Remove specified keys<br>
     *
     * If you are connecting to Redis server >= 4.0.0 you can remove a key with the unlink method <br>
     * in the exact same way you would use del. The Redis unlink command is non-blocking and will <br>
     * perform the actual deletion asynchronously
     *
     * @param string[] $keys An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @return int Long Number of keys deleted
     */
    public function del(string ...$keys)
    {
        return $this->cur->del(...$keys);
    }

    /**
     * Remove specified keys<br>
     *
     * If you are connecting to Redis server >= 4.0.0 you can remove a key with the unlink method <br>
     * in the exact same way you would use del. The Redis unlink command is non-blocking and will <br>
     * perform the actual deletion asynchronously
     *
     * @param string[] $keys An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @return int Long Number of keys deleted
     */
    public function delete(string ...$keys)
    {
        return $this->cur->delete(...$keys);
    }

    /**
     * Remove specified keys<br>
     *
     * If you are connecting to Redis server >= 4.0.0 you can remove a key with the unlink method <br>
     * in the exact same way you would use del. The Redis unlink command is non-blocking and will <br>
     * perform the actual deletion asynchronously
     *
     * @param string[] $keys An array of keys, or an undefined number of parameters, each a key: key1 key2 key3 ... keyN
     * @return int Long Number of keys deleted
     */
    public function unlink(string ...$keys)
    {
        return $this->cur->unlink(...$keys);
    }

    /**
     * Dump a key out of a redis database, the value of which can later be passed into redis using the RESTORE command.
     * The data that comes out of DUMP is a binary representation of the key as Redis stores it
     *
     * @param string $key
     * @return mixed The Redis encoded value of the key, or FALSE if the key doesn't exist
     */
    public function dump(string $key)
    {
        return $this->cur->dump($key);
    }

    /**
     * Evaluate a LUA script serverside, from the SHA1 hash of the script instead of the script itself
     *
     * In order to run this command Redis will have to have already loaded the script,
     * either by running it or via the SCRIPT LOAD command
     *
     * @param string $script_sha
     * @param array $args
     * @param int $num_keys
     * @return mixed  See EVAL
     */
    public function evalsha(string $script_sha, array $args = [], int $num_keys = 0)
    {
        return $args ? $this->cur->evalsha($script_sha, $args, $num_keys) : $this->cur->evalsha($script_sha);
    }

    /**
     * Evaluate a LUA script serverside
     *
     * @param string $script
     * @param array $args
     * @param int $num_keys
     * @return mixed     What is returned depends on what the LUA script itself returns,
     *                  which could be a scalar value (int/string), or an array.
     *                  Arrays that are returned can also contain other arrays,
     *                  if that's how it was set up in your LUA script. If there is an error executing the LUA script,
     *                  the getLastError() function can tell you the message that came back from Redis (e.g. compile error)
     */
    public function eval(string $script, array $args = [], int $num_keys = 0)
    {
        return $args ? $this->cur->exec($script, $args, $num_keys?:null) : $this->cur->exec($script);
    }

    /**
     * Execute the Redis SCRIPT command to perform various operations on the scripting subsystem
     *
     * Usage
     *
     * $redis->script('load', $script);<br>
     * $redis->script('flush');<br>
     * $redis->script('kill');<br>
     * $redis->script('exists', $script1, [$script2, $script3, ...]);<br>
     *
     * @param string $command
     * @param string $scripts
     * @return mixed    SCRIPT LOAD will return the SHA1 hash of the passed script on success, and FALSE on failure.<br>
     *              SCRIPT FLUSH should always return TRUE<br>
     *              SCRIPT KILL will return true if a script was able to be killed and false if not<br>
     *              SCRIPT EXISTS will return an array with TRUE or FALSE for each passed script
     */
    public function script(string $command, string ...$scripts)
    {
        return $this->cur->script($command, ...$scripts);
    }

    /**
     * Verify if the specified key exists
     *
     * @param string $keys
     * @return int The number of keys tested that do exist
     */
    public function exists(string ...$keys)
    {
        return $this->cur->exists(...$keys);
    }

    /**
     * Sets an expiration date (a timeout) on an item. pexpire requires a TTL in milliseconds
     *
     * @param string $key   The key that will disappear
     * @param int $ttl      The key's remaining Time To Live, in seconds
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function expire(string $key, int $ttl)
    {
        return $this->cur->expire($key, $ttl);
    }

    /**
     * Sets an expiration date (a timestamp) on an item. pexpireAt requires a timestamp in milliseconds
     *
     * @param string $key       The key that will disappear
     * @param int $timestamp    Unix timestamp. The key's date of death, in seconds from Epoch time
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function expireAt(string $key, int $timestamp)
    {
        return $this->cur->expireAt($key, $timestamp);
    }

    /**
     * Remove all keys from all databases
     *
     * @param boolean $async    requires server version 4.0.0 or greater
     * @return boolean  Always TRUE
     */
    public function flushAll(bool $async)
    {
        return $this->cur->flushAll($async);
    }

    /**
     * Remove all keys from the current database
     *
     * @param boolean $async    requires server version 4.0.0 or greater
     * @return boolean  Always TRUE
     */
    public function flushDB(bool $async)
    {
        return $this->cur->flushDB($async);
    }

    /**
     * Get the value related to the specified key
     *
     * @param string $key
     * @return string|boolean String or Bool: If key didn't exist, FALSE is returned.
     *                      Otherwise, the value related to this key is returned
     */
    public function get(string $key)
    {
        return $this->cur->get($key);
    }

    /**
     * Get the password used to authenticate the phpredis connection
     *
     * @return string|null|false Returns the password used to authenticate a phpredis session
     *                          or NULL if none was used, and FALSE if we're not connected
     */
    public function getAuth()
    {
        return $this->cur->getAuth();
    }

    /**
     * Return a single bit out of a larger string
     *
     * @param string $key
     * @param int $offset
     * @return int the bit value (0 or 1)
     */
    public function getBit(string $key, int $offset)
    {
        return $this->cur->getBit($key, $offset);
    }

    /**
     * Get the database number phpredis is pointed to
     *
     * @return int|boolean Returns the database number (LONG) phpredis thinks it's pointing to or FALSE if we're not connected
     */
    public function getDBNum()
    {
        return $this->cur->getDBNum();
    }

    /**
     * Retrieve our host or unix socket that we're connected to
     *
     * @return mixed The host or unix socket we're connected to or FALSE if we're not connected
     */
    public function getHost()
    {
        return $this->cur->getHost();
    }

    /**
     * The last error message (if any)
     *
     * @return string|null A string with the last returned script based error message, or NULL if there is no error
     */
    public function getLastError()
    {
        return $this->cur->getLastError();
    }

    /**
     * Clear the last error message
     *
     * @return boolean
     */
    public function clearLastError()
    {
        return $this->cur->clearLastError();
    }

    /**
     * Gets the persistent ID that phpredis is using
     *
     * @return mixed Returns the persistent id phpredis is using (which will only be set if connected with pconnect),
     *               NULL if we're not using a persistent ID, and FALSE if we're not connected
     */
    public function getPersistentID()
    {
        return $this->cur->getPersistentID();
    }

    /**
     * Get the port we're connected to
     *
     * @return mixed Returns the port we're connected to or FALSE if we're not connected
     */
    public function getPort()
    {
        return $this->cur->getPort();
    }

    /**
     * Return a substring of a larger string
     *
     * Note: substr also supported but deprecated in redis.
     *
     * @param string $key
     * @param int $start
     * @param int $end
     * @return string the substring
     */
    public function getRange(string $key, int $start, int $end)
    {
        return $this->cur->getRange($key, $start, $end);
    }

    /**
     * Get the read timeout specified to phpredis or FALSE if we're not connected
     *
     * @return int|boolean   Returns the read timeout (which can be set using setOption
     *                      and Redis::OPT_READ_TIMEOUT) or FALSE if we're not connected
     */
    public function getReadTimeout()
    {
        return $this->cur->getReadTimeout();
    }

    /**
     * Sets a value and returns the previous entry at that key
     *
     * Example:<br>
     * $redis->set('x', '42');<br>
     * $exValue = $redis->getSet('x', 'lol');	// return '42', replaces x by 'lol'<br>
     * $newValue = $redis->get('x')'		// return 'lol'
     *
     * @param string $key
     * @param int|string $val
     * @return string   A string, the previous value located at this key
     */
    public function getSet(string $key, $val)
    {
        return $this->cur->getSet($key, $val);
    }

    /**
     * Get the (write) timeout in use for phpredis
     *
     * @return float|boolean The timeout (DOUBLE) specified in our connect call or FALSE if we're not connected
     */
    public function getTimeout()
    {
        return $this->cur->getTimeout();
    }

    /**
     * Removes a value from the hash stored at key. If the hash table doesn't exist, or the key doesn't exist, FALSE is returned
     *
     * @param string $key
     * @param string $hash_keys
     * @return int|boolean the number of deleted keys, 0 if the key doesn't exist, FALSE if the key isn't a hash
     */
    public function hDel(string $key, string ...$hash_keys)
    {
        return $this->cur->hDel($key, ...$hash_keys);
    }

    /**
     * Verify if the specified member exists in a key
     *
     * @param string $key
     * @param string $member_hash_key
     * @return boolean If the member exists in the hash table, return TRUE, otherwise return FALSE
     */
    public function hExists(string $key, string $member_hash_key)
    {
        return $this->cur->hExists($key, $member_hash_key);
    }

    /**
     * Gets a value from the hash stored at key. If the hash table doesn't exist, or the key doesn't exist, FALSE is returned
     *
     * @param string $key
     * @param string $hash_key
     * @return string|boolean  The value if the command executed successfully, FALSE in case of failure
     */
    public function hGet(string $key, string $hash_key)
    {
        return $this->cur->hGet($key, $hash_key);
    }

    /**
     * Returns the whole hash, as an array of strings indexed by strings
     *
     * @param string $key
     * @return array    An array of elements, the contents of the hash
     */
    public function hGetAll(string $key)
    {
        return $this->cur->hGetAll($key);
    }

    /**
     * Increments the value of a member from a hash by a given amount
     *
     * @param string $key
     * @param string $member_key
     * @param int $val
     * @return int the new value
     */
    public function hIncrBy(string $key, string $member_key, int $val)
    {
        return $this->cur->hIncrBy($key, $member_key, $val);
    }

    /**
     * Increments the value of a member from a hash by a given amount
     *
     * @param string $key
     * @param string $member_key
     * @param float $val
     * @return float the new value
     */
    public function hIncrByFloat(string $key, string $member_key, float $val)
    {
        return $this->cur->hIncrByFloat($key, $member_key, $val);
    }

    /**
     * Returns the keys in a hash, as an array of strings
     *
     * @param string $key
     * @return array An array of elements, the keys of the hash. This works like PHP's array_keys()
     */
    public function hKeys(string $key)
    {
        return $this->cur->hKeys($key);
    }

    /**
     * Returns the length of a hash, in number of items
     *
     * @param string $key
     * @return int the number of items in a hash, FALSE if the key doesn't exist or isn't a hash
     */
    public function hLen(string $key)
    {
        return $this->cur->hLen($key);
    }

    /**
     * Retrieve the values associated to the specified fields in the hash
     *
     * @param string $key
     * @param array $member_keys
     * @return array Array An array of elements, the values of the specified fields in the hash, with the hash keys as array keys
     */
    public function hMget(string $key, array $member_keys)
    {
        return $this->cur->hMget($key, $member_keys);
    }

    /**
     * Fills in a whole hash. Non-string values are converted to string,
     * using the standard (string) cast. NULL values are stored as empty strings
     *
     * @param string $key
     * @param array $key_values     key → value array
     * @return boolean
     */
    public function hMset(string $key, array $key_values)
    {
        return $this->cur->hMset($key, $key_values);
    }

    /**
     * Adds a value to the hash stored at key
     *
     * @param string $key
     * @param string $hash_key
     * @param string $val
     * @return int 1 if value didn't exist and was added successfully,
     *              0 if the value was already present and was replaced, FALSE if there was an error
     */
    public function hSet(string $key, string $hash_key, string $val)
    {
        return $this->cur->hSet($key, $hash_key, $val);
    }

    /**
     * Adds a value to the hash stored at key only if this field isn't already in the hash
     *
     * @param string $key
     * @param string $hash_key
     * @param string $val
     * @return boolean TRUE if the field was set, FALSE if it was already present
     */
    public function hSetNx(string $key, string $hash_key, string $val)
    {
        return $this->cur->hSetNx($key, $hash_key, $val);
    }

    /**
     * Returns the values in a hash, as an array of strings
     *
     * @param string $key
     * @return array An array of elements, the values of the hash. This works like PHP's array_values()
     */
    public function hVals(string $key)
    {
        return $this->cur->hVals($key);
    }

    /**
     * Get the string length of the value associated with field in the hash stored at key
     *
     * @param string $key
     * @param string $field
     * @return int the string length of the value associated with field,
     *              or zero when field is not present in the hash or key does not exist at all
     */
    public function hStrLen(string $key, string $field)
    {
        return $this->cur->hStrLen($key, $field);
    }

    /**
     * Scan a HASH value for members, with an optional pattern and count
     *
     * @param string $str_key
     * @param int $i_iterator
     * @param string $str_pattern
     * @param int $i_count
     * @return array    An array of members that match our pattern
     */
    public function hscan(string $str_key, int &$i_iterator, string $str_pattern, int $i_count)
    {
        return $this->cur->hscan($str_key, $i_iterator, $str_pattern, $i_count);
    }

    /**
     * Increment the number stored at key by one. If the second argument is filled,
     * it will be used as the integer value of the increment
     *
     * @param string $key
     * @return int the new value
     */
    public function incr(string $key)
    {
        return $this->cur->incr($key);
    }

    /**
     * Increment the number stored at key by one. If the second argument is filled,
     * it will be used as the integer value of the increment
     *
     * @param string $key
     * @param int $val
     * @return int  the new value
     */
    public function incrBy(string $key, int $val)
    {
        return $this->cur->incrBy($key, $val);
    }

    /**
     * Increment the key with floating point precision
     *
     * @return float    the new value
     */
    public function incrByFloat(string $key, float $val)
    {
        return $this->cur->incrByFloat($key, $val);
    }

    /**
     * Get information and statistics about the server
     * Returns an associative array that provides information about the server. Passing no arguments to INFO will call the standard REDIS INFO command, which returns information such as the following:

     * redis_version
     * arch_bits
     * uptime_in_seconds
     * uptime_in_days
     * connected_clients
     * connected_slaves
     * used_memory
     * changes_since_last_save
     * bgsave_in_progress
     * last_save_time
     * total_connections_received
     * total_commands_processed
     * role

     * You can pass a variety of options to INFO (https://redis.io/commands/info), which will modify what is returned.
     *
     * @param string $info  The option to provide redis (e.g. "COMMANDSTATS", "CPU")
     * @return type
     */
    public function info(string $info = '')
    {
        return $this->cur->info($info);
    }

    /**
     *  A method to determine if a phpredis object thinks it's connected to a server
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->cur->isConnected();
    }

    /**
     * Returns the keys that match a certain pattern
     *
     * @param string $key   pattern, using '*' as a wildcard
     * @return string[] Array of STRING: The keys that match a certain pattern
     */
    public function keys(string $key)
    {
        return $this->cur->keys($key);
    }

    /**
     * Return the specified element of the list stored at the specified key
     *
     * 0 the first element, 1 the second ...
     * -1 the last element, -2 the penultimate ...
     *
     * Note: lGet is an alias for lIndex and will be removed in future versions of phpredis.
     *
     * @param string $key
     * @param int $index
     * @return string|boolean  the element at this index, FALSE if the key identifies a non-string data type,
     *                           or no value corresponds to this index in the list Key
     */
    public function lGet(string $key, int $index)
    {
        return $this->cur->lGet($key, $index);
    }

    /**
     * Insert value in the list before or after the pivot value
     *
     * The parameter options specify the position of the insert (before or after).
     * If the list didn't exists, or the pivot didn't exists, the value is not inserted
     *
     * @param string $key
     * @param int $position     \Redis::BEFORE | \Redis::AFTER
     * @param string $pivot
     * @param string $val
     * @return int The number of the elements in the list, -1 if the pivot didn't exists
     */
    public function lInsert(string $key, int $position, string $pivot, string $val)
    {
        return $this->cur->lInsert($key, $position, $pivot, $val);
    }

    /**
     * Returns the size of a list identified by Key
     *
     * If the list didn't exist or is empty, the command returns 0.
     * If the data type identified by Key is not a list, the command return FALSE
     *
     * @param string $key
     * @return int|boolean The size of the list identified by Key exists, FALSE if the data type identified by Key is not list
     */
    public function lLen(string $key)
    {
        return $this->cur->lLen($key);
    }

    /**
     * Return and remove the first element of the list
     *
     * @param string $key
     * @return string|boolean   return element if command executed successfully, FALSE in case of failure (empty list)
     */
    public function lPop(string $key)
    {
        return $this->cur->lPop($key);
    }

    /**
     * Adds the string value to the head (left) of the list. Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned
     *
     * @param string $key
     * @param string $val
     * @return int|boolean The new length of the list in case of success, FALSE in case of Failure
     */
    public function lPush(string $key, string $val)
    {
        return $this->cur->lPush($key, $val);
    }

    /**
     * Adds the string value to the head (left) of the list if the list exists
     *
     * @param string $key
     * @param string $val
     * @return int|boolean The new length of the list in case of success, FALSE in case of Failure
     */
    public function lPushx(string $key, string $val)
    {
        return $this->cur->lPushx($key, $val);
    }

    /**
     * Removes the first count occurrences of the value element from the list. If count is zero,
     * all the matching elements are removed. If count is negative, elements are removed from tail to head
     *
     * Note: The argument order is not the same as in the Redis documentation. This difference is kept for compatibility reasons
     *
     * Note: lRemove is an alias for lRem and will be removed in future versions of phpredis
     *
     * @param string $key
     * @param string $val
     * @param int $count
     * @return int|boolean the number of elements to remove, FALSE if the value identified by key is not a list
     */
    public function lRemove(string $key, string $val, int $count = 1)
    {
        return $this->cur->lRemove($key, $val, $count);
    }

    /**
     * Set the list at index with the new value
     *
     * @param string $key
     * @param int $index
     * @param string $val
     * @return boolean TRUE if the new value was set. FALSE if the index is out of range, or data type identified by key is not a list
     */
    public function lSet(string $key, int $index, string $val)
    {
        return $this->cur->lSet($key, $index, $val);
    }

    /**
     * Returns the timestamp of the last disk save
     *
     * @return int timestamp
     */
    public function lastSave()
    {
        return $this->cur->lastSave();
    }

    /**
     * Reset the stats returned by info method
     * These are the counters that are reset:
     *      Keyspace hits
     *      Keyspace misses
     *      Number of commands processed
     *      Number of connections received
     *      Number of expired keys
     *
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function resetStat()
    {
        return $this->cur->resetStat();
    }

    /**
     * Return the specified element of the list stored at the specified key
     *
     * 0 the first element, 1 the second ...
     * -1 the last element, -2 the penultimate ...
     *
     * @param string $key
     * @param int $index
     * @return string|boolean  the element at this index, FALSE if the key identifies a non-string data type,
     *                           or no value corresponds to this index in the list Key
     */
    public function lindex(string $key, int $index)
    {
        return $this->cur->lindex($key, $index);
    }

    /**
     * Trims an existing list so that it will contain only a specified range of elements
     *
     * Note: listTrim is an alias for lTrim and will be removed in future versions of phpredis
     *
     * @param string $key
     * @param int $start
     * @param int $stop
     * @return array|boolean an array element of trim, or FALSE if the key identify a non-list value
     */
    public function listTrim(string $key, int $start, int $stop)
    {
        return $this->cur->listTrim($key, $start, $stop);
    }

    /**
     * Returns the specified elements of the list stored at the specified key in the range [start, end].
     * start and stop are interpreted as indices:
     *
     *  0 the first element, 1 the second ...
     *  -1 the last element, -2 the penultimate ...
     *
     * @param string $key
     * @param int $start
     * @param int $end
     * @return array containing the values in specified range
     */
    public function lrange(string $key, int $start, int $end)
    {
        return $this->cur->lrange($key, $start, $end);
    }

    /**
     * Removes the first count occurrences of the value element from the list. If count is zero,
     * all the matching elements are removed. If count is negative, elements are removed from tail to head
     *
     * Note: The argument order is not the same as in the Redis documentation. This difference is kept for compatibility reasons
     *
     * @param string $key
     * @param string $val
     * @param int $count
     * @return int|boolean the number of elements to remove, FALSE if the value identified by key is not a list
     */
    public function lrem(string $key, string $val, int $count = 1)
    {
        return $this->cur->lrem($key, $val, $count);
    }

    /**
     * Trims an existing list so that it will contain only a specified range of elements
     *
     * @param string $key
     * @param int $start
     * @param int $stop
     * @return array|boolean an array element of trim, or FALSE if the key identify a non-list value
     */
    public function ltrim(string $key, int $start, int $stop)
    {
        return $this->cur->ltrim($key, $start, $stop);
    }

    /**
     * Get the values of all the specified keys. If one or more keys don't exist,
     * the array will contain FALSE at the position of the key
     *
     * @param array $keys Array containing the list of the keys
     * @return array Array containing the values related to keys in argument
     */
    public function mGet(array $keys)
    {
        return $this->cur->mGet($keys);
    }

    /**
     * Migrates a key to a different Redis instance
     *
     * Note:: Redis introduced migrating multiple keys in 3.0.6, so you must have at least that version in order to call migrate with an array of keys
     *
     * @param string $host          The destination host
     * @param int $port             The TCP port to connect to
     * @param string|array $keys    string or array
     * @param int $db               The target DB
     * @param int $timeout          The maximum amount of time given to this transfer
     * @param bool $copy            optional. Should we send the COPY flag to redis
     * @param bool $replace         optional. Should we send the REPLACE flag to redis
     */
    public function migrate(string $host, int $port, $keys, int $db, int $timeout, bool $copy = true, bool $replace = true)
    {
        return $this->cur->migrate($host, $port, $keys, $db, $timeout, $copy, $replace);
    }

    /**
     * Moves a key to a different database
     *
     * @param string $key
     * @param int $dbindex
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function move(string $key, int $dbindex)
    {
        return $this->cur->move($key, $dbindex);
    }

    /**
     * Sets multiple key-value pairs in one atomic command.
     * MSETNX only returns TRUE if all the keys were set (see SETNX).
     *
     * @param array $keyAndVal  Pairs: [key => value, ...]
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function mset(array $keyAndVal)
    {
        return $this->cur->mset($keyAndVal);
    }

    /**
     * Sets multiple key-value pairs in one atomic command.
     * MSETNX only returns TRUE if all the keys were set (see SETNX).
     *
     * @param array $keyAndVal  Pairs: [key => value, ...]
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function msetnx(array $keyAndVal)
    {
        return $this->cur->msetnx($keyAndVal);
    }

    /**
     * Watches a key for modifications by another client
     *
     * If the key is modified between WATCH and EXEC,
     * the MULTI/EXEC transaction will fail (return FALSE).
     * unwatch cancels all the watching of all keys by this client
     *
     * @param string $key string for one key or array for a list of keys
     */
    public function watch(string $key)
    {
        $this->cur->watch($key);
    }

    /**
     * Watches a key for modifications by another client
     *
     * If the key is modified between WATCH and EXEC,
     * the MULTI/EXEC transaction will fail (return FALSE).
     * unwatch cancels all the watching of all keys by this client
     *
     * @param string $key string for one key or array for a list of keys
     */
    public function unwatch(string $key)
    {
        return $this->cur->unwatch($key);
    }

    /**
     * Enter and exit transactional mode
     *
     * Redis::MULTI or Redis::PIPELINE. Defaults to Redis::MULTI.
     * A Redis::MULTI block of commands runs as a single transaction;
     * a Redis::PIPELINE block is simply transmitted faster to the server,
     * but without any guarantee of atomicity. discard cancels a transaction
     *
     * @return \Redis returns the Redis instance and enters multi-mode. Once in multi-mode,
     *              all subsequent method calls return the same object until exec() is called
     */
    public function multi($mode = \Redis::MULTI)
    {
        return $this->cur->multi($mode);
    }

    /**
     * exec an transactional
     *
     * @return mixed
     */
    public function exec()
    {
        return $this->cur->exec();
    }

    /**
     * cancels a transaction
     */
    public function discard()
    {
        $this->cur->discard();
    }

    /**
     * Describes the object pointed to by a key
     *
     * @param string $info  The information to retrieve (string) and the key (string). Info can be one of the following:
     *                      "encoding"、 "refcount"、 "idletime"
     * @param string $key
     * @return mixed    STRING for "encoding", LONG for "refcount" and "idletime", FALSE if the key doesn't exist
     */
    public function object(string $info, string $key)
    {
        return $this->cur->object($info, $key);
    }

    /**
     * Remove the expiration timer from a key
     *
     * @param string $key
     * @return boolean  if a timeout was removed, FALSE if the key didn’t exist or didn’t have an expiration timer
     */
    public function persist(string $key)
    {
        return $this->cur->persist($key);
    }

    /**
     * Sets an expiration date (a timeout) on an item. pexpire requires a TTL in milliseconds
     *
     * @param string $key   The key that will disappear
     * @param int $ttl      The key's remaining Time To Live, in milliseconds
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function pexpire(string $key, int $ttl)
    {
        return $this->cur->pexpire($key, $ttl);
    }
/**
     * Sets an expiration date (a timestamp) on an item. pexpireAt requires a timestamp in milliseconds
     *
     * @param string $key       The key that will disappear
     * @param int $timestamp    Unix timestamp. The key's date of death, in milliseconds from Epoch time
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function pexpireAt(string $key, int $timestamp)
    {
        return $this->cur->pexpireAt($key, $timestamp);
    }

    /**
     * Set the string value in argument as value of the key, with a time to live. PSETEX uses a TTL in milliseconds
     *
     * @param string $key
     * @param int $ttl
     * @param string $val
     * @return boolean TRUE if the command is successful
     */
    public function pSetEx(string $key, int $ttl, string $val)
    {
        return $this->cur->pSetEx($key, $ttl, $val);
    }

    public function psubscribe()
    {
        return $this->cur->psubscribe();
    }

    /**
     * Returns the time to live left for a given key in seconds (ttl), or milliseconds (pttl)
     *
     * @param type $key
     * @return int The time to live in seconds. If the key has no ttl, -1 will be returned, and -2 if the key doesn't exist
     */
    public function pttl($key)
    {
        return $this->cur->pttl($key);
    }

    /**
     * Publish messages to channels. Warning: this function will probably change in the future
     *
     * @param string $channel
     * @param string $message
     */
    public function publish(string $channel, string $message)
    {
        $this->cur->publish($channel, $message);
    }

    /**
     * A command allowing you to get information on the Redis pub/sub system
     *
     * @param string $keyword           String, which can be: "channels", "numsub", or "numpat"
     * @param string|array $argument     Optional, variant. For the "channels" subcommand, you can pass a string pattern. For "numsub" an array of channel names
     * @return mixed CHANNELS: Returns an array where the members are the matching channels.<br>
     *              NUMSUB: Returns a key/value array where the keys are channel names and values are their counts.<br>
     *              NUMPAT: Integer return containing the number active pattern subscriptions<br>
     */
    public function pubsub(string $keyword, $argument = null)
    {
        return $this->cur->pubsub($keyword, $argument);
    }

    public function punsubscribe()
    {
        return $this->cur->punsubscribe();
    }

    /**
     * Returns and removes the last element of the list
     *
     * @param string $key
     * @return string|boolean the pop element if command executed successfully, FALSE in case of failure (empty list)
     */
    public function rPop(string $key)
    {
        return $this->cur->rPop($key);
    }

    /**
     * Adds the string value to the tail (right) of the list.
     * Creates the list if the key didn't exist. If the key exists and is not a list, FALSE is returned
     *
     * @param string $key
     * @param string $val
     * @return int|boolean The new length of the list in case of success, FALSE in case of Failure
     */
    public function rPush(string $key, string $val)
    {
        return $this->cur->rPush($key, $val);
    }

    /**
     * Adds the string value to the tail (right) of the list if the list exists. FALSE in case of Failure
     *
     * @param string $key
     * @param string $val
     * @return int|boolean The new length of the list in case of success, FALSE in case of Failure
     */
    public function rPushx(string $key, string $val)
    {
        return $this->cur->rPushx($key, $val);
    }

    /**
     * Returns a random key
     *
     * @return string an existing key in redis
     */
    public function randomKey()
    {
        return $this->cur->randomKey();
    }

    /**
     * A method to execute any arbitrary command against the a Redis server
     *
     * Returns: true<br>
     * $redis->rawCommand("set", "foo", "bar");
     *
     * Returns: "bar"<br>
     * $redis->rawCommand("get", "foo");
     *
     * Returns: 3<br>
     * $redis->rawCommand("rpush", "mylist", "one", 2, 3.5));
     *
     * Returns: ["one", "2", "3.5000000000000000"]<br>
     * $redis->rawCommand("lrange", "mylist", 0, -1);
     *
     * @param mixed $params This method is variadic and takes a dynamic number of arguments of various
     *                  types (string, long, double), but must be passed at least one argument (the command keyword itself)
     * @return mixed The return value can be various types depending on what the server itself returns.
     *              No post processing is done to the returned value and must be handled by the client code
     */
    public function rawcommand(...$params)
    {
        return $this->cur->rawcommand(...$params);
    }

    /**
     * Renames a key
     *
     * @param string $srcKey
     * @param string $dstKey
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function rename(string $srcKey, string $dstKey)
    {
        return $this->cur->rename($srcKey, $dstKey);
    }

    /**
     * Same as rename, but will not replace a key if the destination already exists. This is the same behaviour as setNx
     *
     * @param string $srcKey
     * @param string $dstKey
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function renameNx(string $srcKey, string $dstKey)
    {
        return $this->cur->renameNx($srcKey, $dstKey);
    }

    /**
     * Restore a key from the result of a DUMP operation
     *
     * @param string $key       key name
     * @param int $ttl          How long the key should live (if zero, no expire will be set on the key)
     * @param string $val       The Redis encoded key value (from DUMP)
     * @return mixed
     */
    public function restore(string $key, int $ttl, string $val)
    {
        return $this->cur->restore($key, $ttl, $val);
    }

    /**
     * Pops a value from the tail of a list, and pushes it to the front of another list. Also return this value. (redis >= 1.1)
     *
     * @param string $srckey
     * @param string $dstkey
     * @return string|boolean The element that was moved in case of success, FALSE in case of failure
     */
    public function rpoplpush(string $srckey, string $dstkey)
    {
        return $this->cur->rpoplpush($srckey, $dstkey);
    }

    /**
     * Adds a value to the set value stored at key. If this value is already in the set, FALSE is returned
     *
     * @param string $key
     * @param string $val
     * @return int the number of elements added to the set
     */
    public function sAdd(string $key, string $val)
    {
        return $this->cur->sAdd($key, $val);
    }

    /**
     * Checks if value is a member of the set stored at the key key
     *
     * Note: sContains is an alias for sIsMember and will be removed in future versions of phpredis
     *
     * @param string $key
     * @param string $val
     * @return boolean TRUE if value is a member of the set at key key, FALSE otherwise
     */
    public function sContains(string $key, string $val)
    {
        return $this->cur->sContains($key, $val);
    }

    /**
     * Performs the difference between N sets and returns it
     *
     * @param type $keys    Any number of keys corresponding to sets in redis
     * @return array        Array of strings: The difference of the first set will all the others
     */
    public function sDiff(string ...$keys)
    {
        return $this->cur->sDiff(...$keys);
    }

    /**
     * Performs the same action as sDiff, but stores the result in the first key
     *
     * @param string $dstkey    the key to store the diff into
     * @param string $keys      Any number of keys corresponding to sets in redis
     * @return int|boolean  The cardinality of the resulting set, or FALSE in case of a missing key
     */
    public function sDiffStore(string $dstkey, string ...$keys)
    {
        return $this->cur->sDiffStore($dstkey, ...$keys);
    }

    /**
     * Returns the contents of a set
     *
     * The order is random and corresponds to redis' own internal representation of the set structure
     *
     * @param string $key
     * @return array An array of elements, the contents of the set
     */
    public function sGetMembers(string $key)
    {
        return $this->cur->sGetMembers($key);
    }

    /**
     * Returns the members of a set resulting from the intersection of all the sets held at the specified keys
     *
     * If just a single key is specified, then this command produces the members of this set.
     * If one of the keys is missing, FALSE is returned
     *
     * @param string $keys
     * @return array|boolean contain the result of the intersection between those keys.
     *               If the intersection between the different sets is empty, the return value will be empty array
     */
    public function sInter(string ...$keys)
    {
        return $this->cur->sInter(...$keys);
    }

    /**
     * Performs a sInter command and stores the result in a new set
     *
     * @param string $dstkey    the key to store the diff into
     * @param string $keys      key1, key2... keyN. key1..keyN are intersected as in sInter
     * @return int|boolean The cardinality of the resulting set, or FALSE in case of a missing key
     */
    public function sInterStore(string $dstkey, string ...$keys)
    {
        return $this->cur->sInterStore($dstkey, ...$keys);
    }

    /**
     * Returns the contents of a set
     *
     * The order is random and corresponds to redis' own internal representation of the set structure
     *
     * @param string $key
     * @return array An array of elements, the contents of the set
     */
    public function sMembers(string $key)
    {
        return $this->cur->sMembers($key);
    }

    /**
     * Moves the specified member from the set at srcKey to the set at dstKey
     *
     * @param string $srckey
     * @param string $dstkey
     * @param string $member
     * @return boolean If the operation is successful, return TRUE. If the srcKey and/or dstKey didn't exist, and/or the member didn't exist in srcKey, FALSE is returned
     */
    public function sMove(string $srckey, string $dstkey, string $member)
    {
        return $this->cur->sMove($srckey, $dstkey, $member);
    }

    /**
     * Removes and returns a random element from the set value at Key
     *
     * @param string $key
     * @param int $count
     * @return string|array|boolean return "popped" value without $count, or array with $count, or FALSE if set identified by key is empty or doesn't exist
     */
    public function sPop(string $key, int $count = 1)
    {
        return $count == 1 ? $this->cur->sPop($key) : $this->cur->sPop($key, $count);
    }

    /**
     * Returns a random element from the set value at Key, without removing it
     *
     * @param string $key
     * @param int $count
     * @return string|array|boolean If no count is provided, a random String value from the set will be returned.
     *                              If a count is provided, an array of values from the set will be returned.
     *                              Read about the different ways to use the count here: SRANDMEMBER,
     *                              FALSE if set identified by key is empty or doesn't exist
     */
    public function sRandMember(string $key, int $count = 1)
    {
        return $this->cur->sRandMember($key, $count);
    }

    /**
     * Performs the union between N sets and returns it
     *
     * @param type $keys    Any number of keys corresponding to sets in redis
     * @return array The union of all these sets
     */
    public function sUnion(string ...$keys)
    {
        return $this->cur->sUnion($keys);
    }

    /**
     * Performs the same action as sUnion, but stores the result in the first key
     *
     * @param string $dstkey
     * @param string $keys
     * @return int|boolean The cardinality of the resulting set, or FALSE in case of a missing key
     */
    public function sUnionStore(string $dstkey, string ...$keys)
    {
        return $this->cur->sUnionStore($dstkey, $keys);
    }

    /**
     * Synchronously save the dataset to disk (wait to complete)
     *
     * @return boolean TRUE in case of success, FALSE in case of failure.
     *                  If a save is already running, this command will fail and return FALSE
     */
    public function save()
    {
        return $this->cur->save();
    }

    /**
     * Scan the keyspace for keys
     *
     * @param int $i_iterator       LONG (reference): Iterator, initialized to NULL STRING
     * @param string $str_pattern   Optional: Pattern to match LONG
     * @param int $i_count          Optional: Count of keys per iteration (only a suggestion to Redis)
     * @return boolean|array Array, boolean: This function will return an array of keys or FALSE if Redis returned zero keys
     */
    public function scan(&$i_iterator, $str_pattern = '', $i_count = 0)
    {
        return $str_pattern ? $this->cur->scan($i_iterator, $str_pattern, $i_count) : $this->cur->scan($i_iterator);
    }

    /**
     * Returns the cardinality of the set identified by key
     *
     * @param string $key
     * @return int the cardinality of the set identified by key, 0 if the set doesn't exist
     */
    public function scard(string $key)
    {
        return $this->cur->scard($key);
    }

    /**
     * Set the string value in argument as value of the key. If you're using Redis >= 2.6.12,<br>
     * you can pass extended options as explained below
     *      Will redirect, and actually make an SETEX call:<br>
     *      $redis->set('key','value', 10);<br>
     *
     *      Will set the key, if it doesn't exist, with a ttl of 10 seconds:<br>
     *      $redis->set('key', 'value', ['nx', 'ex'=>10]);<br>
     *
     *      Will set a key, if it does exist, with a ttl of 1000 miliseconds:<br>
     *      $redis->set('key', 'value', ['xx', 'px'=>1000]);<br>
     *
     * @param string $key
     * @param string $val
     * @param int|array $options    Timeout or Options Array (optional). If you pass an integer, phpredis will redirect to SETEX,
     *                              and will try to use Redis >= 2.6.12 extended options if you pass an array with valid values
     * @return boolean  TRUE if the command is successful
     */
    public function set(string $key, string $val, $options = null)
    {
        return null === $options ? $this->cur->set($key, $val) : $this->cur->set($key, $val, $options);
    }

    /**
     * Changes a single bit of a string
     *
     * @param string $key
     * @param int $offset
     * @param int $val      int (1 or 0)
     * @return int 0 or 1, the value of the bit before it was set
     */
    public function setBit(string $key, int $offset, int $val)
    {
        return $this->cur->setBit($key, $offset, $val);
    }

    /**
     * Changes a substring of a larger string
     *
     * @param string $key
     * @param int $offset
     * @param string $val
     * @return string the length of the string after it was modified
     */
    public function setRange(string $key, int $offset, string $val)
    {
        return $this->cur->setRange($key, $offset, $val);
    }

    /**
     * Set the string value in argument as value of the key, with a time to live. PSETEX uses a TTL in milliseconds
     *
     * @param string $key
     * @param int $ttl
     * @param string $val
     * @return boolean TRUE if the command is successful
     */
    public function setex(string $key, int $ttl, string $val)
    {
        return $this->cur->setex($key, $ttl, $val);
    }

    /**
     * Set the string value in argument as value of the key if the key doesn't already exist in the database
     *
     * @param string $key
     * @param string $val
     * @return boolean TRUE in case of success, FALSE in case of failure
     */
    public function setnx(string $key, string $val)
    {
        return $this->cur->setnx($key, $val);
    }

    /**
     * Checks if value is a member of the set stored at the key key
     *
     * @param string $key
     * @param string $val
     * @return boolean TRUE if value is a member of the set at key key, FALSE otherwise
     */
    public function sismember(string $key, string $val)
    {
        return $this->cur->sismember($key, $val);
    }

    /**
     * Changes the slave status
     * Either host (string) and port (int), or no parameter to stop being a slave
     *
     * @param string $host
     * @param int $port
     * @return boolean  TRUE in case of success, FALSE in case of failure
     */
    public function slaveOf(string $host = '', int $port = 0)
    {
        return $host && $port > 0 ? $this->cur->slaveOf($host, $port) : $this->cur->slaveOf();
    }

    /**
     * Access the Redis slowLog
     *
     * @param string $operation This can be either GET, LEN, or RESET
     * @param int $length   optional: If executing a SLOWLOG GET command, you can pass an optional length
     * @return mixed The return value of SLOWLOG will depend on which operation was performed.
     *              SLOWLOG GET: Array of slowLog entries, as provided by Redis SLOGLOG LEN: Integer,
     *              the length of the slowLog SLOWLOG RESET: Boolean, depending on success
     */
    public function slowLog(string $operation, int $length = 0)
    {
        return $length > 0 ? $this->cur->slowLog($operation, $length) : $this->cur->slowLog($operation);
    }

    /**
     * Sort the elements in a list, set or sorted set
     *
     * @param string $key
     * @param array $sortArr    Options: [key => value, ...] - optional, with the following keys and values: <br>
     *
     *          'by' => 'some_pattern_*',                                       <br>
     *          'limit' => [0, 1],                                              <br>
     *          'get' => 'some_other_pattern_*' or an array of patterns,        <br>
     *          'sort' => 'asc' or 'desc',                                      <br>
     *          'alpha' => TRUE,                                                <br>
     *          'store' => 'external-key'                                       <br>
     * @return array|int  An array of values, or a number corresponding to the number of elements stored if that was used
     */
    public function sort(string $key, array $sortArr)
    {
        return $this->cur->sort($key, $sortArr);
    }

    /**
     * Removes the specified member from the set value stored at key
     *
     * @param string $key
     * @param string $member
     * @return int The number of elements removed from the set
     */
    public function srem(string $key, string $member)
    {
        return $this->cur->srem($key, $member);
    }

    /**
     * Scan a set for members
     *
     * @param string $str_key
     * @param int $i_iterator
     * @param string $str_pattern
     * @param int $i_count
     * @return array|boolean return an array of keys or FALSE when we're done iterating
     */
    public function sscan(string $str_key, int &$i_iterator, string $str_pattern, int $i_count)
    {
        return $this->cur->sscan($str_key, $i_iterator, $str_pattern, $i_count);
    }

    /**
     * Get the length of a string value
     *
     * @param string $key
     * @return int
     */
    public function strlen(string $key)
    {
        return $this->cur->strlen($key);
    }

    /**
     * Subscribe to channels. Warning: this function will probably change in the future
     *
     * @param array $channels   an array of channels to subscribe to
     * @param string $callback  either a string or an Array($instance, 'method_name').
     *                  The callback function receives 3 parameters: the redis instance,
     *                  the channel name, and the message. return value: Mixed.
     *                  Any non-null return value in the callback will be returned to the caller
     */
    public function subscribe(array $channels, string $callback)
    {
        $this->cur->subscribe($channels, $callback);
    }

    /**
     * Return the current server time
     *
     * @return int If successful, the time will come back as an associative array with element zero being the unix timestamp,
     *              and element one being microseconds
     */
    public function time()
    {
        return $this->cur->time();
    }

    /**
     * Returns the time to live left for a given key in seconds (ttl), or milliseconds (pttl)
     *
     * @param type $key
     * @return int The time to live in seconds. If the key has no ttl, -1 will be returned, and -2 if the key doesn't exist
     */
    public function ttl($key)
    {
        return $this->cur->ttl($key);
    }

    /**
     * Returns the type of data pointed by a given key
     *
     * @param string $key
     * @return mixed    Depending on the type of the data pointed by the key, this method will return the following value:<br>
     *      string: Redis::REDIS_STRING         <br>
     *      set: Redis::REDIS_SET               <br>
     *      list: Redis::REDIS_LIST             <br>
     *      zset: Redis::REDIS_ZSET             <br>
     *      hash: Redis::REDIS_HASH             <br>
     *      other: Redis::REDIS_NOT_FOUND       <br>
     */
    public function type(string $key)
    {
        return $this->cur->type($key);
    }

    public function unsubscribe()
    {
        return $this->cur->unsubscribe();
    }

    /**
     * Add one or more members to a sorted set or update its score if it already exists
     *
     * @param string $key
     * @param float $score
     * @param string $val
     * @return int 1 if the element is added. 0 otherwise
     */
    public function zAdd(string $key, float $score, string $val)
    {
        return $this->cur->zAdd($key, $score, $val);
    }

    /**
     * Returns the cardinality of an ordered set
     *
     * @param string $key
     * @return int the set's cardinality
     */
    public function zCard(string $key)
    {
        return $this->cur->zCard($key);
    }

    /**
     * Returns the number of elements of the sorted set stored at the specified key which have scores in the range [start,end].
     * Adding a parenthesis before start or end excludes it from the range. +inf and -inf are also valid limits
     *
     * @param string $key
     * @param float $start
     * @param float $end
     * @return int the size of a corresponding zRangeByScore
     */
    public function zCount(string $key, float $start, float $end)
    {
        return $this->cur->zCount($key, $start, $end);
    }

    /**
     * Increments the score of a member from a sorted set by a given amount
     *
     * @param string $key
     * @param float $val
     * @param string $member
     * @return float the new value
     */
    public function zIncrBy(string $key, float $val, string $member)
    {
        return $this->cur->zIncrBy($key, $val, $member);
    }

    /**
     * Returns a range of elements from the ordered set stored at the specified key, with values in the range [start, end]
     *
     * Start and stop are interpreted as zero-based indices:    <br>
     * 0 the first element, 1 the second ...                    <br>
     * -1 the last element, -2 the penultimate ...              <br>
     *
     * @param string $key
     * @param int $start
     * @param int $end
     * @param bool $with_score
     * @return array containing the values in specified range
     */
    public function zRange(string $key, int $start, int $end, bool $with_score = false)
    {
        return $this->cur->zRange($key, $start, $end, $with_score);
    }

    /**
     * Returns a lexicographical range of members in a sorted set, assuming the members have the same score.
     * The min and max values are required to start with '(' (exclusive), '[' (inclusive),
     * or be exactly the values '-' (negative inf) or '+' (positive inf).
     * The command must be called with either three or five arguments or will return FALSE
     *
     * @param string $key   The ZSET you wish to run against
     * @param string $min   The minimum alphanumeric value you wish to get
     * @param string $max   The maximum alphanumeric value you wish to get
     * @param int $offset   Optional argument if you wish to start somewhere other than the first element
     * @param int $limit    Optional argument if you wish to limit the number of elements returned
     * @return array|boolean containing the values in the specified range
     */
    public function zRangeByLex(string $key, string $min, string $max, int $offset = 0, int $limit = 0)
    {
        return $offset > 0 && $limit > 0 ? $this->cur->zRangeByLex($key, $min, $max, $offset, $limit)
                : $this->cur->zRangeByLex($key, $min, $max);
    }

    /**
     * Returns the elements of the sorted set stored at the specified key which have scores in the range [start,end].
     * Adding a parenthesis before start or end excludes it from the range. +inf and -inf are also valid limits.
     * zRevRangeByScore returns the same items in reverse order, when the start and end parameters are swapped
     *
     * @param string $key
     * @param int $start
     * @param type $end
     * @param array $options    Two options are available: withscores => TRUE, and limit => [$offset, $count]
     * @return array containing the values in specified range
     */
    public function zRangeByScore(string $key, int $start, $end, array $options = [])
    {
        return $this->cur->zRangeByScore($key, $start, $end, $options);
    }

    /**
     * Returns the rank of a given member in the specified sorted set, starting at 0 for the item with the smallest score.
     * zRevRank starts at 0 for the item with the largest score
     *
     * @param string $key
     * @param string $member
     * @return int the member's index
     */
    public function zRank(string $key, string $member)
    {
        return $this->cur->zRank($key, $member);
    }

    /**
     * Delete one or more members from a sorted set
     *
     * @param string $key
     * @param string $members
     * @return int The number of members deleted
     */
    public function zRem(string $key, string ...$members)
    {
        return $this->cur->zRem($key, ...$members);
    }

    /**
     * Deletes the elements of the sorted set stored at the specified key which have rank in the range [start,end]
     *
     * @param string $key
     * @param int $start
     * @param int $end
     * @return int The number of values deleted from the sorted set
     */
    public function zRemRangeByRank(string $key, int $start, int $end)
    {
        return $this->cur->zRemRangeByRank($key, $start, $end);
    }

    /**
     * Deletes the elements of the sorted set stored at the specified key which have scores in the range [start,end]
     *
     * @param string $key
     * @param string|float $start
     * @param string|float $end
     * @return int The number of values deleted from the sorted set
     */
    public function zRemRangeByScore(string $key, $start, $end)
    {
        return $this->cur->zRemRangeByScore($key, $start, $end);
    }

    /**
     * Returns the elements of the sorted set stored at the specified key in the range [start, end] in reverse order.
     * start and stop are interpreted as zero-based indices:
     *
     * 0 the first element, 1 the second ...
     * -1 the last element, -2 the penultimate ...
     *
     * @param string $key
     * @param int $start
     * @param int $end
     * @param bool $with_score
     * @return array containing the values in specified range
     */
    public function zRevRange(string $key, int $start, int $end, bool $with_score = false)
    {
        return $this->cur->zRevRange($key, $start, $end, $with_score);
    }

    /**
     * Returns the elements of the sorted set stored at the specified key which have scores in the range [start,end].
     * Adding a parenthesis before start or end excludes it from the range. +inf and -inf are also valid limits.
     * zRevRangeByScore returns the same items in reverse order, when the start and end parameters are swapped
     *
     * @param string $key
     * @param int $start
     * @param type $end
     * @param array $options    Two options are available: withscores => TRUE, and limit => [$offset, $count]
     * @return array containing the values in specified range
     */
    public function zRevRangeByScore(string $key, int $start, $end, array $options = [])
    {
        return $this->cur->zRevRangeByScore($key, $start, $end, $options);
    }

    /**
     * Returns the rank of a given member in the specified sorted set, starting at 0 for the item with the smallest score.
     * zRevRank starts at 0 for the item with the largest score
     *
     * @param string $key
     * @param string $member
     * @return int the member's index
     */
    public function zRevRank(string $key, string $member)
    {
        return $this->cur->zRevRank($key, $member);
    }

    /**
     * Returns the score of a given member in the specified sorted set
     *
     * @param string $key
     * @param string $member
     * @return float
     */
    public function zScore(string $key, string $member)
    {
        return $this->cur->zScore($key, $member);
    }

    /**
     * Returns the cardinality of an ordered set
     *
     * @param string $key
     * @return int the set's cardinality
     */
    public function zSize(string $key)
    {
        return $this->cur->zSize($key);
    }

    /**
     * Creates an intersection of sorted sets given in second argument. The result of the union will be stored in the sorted set defined by the first argument.

     * The third optional argument defines weights to apply to the sorted sets in input.
     * In this case, the weights will be multiplied by the score of each element in the sorted set before
     * applying the aggregation. The forth argument defines the AGGREGATE option which specify how the
     * results of the union are aggregated
     *
     *
     * @param string $outkey
     * @param array $keys
     * @param array $weights
     * @param string $aggregate_function    Either "SUM", "MIN", or "MAX": defines the behaviour to use on duplicate entries during the zinterstore
     * @return int The number of values in the new sorted set
     */
    public function zinterstore(string $outkey, array $keys, array $weights = [], string $aggregate_function = '')
    {
        return $this->cur->zinterstore($outkey, $keys, $weights, $aggregate_function);
    }

    /**
     * Scan a sorted set for members, with optional pattern and count
     *
     * @param string $str_key       the set to scan
     * @param int $i_iterator       Long (reference), initialized to NULL
     * @param string $str_pattern   String (optional), the pattern to match
     * @param int $i_count          How many keys to return per iteration (Redis might return a different number)
     * @return array|boolean  return matching keys from Redis, or FALSE when iteration is complete
     */
    public function zscan(string $str_key, int &$i_iterator, string $str_pattern, int $i_count)
    {
        return $this->cur->zscan($str_key, $i_iterator, $str_pattern, $i_count);
    }

    /**
     * Creates an union of sorted sets given in second argument. The result of the union will be stored in the sorted set defined by the first argument
     *
     * The third optional argument defines weights to apply to the sorted sets in input.
     * In this case, the weights will be multiplied by the score of each element in the sorted
     * set before applying the aggregation. The forth argument defines the AGGREGATE option
     * which specify how the results of the union are aggregated
     *
     * @param string $outkey
     * @param array $keys
     * @param array $weights
     * @param string $aggregate_function    Either "SUM", "MIN", or "MAX": defines the behaviour to use on duplicate entries during the zunionstore
     * @return int The number of values in the new sorted set
     */
    public function zunionstore(string $outkey, array $keys, array $weights = [], string $aggregate_function = '')
    {
        return $this->cur->zunionstore($outkey, $keys, $weights, $aggregate_function);
    }

    /**
     * Can pop the highest or lowest scoring members from one ZSETs. There are two commands
     * (ZPOPMIN and ZPOPMAX for popping the lowest and highest scoring elements respectively.)
     *
     * @param array $keys
     * @param int $timeout
     * @return array Either an array with the key member and score of the higest or lowest element or an empty array if there is no element available
     */
    public function zPopMin(array $keys, int $timeout)
    {
        return $this->cur->zPopMin($keys, $timeout);
    }

    /**
     * Can pop the highest or lowest scoring members from one ZSETs. There are two commands
     * (ZPOPMIN and ZPOPMAX for popping the lowest and highest scoring elements respectively.)
     *
     * @param array $keys
     * @param int $timeout
     * @return array Either an array with the key member and score of the higest or lowest element or an empty array if there is no element available
     */
    public function zPopMax(array $keys, int $timeout)
    {
        return $this->cur->zPopMax($keys, $timeout);
    }

    /**
     * Block until Redis can pop the highest or lowest scoring members from one or more ZSETs.
     * There are two commands (BZPOPMIN and BZPOPMAX for popping the lowest and highest scoring elements respectively.)
     *
     * @param array $keys
     * @param int $timeout
     * @return array Either an array with the key member and score of the higest or lowest element or an empty array if the timeout was reached without an element to pop
     */
    public function bzPopMin(array $keys, int $timeout)
    {
        return $this->cur->bzPopMin($keys, $timeout);
    }

    /**
     * Block until Redis can pop the highest or lowest scoring members from one or more ZSETs.
     * There are two commands (BZPOPMIN and BZPOPMAX for popping the lowest and highest scoring elements respectively.)
     *
     * @param array $keys
     * @param int $timeout
     * @return array Either an array with the key member and score of the higest or lowest element or an empty array if the timeout was reached without an element to pop
     */
    public function bzPopMax(array $keys, int $timeout)
    {
        return $this->cur->bzPopMax($keys, $timeout);
    }

    /**
     *  Add one or more geospatial items to the specified key. This function must be called with at least one longitude, latitude, member triplet
     *
     * Prototype
     *
     * $redis->geoAdd($key, $longitude, $latitude, $member [, $longitude, $latitude, $member, ...]);
     *
     * @param string $key
     * @param type $pairparams
     * @return int The number of elements added to the geospatial key
     */
    public function geoAdd(string $key, ...$pairparams)
    {
        return $this->cur->geoAdd($key, ...$pairparams);
    }

    /**
     * Retrieve Geohash strings for one or more elements of a geospatial index
     *
     * @param string $key
     * @param string $members
     * @return string[] One or more Redis Geohash encoded strings
     */
    public function geoHash(string $key, string ...$members)
    {
        return $this->cur->geoHash($key, ...$members);
    }

    /**
     * Return longitude, latitude positions for each requested member
     *
     * @param string $key
     * @param string $members
     * @return array One or more longitude/latitude positions
     */
    public function geoPos(string $key, string ...$members)
    {
        return $this->cur->geoPos($key, $members);
    }

    /**
     * Return the distance between two members in a geospatial set. If units are passed it must be one of the following values:
     *
     * @param string $key
     * @param string $member1
     * @param string $member2
     * @param string $unit  'm' => Meters, 'km' => Kilometers, 'mi' => Miles, 'ft' => Feet
     * @return float  The distance between the two passed members in the units requested (meters by default)
     */
    public function geoDist(string $key, string $member1, string $member2, string $unit = 'm')
    {
        return $this->cur->geoDist($key, $member1, $member2, $unit);
    }

    /**
     * Return members of a set with geospatial information that are within the radius specified by the caller
     *
     * Note: It doesn't make sense to pass both ASC and DESC options but if both are passed the last one passed will be used.<br>
     * Note: When using STORE[DIST] in Redis Cluster, the store key must has to the same slot as the query key or you will get a CROSSLOT error.
     *
     * @param string $key
     * @param float $longitude
     * @param float $latitude
     * @param int $radius
     * @param string $unit
     * @param array $options    The georadius command can be called with various options that control how Redis returns results.
     *                          The following table describes the options phpredis supports. All options are case insensitive
     *
     *      -------------------------------------------------------------------------------------------------<br>
     *      |       key       |       value       |   description                                           |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |       COUNT     |      integer > 0  |   Limit how many results are returned                   |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |                 |      WITHCOORD    |   Return longitude and latitude of matching members     |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |                 |      WITHDIST     |   Return the distance from the center                   |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |                 |      WITHHASH     |   Return the raw geohash-encoded score                  |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |                 |      ASC          |   Sort results in ascending order                       |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |                 |      DESC         |   Sort results in descending order                      |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |       STORE     |      key          |   Store results in key                                  |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *      |       STOREDIST |      key          |   Store the results as distances in key                 |<br>
     *      -------------------------------------------------------------------------------------------------<br>
     *
     * @return mixed When no STORE option is passed, this function returns an array of results. If it is passed this function returns the number of stored entries
     */
    public function geoRadius(string $key, float $longitude, float $latitude, int $radius, string $unit, array $options = [])
    {
        return $this->cur->geoRadius($key, $longitude, $latitude, $radius, $unit, $options);
    }

    /**
     * This method is identical to geoRadius except that instead of passing a
     * longitude and latitude as the "source" you pass an existing member in the geospatial set
     *
     * @param string $key
     * @param string $member
     * @param int $radius
     * @param string $units
     * @param array $options
     * @return array  The zero or more entries that are close enough to the member given the distance and radius specified
     */
    public function geoRadiusByMember(string $key, string $member, int $radius, string $units , array $options)
    {
        return $this->cur->geoRadiusByMember($key, $member, $radius, $units, $options);
    }

    /**
     * Acknowledge one or more messages on behalf of a consumer group
     *
     * @param string $stream
     * @param string $group
     * @param array $arr_messages
     * @return int  The number of messages Redis reports as acknowledged
     */
    public function xAck(string $stream, string $group, array $arr_messages)
    {
        return $this->cur->xAck($stream, $group, $arr_messages);
    }

    /**
     *  Add a message to a stream
     *
     * @param string $key
     * @param string $id
     * @param array $message
     * @param int $maxlen
     * @param bool $approximate
     * @return string The added message ID
     */
    public function xAdd(string $key, string $id, array $message, int $maxlen = 0, bool $approximate = false)
    {
        return $this->cur->xAdd($key, $id, $message, $maxlen, $approximate);
    }

    /**
     * Claim ownership of one or more pending messages
     *
     * Options Array
     *      Note:  'TIME', and 'IDLE' are mutually exclusive <br>
     * $options = [ <br>
     *      'IDLE' => $value, // Set the idle time to $value ms  , <br>
     *      'TIME' => $value, // Set the idle time to now - $value <br>
     *      'RETRYCOUNT' => $value, // Update message retrycount to $value <br>
     *      'FORCE', // Claim the message(s) even if they're not pending anywhere <br>
     *      'JUSTID', // Instruct Redis to only return IDs <br>
     * ];
     *
     * @param string $key
     * @param string $group
     * @param string $consumer
     * @param int $min_idle_time
     * @param array $ids
     * @param array $options
     * @return array Either an array of message IDs along with corresponding data, or just an array of IDs (if the 'JUSTID' option was passed)
     */
    public function xClaim(string $key, string $group, string $consumer, int $min_idle_time, array $ids, array $options = [])
    {
        return $this->cur->xClaim($key, $group, $consumer, $min_idle_time, $ids, $options);
    }

    /**
     * Delete one or more messages from a stream
     *
     * @param string $key
     * @param array $ids
     * @return int The number of messages removed
     */
    public function xDel(string $key, array $ids)
    {
        return $this->cur->xDel($key, $ids);
    }

    /**
     * This command is used in order to create, destroy, or manage consumer groups
     *
     * $obj_redis->xGroup('HELP');<br>
     * $obj_redis->xGroup('CREATE', $str_key, $str_group, $str_msg_id, [$boo_mkstream]);<br>
     * $obj_redis->xGroup('SETID', $str_key, $str_group, $str_msg_id);<br>
     * $obj_redis->xGroup('DESTROY', $str_key, $str_group);<br>
     * $obj_redis->xGroup('DELCONSUMER', $str_key, $str_group, $str_consumer_name);<br>
     *
     * $obj_redis->xGroup('CREATE', 'mystream', 'mygroup', 0);<br>
     * $obj_redis->xGroup('CREATE', 'mystream', 'mygroup2', 0, true); // Create stream if non-existent. <br>
     * $obj_redis->xGroup('DESTROY', 'mystream', 'mygroup');<br>
     *
     * @param string $command
     * @param string $key
     * @param string $group
     * @param type $msg_or_consumer
     * @param bool $mkstream
     * @return mixed  This command returns different types depending on the specific XGROUP command executed
     */
    public function xGroup(string $command, string $key = '', string $group = '', $msg_or_consumer = '', bool $mkstream = false)
    {
        $res = null;
        switch (strtoupper($command)) {
            case 'HELP':
                $res = $this->cur->xGroup('HELP');
                break;
            case 'CREATE':
                $res = $this->cur->xGroup('CREATE', $key, $group, $msg_or_consumer, $mkstream);
                break;
            case 'SETID':
                $res = $this->cur->xGroup('SETID', $key, $group, $msg_or_consumer);
                break;
            case 'DESTROY':
                $res = $this->cur->xGroup('DESTROY', $key, $group);
                break;
            case 'DELCONSUMER':
                $res = $this->cur->xGroup('DELCONSUMER', $key, $group, $msg_or_consumer);
                break;
        }

        return $res;
    }

    /**
     * Get information about a stream or consumer groups
     *
     * Prototype:<br>
     *      $obj_redis->xInfo('CONSUMERS', $str_stream, $str_group);<br>
     *      $obj_redis->xInfo('GROUPS', $str_stream);<br>
     *      $obj_redis->xInfo('STREAM', $str_stream);<br>
     *      $obj_redis->xInfo('HELP');
     *
     * @param string $command
     * @param string $stream
     * @param string $group
     * @return mixed This command returns different types depending on which subcommand is used
     */
    public function xInfo(string $command, string $stream = '', string $group = '')
    {
        $res = null;
        switch (strtoupper($command)) {
            case 'HELP':
                $res = $this->cur->xInfo('HELP');
                break;
            case 'STREAM':
                $res = $this->cur->xInfo('STREAM', $stream);
                break;
            case 'GROUPS':
                $res = $this->cur->xInfo('GROUPS', $stream);
                break;
            case 'CONSUMERS':
                $res = $this->cur->xInfo('CONSUMERS', $stream, $group);
                break;

            default:
                break;
        }
        return $res;
    }

    /**
     * Get the length of a given stream
     *
     * @param string $stream
     * @return int The number of messages in the stream
     */
    public function xLen(string $stream)
    {
        return $this->cur->xLen($stream);
    }

    /**
     * Get information about pending messages in a given stream
     *
     * Examples

     * $obj_redis->xPending('mystream', 'mygroup');<br>
     * $obj_redis->xPending('mystream', 'mygroup', '-', '+', 1, 'consumer-1');<br>
     *
     * @param string $stream
     * @param string $group
     * @param string $start
     * @param string $end
     * @param int $count
     * @param string $consumer
     * @return array  Information about the pending messages, in various forms depending on the specific invocation of XPENDING
     */
    public function xPending(string $stream, string $group, string $start = '', string $end = '', int $count = 0, string $consumer = '')
    {
        return $start ? $this->cur->xPending($stream, $group, $start, $end, $count, $consumer)
                : $this->cur->xPending($stream, $group);
    }

    /**
     * Get a range of messages from a given stream
     *
     * Example

     * Get everything in this stream
     * $obj_redis->xRange('mystream', '-', '+');

     * Only the first two messages
     * $obj_redis->xRange('mystream', '-', '+', 2);
     *
     * @param string $stream
     * @param string $start
     * @param string $end
     * @param int $count
     * @return array  The messages in the stream within the requested range
     */
    public function xRange(string $stream, string $start, string $end, int $count = 0)
    {
        return $count ? $this->cur->xRange($stream, $start, $end, $count)
                : $this->cur->xRange($stream, $start, $end);
    }

    /**
     * Read data from one or more streams and only return IDs greater than sent in the command
     *
     * $obj_redis->xRead(['stream1' => '1535222584555-0', 'stream2' => '1535222584555-0']);
     *
     * Receive only new message ($ = last id) and wait for one new message unlimited time
     * $obj_redis->xRead(['stream1' => '$'], 1, 0);
     *
     * @param array $streams
     * @param int $count
     * @param int $block
     * @return array The messages in the stream newer than the IDs passed to Redis (if any)
     */
    public function xRead(array $streams, int $count = 0, int $block = 0)
    {
        return $count ? $this->cur->xRead($streams, $count, $block)
                : $this->cur->xRead($streams);
    }

    /**
     * This method is similar to xRead except that it supports reading messages for a specific consumer group
     *
     * Examples

     * Consume messages for 'mygroup', 'consumer1'<br>
     * $obj_redis->xReadGroup('mygroup', 'consumer1', ['s1' => 0, 's2' => 0]);<br>
     *
     * Consume messages for 'mygroup', 'consumer1' which were not consumed yet by the group<br>
     * $obj_redis->xReadGroup('mygroup', 'consumer1', ['s1' => '>', 's2' => '>']);<br>
     *
     * Read a single message as 'consumer2' wait for up to a second until a message arrives.<br>
     * $obj_redis->xReadGroup('mygroup', 'consumer2', ['s1' => 0, 's2' => 0], 1, 1000);<br>
     *
     * @param string $group
     * @param string $consumer
     * @param array $streams
     * @param int $count
     * @param int $block
     * @return array The messages delivered to this consumer group (if any)
     */
    public function xReadGroup(string $group, string $consumer, array $streams, int $count = 0, int $block = 0)
    {
        return $count ? $this->cur->xReadGroup($group, $consumer, $streams, $count, $block)
                : $this->cur->xReadGroup($group, $consumer, $streams);
    }

    /**
     * This is identical to xRange except the results come back in reverse order. Also note that Redis reverses the order of "start" and "end"
     *
     * Example
     *
     * $obj_redis->xRevRange('mystream', '+', '-');
     *
     * @param string $stream
     * @param string $end
     * @param string $start
     * @param int $count
     * @return array  The messages in the range specified
     */
    public function xRevRange(string $stream, string $end, string $start, int $count = 0)
    {
        return $this->cur->xRevRange($stream, $end, $start, $count?:null);
    }

    /**
     * Trim the stream length to a given maximum. If the "approximate" flag is pasesed,
     * Redis will use your size as a hint but only trim trees in whole nodes (this is more efficient)
     *
     * @param string $stream
     * @param int $max_len
     * @param bool $approximate
     * @return int The number of messages trimed from the stream
     */
    public function xTrim(string $stream, int $max_len, bool $approximate = false)
    {
        return $this->cur->xTrim($stream, $max_len, $approximate);
    }

    /**
     * A utility method to serialize values manually
     *
     * This method allows you to serialize a value with whatever serializer is configured,
     * manually. This can be useful for serialization/unserialization of data going in and
     * out of EVAL commands as phpredis can't automatically do this itself.
     * Note that if no serializer is set, phpredis will change Array values to 'Array', and Objects to 'Object'
     *
     * Examples
     *
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);<br>
     * $redis->_serialize("foo"); // returns "foo"<br>
     * $redis->_serialize([]); // Returns "Array"<br>
     * $redis->_serialize(new stdClass()); // Returns "Object"
     *
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);<br>
     * $redis->_serialize("foo"); // Returns 's:3:"foo";'
     *
     * @param mixed $value The value to be serialized
     * @return mixed
     */
    public function _serialize(string $value)
    {
        return $this->cur->_serialize($value);
    }

    /**
     * A utility method to unserialize data with whatever serializer is set up
     *
     * $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);<br>
     * $redis->_unserialize('a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}'); // Will return [1,2,3]
     *
     * @param string $value
     * @return mixed
     */
    public function _unserialize(string $value)
    {
        return $this->cur->_unserialize($value);
    }

    /**
     * Disconnects from the Redis instance.
     * Note: Closing a persistent connection requires PhpRedis >= 4.2.0
     *
     * @return boolean  TRUE on success, FALSE on failure.
     */
    public function close()
    {
        return $this->cur->close();
    }
}


/* ----------------------------------------------------------------------
 * 下面的类使数据库连接类支持读写分离
 * ----------------------------------------------------------------------
 */

/**
 * Description of Dbc
 *
 * @author wb
 */
class _dbm_
{
    /**
     * @var \Redis
     */
    private $writeableServer;

    /**
     * @var \Redis
     */
    private $readableServer;

    /**
     * @var \Nopis\Lib\Redis\_RedisCollection
     */
    private $dbCollection;

    /**
     * Constructor.
     *
     * @param array $dbconfig
     */
    public function __construct(array $dbconfig)
    {
        $this->dbCollection = new _RedisCollection($dbconfig);
    }

    /**
     * Get Redis connection of readable server
     *
     * @return \Redis
     */
    public function getReadableServer()
    {
        if (null === $this->readableServer)
            $this->readableServer = $this->dbCollection->getReadableServer();

        return $this->readableServer;
    }

    /**
     * Get Redis connection of writeable server
     *
     * @return \Redis
     */
    public function getWriteableServer()
    {
        if (null === $this->writeableServer)
            $this->writeableServer = $this->dbCollection->getWriteableServer();

        return $this->writeableServer;
    }
}

class _RedisCollection
{
    /**
     * @var boolean
     */
    protected $isAlone;

    /**
     * @var \Nopis\Lib\Redis\_RedisConnection
     */
    protected $aloneServer;

    /**
     * @var \Nopis\Lib\Redis\_RedisConnection[]
     */
    protected $writeServers = [];

    /**
     * @var \Nopis\Lib\Redis\_RedisConnection[]
     */
    protected $readServers = [];

    /**
     * @var int
     */
    protected $readSvrCount = 0;

    /**
     * @var int
     */
    protected $writeSvrCount = 0;

    /**
     * @var array
     */
    protected $dbconfig;

    public function __construct(array $dbconfig)
    {
        $this->dbconfig = $dbconfig;
        if ($this->isAlone()) {
            $this->aloneServer = new _RedisConnection($dbconfig);
        } else {
            foreach ($this->dbconfig['read'] as $read) {
                $this->readServers[] = new _RedisConnection($read);
            }

            foreach ($this->dbconfig['write'] as $write) {
                $this->writeServers[] = new _RedisConnection($write);
            }

            $this->readSvrCount  = count($this->readServers);
            $this->writeSvrCount = count($this->writeServers);
        }
    }

    public function isAlone()
    {
        if (null === $this->isAlone)
            $this->isAlone = empty($this->dbconfig['read']) && empty($this->dbconfig['write']);

        return $this->isAlone;
    }

    /**
     * Get Redis connection of readable server
     *
     * @return \Redis
     * @throws \Exception
     */
    public function getReadableServer()
    {
        $redis = null;
        if ($this->isAlone()) {
            $redis = $this->aloneServer->getConnection();
        } else {
            if ($this->readSvrCount == 1) {
                $redis = $this->readServers[0]->getConnection();
            } else {
                for ($i = 0; $i < $this->readSvrCount; $i++) {
                    $k = mt_rand(0, $this->readSvrCount - 1);
                    $redis = $this->readServers[$k]->getConnection();
                    if ($redis instanceof \Redis)
                        break;
                }
            }
        }

        if (null === $redis) {
            throw new \Exception('Unable to connect readable redis server');
        }

        return $redis;
    }

    /**
     * Get Redis connection of writeable server
     *
     * @return \Redis
     */
    public function getWriteableServer()
    {
        $redis = null;
        if ($this->isAlone()) {
            $redis = $this->aloneServer->getConnection();
        } else {
            if ($this->writeSvrCount == 1) {
                $redis = $this->writeServers[0]->getConnection();
            } else {
                for ($i = 0; $i < $this->writeSvrCount; $i++) {
                    $k = mt_rand(0, $this->writeSvrCount - 1);
                    $redis = $this->writeServers[$k]->getConnection();
                    if ($redis instanceof \Redis)
                        break;
                }
            }
        }

        if (null === $redis) {
            throw new \Exception('Unable to connect writeable redis server');
        }

        return $redis;
    }
}

class _RedisConnection
{
    /**
     * @var \Redis
     */
    private $redis;

    /* ----------------------------------------------------------
     * database config
     * ----------------------------------------------------------
     */
    private $host;
    private $port;
    private $pass;
    private $dbno;

    public function __construct(array $dbconfig)
    {
        foreach ($dbconfig as $key => $val) {
            $this->$key = $val;
        };
    }

    /**
     * Get Redis object of database connection
     *
     * @return \Redis
     */
    public function getConnection()
    {
        if (!$this->hasConnection()) {
            $this->connect($this->host, $this->port, $this->pass, $this->dbno);
        }

        return $this->redis;
    }

    /**
     * 检查是否已连接数据库
     *
     * @return boolean
     */
    protected function hasConnection()
    {
        return $this->redis instanceof \Redis;
    }

    /**
     * 连接数据库
     *
     * @param string $host      数据库主机地址
     * @param int    $port      数据库端口
     * @param string $pass    数据库密码
     */
    protected function connect(string $host, int $port, string $pass, $dbno = 0)
    {
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
        if ($pass) {
            $this->redis->auth($pass);
        }

        $this->redis->select($dbno >= 0 ? (int)$dbno : 0);
    }
}
