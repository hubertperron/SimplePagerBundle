<?php

namespace Ideato\SimplePagerBundle\Paginator;

use Ideato\SimplePagerBundle\Pager\sfPager;

class Pager extends sfPager
{
    private $query_class = 'Doctrine\ORM\AbstractQuery';
    private $query_scalar_hydration_mode = 'HYDRATE_SCALAR';
    
    private $query = null;

    /**
     * Clone the query and then sets all the needed parameters
     *
     * @return mixed returns and object of class $query_class (default: Doctrine\ORM\AbstractQuery )
     */
    protected function cloneQuery()
    {
        $q = clone $this->query;
        $q->setParameters($this->query->getParameters());

        return $q;
    }

    /**
     * Based on the $query_class and $query_scalar_hydration_mode,
     * returns the value of the constant that specifies
     * a scalar hydration mode for a query object of class $query_class
     *
     * @return mixed
     */
    public function getScalarHydrationValue()
    {
        $ref = new \ReflectionClass($this->query_class);
        return $ref->getConstant($this->query_scalar_hydration_mode);
    }

    /**
     * Before setting the query, checks if the given object is of class $query_class.
     *
     * @throw InvalidArgumentException
     * @param query_class $query
     */
    public function setQuery($query)
    {
        if (!$query instanceof  $this->query_class)
        {
            throw new \InvalidArgumentException('The given query is not ad instance of '.$this->query_class.',  but '.  \get_class($query));
        }
        $this->query = $query;
    }

    
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 1. Checks if the query has been set.
     * 2. Retrieves the actual page resutls
     * 3. Sets the results total number
     * 4. Calculate and sets the last page
     */
    public function init()
    {
        if ($this->query === null)
        {
            throw new \Exception('You must specify a query');
        }

        
        $this->getResults();
        $nb_results = count($this->cloneQuery()->getResult($this->getScalarHydrationValue()));

        $this->setNbResults($nb_results);
        $this->setLastPage(ceil($this->nbResults / $this->maxPerPage));
    }

    /**
     * Returns an array of results on the given page.
     *
     * @return array
     */
    public function getResults()
    {
        if ($this->results !== null)
        {
            return $this->results;
        }

        $q = $this->cloneQuery();
        $this->results = $q->setMaxResults($this->getMaxPerPage())->setFirstResult($this->getFirstIndice() - 1)->execute();

        return $this->results;
    }

    /**
     * Returns an object at a certain offset.
     *
     * Used internally by {@link getCurrent()}.
     *
     * @return mixed
     */
    protected function retrieveObject($offset)
    {
        return $this->results[$offset - $this->getFirstIndice()];
    }

    public function getQueryClass()
    {
        return $this->query_class;
    }

    public function setQueryClass($query_class)
    {
        $this->query_class = $query_class;
    }

    public function getQueryScalarHydrationMode()
    {
        return $this->query_scalar_hydration_mode;
    }

    public function setQueryScalarHydrationMode($query_scalar_hydration_mode)
    {
        $this->query_scalar_hydration_mode = $query_scalar_hydration_mode;
    }



}