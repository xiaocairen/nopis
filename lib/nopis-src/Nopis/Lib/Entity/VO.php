<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Entity;

/**
 * Description of VO
 *
 * @author wangbin
 */
abstract class VO implements \IteratorAggregate
{
    /**
     * Construct object optionally with a set of properties
     *
     * Readonly properties values must be set using $properties as they are not writable anymore
     * after object has been created.
     *
     * @param array $properties
     */
    public function __construct( array $properties = array() )
    {
        foreach ( $properties as $property => $value )
        {
            $this->__isset($property) && $this->$property = $value;
        }
    }

    /**
     * Get property value
     *
     * @param string $property
     *
     * @return array
     */
    public function getPropertyValue($property)
    {
        return $this->__isset($property) ? $this->$property : null;
    }

    /**
     * Magic set function handling writes to private properties, no effected to public and protected
     *
     * @ignore This method is for internal use
     * @access private
     *
     * @throws \Nopis\Lib\Entity\PropertyNotFoundException When property does not exist
     * @throws \Nopis\Lib\Entity\PropertyReadOnlyException When property is readonly (protected)
     *
     * @param string $property Name of the property
     * @param string $value
     *
     * @return void
     */
    public function __set( $property, $value )
    {
        if ( property_exists( $this, $property ) )
        {
            throw new PropertyReadOnlyException( $property, get_class( $this ) );
        }
        throw new PropertyNotFoundException( $property, get_class( $this ) );
    }

    /**
     * Magic get function handling read to non public properties
     *
     * Returns value for all readonly (protected) properties.
     *
     * @ignore This method is for internal use
     * @access private
     *
     * @param string $property Name of the property
     *
     * @return mixed
     */
    public function __get( $property )
    {
        return property_exists( $this, $property ) ? $this->$property : null;
    }

    /**
     * Magic isset function handling isset() to non public properties
     *
     * Returns true for all (public/)protected/private properties.
     *
     * @ignore This method is for internal use
     * @access private
     *
     * @param string $property Name of the property
     *
     * @return boolean
     */
    public function __isset( $property )
    {
        return property_exists( $this, $property );
    }

    /**
     * Magic unset function handling unset() to non public properties
     *
     * @ignore This method is for internal use
     * @access private
     *
     * @throws \Nopis\Lib\Entity\PropertyNotFoundException exception on all writes to undefined properties so typos are not silently accepted and
     * @throws \Nopis\Lib\Entity\PropertyReadOnlyException exception on readonly (protected) properties.
     *
     * @uses __set()
     * @param string $property Name of the property
     *
     * @return boolean
     */
    public function __unset( $property )
    {
        $this->__set( $property, null );
    }

    /**
     * Returns a new instance of this class with the data specified by $array.
     *
     * $array contains all the data members of this class in the form:
     * array('member_name'=>value).
     *
     * __set_state makes this class exportable with var_export.
     * var_export() generates code, that calls this method when it
     * is parsed with PHP.
     *
     * @ignore This method is for internal use
     * @access private
     *
     * @param mixed[] $array
     *
     * @return DTO
     */
    static public function __set_state( array $array )
    {
        return new static( $array );
    }

    /**
     * Gets the current RouteCollection as an Iterator that includes all routes.
     *
     * It implements \IteratorAggregate.
     *
     * @return \ArrayIterator An \ArrayIterator object for iterating over routes
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
}
