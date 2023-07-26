<?php

namespace IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces;

/**
 * Interface ResourceRepositoryInterface
 * @depreacted  1.5.0
 * @package IdeoLogix\DigitalLicenseManager\Abstracts\Interfaces
 */
interface ResourceRepositoryInterface
{
    /**
     * @param array $data
     *
     * @return void
     */
    function sanitize(&$data);

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function insert($data);

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function findBy($query);

    /**
     * @return mixed
     */
    public function findAll();

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function findAllBy($query);

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed
     */
    public function update($id, $data);

    /**
     * @param array $query
     * @param array $data
     *
     * @return mixed
     */
    public function updateBy($query, $data);

    /**
     * @param array $ids
     *
     * @return mixed
     */
    public function delete($ids);

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function deleteBy($query);

    /**
     * @return mixed
     */
    public function count();

    /**
     * @param array $query
     *
     * @return mixed
     */
    public function countBy($query);

    /**
     * @param string $queryString
     *
     * @return mixed
     */
    public function query($queryString);

    /**
     * @return mixed
     */
    public function truncate();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @return string
     */
    public function getPrimaryKey();

    /**
     * @return string
     */
    public function getModel();

    /**
     * @return array
     */
    public function getMapping();
}
