<?php

namespace App\Commands\Contracts;

/**
 * Mutation Command Interface
 *
 * For commands that modify data (Create, Update, Delete)
 */
interface MutationCommandInterface extends CommandInterface
{
    /**
     * Set the data to be processed
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self;

    /**
     * Set the entity ID for update/delete operations
     *
     * @param mixed $id
     * @return $this
     */
    public function setEntityId($id): self;

    /**
     * Execute the command and return the result
     *
     * @return mixed The created/updated entity or deletion result
     */
    public function execute();
}
