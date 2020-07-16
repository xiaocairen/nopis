<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Pagination\Query\Criterion;

/**
 * Description of Field
 *
 * @author wangbin
 */
class Field implements FilterInterface
{

    /**
     * @var string
     */
    protected $target;

    /**
     * like Operator::EQ
     *
     * @var string
     */
    protected $operator;

    /**
     * The value(s) matched by the criteria
     * @var array(int|string)
     */
    protected $value;

    /**
     * Performs operator validation based on the Criterion specifications returned by {@see getSpecifications()}
     * @param string|null $target The target field name
     * @param string|null $operator
     *        The operator the Criterion uses. If null is given, will default to Operator::IN if $value is an array,
     *        Operator::EQ if it is not.
     * @param string[]|int[]|int|string $value
     *
     * @todo Add a dedicated exception
     * @throws \InvalidArgumentException if the provided operator isn't supported
     */
    public function __construct($target, $operator, $value)
    {
        switch ($operator) {
            case Operator::EQ       :   // =
            case Operator::NOT_EQ   :   // <>
            case Operator::GT       :   // >
            case Operator::GTE      :   // >=
            case Operator::LT       :   // <
            case Operator::LTE      :   // <=
                if (!is_numeric($value) && !is_string($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('Operator %s expect value is int or string by the Criterion %s. %s given', $operator, get_class( $this ), gettype( $value ))
                    );
                }
                break;

            case Operator::IS       :
            case Operator::IS_NOT   :
                $value = strtoupper(trim($value));
                if (!in_array($value, ['NULL', 'TRUE', 'FALSE', 'UNKNOWN'])) {
                    throw new \InvalidArgumentException(
                        sprintf('Operator %s expect value is boolean or NULL or UNKNOWN by the Criterion %s.  %s given ', $operator, get_class( $this ), gettype( $value ))
                    );
                }
                break;

            case Operator::IN       :   // in
            case Operator::NOT_IN   :   // not in
                if (!is_array($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('Operator %s expect value is array by the Criterion %s. %s given', $operator, get_class( $this ), gettype( $value ))
                    );
                }
                break;

            case Operator::BETWEEN  :   // between
                if (!is_array($value) || 2 != count($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('Operator %s expect value type is an array with two elements by the Criterion %s. %s given', $operator, get_class( $this ), gettype( $value ))
                    );
                }
                break;

            case Operator::LIKE     :   // like
                if (!is_string($value)) {
                    throw new \InvalidArgumentException(
                        sprintf('Operator %s expect value type is string by the Criterion %s. %s given', $operator, get_class( $this ), gettype( $value ))
                    );
                }
                break;

            default:
                throw new \InvalidArgumentException( "Operator $operator isn't supported by the Criterion " . get_class( $this ) );
        }

        $this->target = $target;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * Return the query criterion.
     *
     * @return array|\Nopis\Lib\Database\Params
     */
    public function getCriterion()
    {
        switch ($this->operator) {
            case Operator::EQ       :   // =
            case Operator::NOT_EQ   :   // <>
            case Operator::GT       :   // >
            case Operator::GTE      :   // >=
            case Operator::LT       :   // <
            case Operator::LTE      :   // <=
            case Operator::IS       :
            case Operator::IS_NOT   :
            case Operator::LIKE     :   // like
                return [$this->target, $this->operator, $this->value];

            case Operator::IN       :   // in
                return _in_($this->target, $this->value);

            case Operator::NOT_IN   :   // not in
                return _not_in_($this->target, $this->value);

            case Operator::BETWEEN  :   // between
                return _between_($this->target, $this->value[0], $this->value[1]);
        }
    }
}
