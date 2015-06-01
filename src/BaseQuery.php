<?php namespace ClanCats\Hydrahon;

/**
 * Base query object
 ** 
 * @package         Hydrahon
 * @copyright       2015 Mario Döring
 */

use ClanCats\Hydrahon\Query\Expression;

class BaseQuery
{   
    /**
     * The database the query should be executed on
     * 
     * @var string
     */
    protected $database = null;

    /**
     * The table the query should be executed on
     * 
     * @var string
     */
    protected $table = null;

    /**
     * The callback where we fetch the results from
     *
     * @var callable
     */
    private $resultFetcher = null;

    /**
     * Construct new query object
     *
     * @param callable              $resultFetcher
     * @param string                $table
     * @param string                $database
     * @return void
     */
    public function __construct($resultFetcher = null, $table = null, $database = null)
    {
        $this->resultFetcher = $resultFetcher;
        $this->table = $table;
        $this->database = $database;
    }

    /**
     * Creates a new raw db expression instance
     * 
     * @param string                $expression
     * @return ClanCats\Hydrahon\Query\Expression
     */
    final public function raw($expression)
    {
        return new Expression($expression);
    } 

    /**
     * Returns all avaialbe attribute data 
     * The result fetcher callback is excluded
     * 
     * @return array
     */
    final public function attributes()
    {
        $excluded = array('resultFetcher');
        $attributes = get_object_vars($this);

        foreach ($excluded as $key) 
        {
            if (isset($attributes[$key])) { unset($attributes[$key]); }
        }

        return $attributes;
    }

    /**
     * Creates another query instance with the current parameters
     *
     * @param string                            $className
     * @return ClanCats\Hydrahon\BaseQuery
     */
    final protected function createSubQuery($className)
    {
        return new $className($this->resultFetcher, $this->table, $this->database);
    }

    /**
     * Run the result fetcher and return the results
     *
     * @return mixed
     */
    final protected function executeResultFetcher()
    {
        if ( is_null($this->resultFetcher))
        {
            throw new Exception('Cannot execute result fetcher callbacks without inital assignment.');
        }

        return call_user_func_array($this->resultFetcher, array( &$this ));
    }
}