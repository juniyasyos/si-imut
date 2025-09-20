<?php

namespace App\Commands\Contracts;

/**
 * Queryable Command Interface
 *
 * For commands that fetch data without side effects
 */
interface QueryCommandInterface extends CommandInterface
{
    /**
     * Set query parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters): self;

    /**
     * Add filter to the query
     *
     * @param string $field
     * @param mixed $value
     * @param string $operator
     * @return $this
     */
    public function addFilter(string $field, $value, string $operator = '='): self;

    /**
     * Set pagination parameters
     *
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function paginate(int $page = 1, int $perPage = 15): self;

    /**
     * Set sorting parameters
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function sortBy(string $field, string $direction = 'asc'): self;
}
