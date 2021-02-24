<?php

namespace ForeverCompanies\StonesIntermediary\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface StonesSupplierInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const NAME = 'name';
    const CODE = 'code';
    const EMAIL = 'email';
    const ENABLED = 'enabled';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode(string $code);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled);
}
