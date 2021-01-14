<?php

namespace ForeverCompanies\StonesIntermediary\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface StonesIntermediaryInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const CERTIFICATE_NUMBER = 'certificate_number';
    const SHAPE_CODE = 'shape_code';
    const SHAPE_NAME = 'shape_name';
    const SUPPLIER = 'supplier';
    const LAB = 'lab';
    const WEIGHT = 'weight';
    const COLOR = 'color';
    const CLARITY = 'clarity';
    const CUT_GRADE = 'cut_grade';
    const CERTIFICATE_URL = 'certificate_url';
    const IMAGE = 'image';
    const VIDEO = 'video';
    const RAPAPORT = 'rapaport';
    const RAP_PERCENT = 'rap_percent';
    const COST = 'cost';
    const LAST_SEEN = 'last_seen';
    const LAST_IMPORTED = 'last_imported';
    const IMPORTED = 'imported';
    const FAILED_IMPORT = 'failed_import';
    const ONLINE = 'online';
    const LENGTH_TO_WIDTH = 'length_to_width';
    const DEPTH = 'depth';
    const TABLE = 'table';
    const POLISH = 'polish';
    const SYMMETRY = 'symmetry';
    const GRIDLE = 'gridle';
    const CULET = 'culet';
    const MIUSA = 'miusa';
    const SHIPPING = 'shipping';
    const MSRP = 'msrp';

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
    public function getCertificateNumber();

    /**
     * @param string $number
     * @return $this
     */
    public function setCertificateNumber(string $number);

    /**
     * @return string
     */
    public function getShapeCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setShapeCode(string $code);

    /**
     * @return string
     */
    public function getShapeName();

    /**
     * @param string $name
     * @return $this
     */
    public function setShapeName(string $name);

    /**
     * @return string
     */
    public function getSupplier();

    /**
     * @param string $supplier
     * @return $this
     */
    public function setSupplier(string $supplier);

    /**
     * @return string
     */
    public function getLab();

    /**
     * @param string $lab
     * @return $this
     */
    public function setLab(string $lab);

    /**
     * @return float
     */
    public function getWeight();

    /**
     * @param float $weight
     * @return $this
     */
    public function setWeight(float $weight);

    /**
     * @return string
     */
    public function getColor();

    /**
     * @param string $color
     * @return $this
     */
    public function setColor(string $color);

    /**
     * @return string
     */
    public function getClarity();

    /**
     * @param string $clarity
     * @return $this
     */
    public function setClarity(string $clarity);

    /**
     * @return string
     */
    public function getCutGrade();

    /**
     * @param string $cut
     * @return $this
     */
    public function setCutGrade(string $cut);

    /**
     * @return string
     */
    public function getCertificateUrl();

    /**
     * @param string $url
     * @return $this
     */
    public function setCertificateUrl(string $url);

    /**
     * @return string
     */
    public function getImage();

    /**
     * @param string $url
     * @return $this
     */
    public function setImage(string $url);

    /**
     * @return string
     */
    public function getVideo();

    /**
     * @param string $url
     * @return $this
     */
    public function setVideo(string $url);

    /**
     * @return float
     */
    public function getRapaport();

    /**
     * @param float $rapaport
     * @return $this
     */
    public function setRapaport(float $rapaport);

    /**
     * @return float
     */
    public function getRapPercent();

    /**
     * @param float $percent
     * @return $this
     */
    public function setRapPercent(float $percent);

    /**
     * @return float
     */
    public function getCost();

    /**
     * @param float $cost
     * @return $this
     */
    public function setCost(float $cost);

    /**
     * @return string
     */
    public function getLastSeen();

    /**
     * @param string $date
     * @return $this
     */
    public function setLastSeen(string $date);

    /**
     * @return string
     */
    public function getLastImported();

    /**
     * @param string $date
     * @return $this
     */
    public function setLastImported(string $date);

    /**
     * @return int
     */
    public function getImported();

    /**
     * @param int $imported
     * @return $this
     */
    public function setImported(int $imported);

    /**
     * @return int
     */
    public function getFailedImport();

    /**
     * @param int $failed
     * @return $this
     */
    public function setFailedImport(int $failed);

    /**
     * @return string
     */
    public function getOnline();

    /**
     * @param string $online
     * @return $this
     */
    public function setOnline(string $online);

    /**
     * @return float
     */
    public function getLengthToWidth();

    /**
     * @param float $length
     * @return $this
     */
    public function setLengthToWidth(float $length);

    /**
     * @return float
     */
    public function getDepth();

    /**
     * @param float $depth
     * @return $this
     */
    public function setDepth(float $depth);

    /**
     * @return float
     */
    public function getTable();

    /**
     * @param float $table
     * @return $this
     */
    public function setTable(float $table);

    /**
     * @return string
     */
    public function getPolish();

    /**
     * @param string $polish
     * @return $this
     */
    public function setPolish(string $polish);

    /**
     * @return string
     */
    public function getSymmetry();

    /**
     * @param string $symmetry
     * @return $this
     */
    public function setSymmetry(string $symmetry);

    /**
     * @return string
     */
    public function getGridle();

    /**
     * @param string $griddle
     * @return $this
     */
    public function setGridle(string $gridle);

    /**
     * @return string
     */
    public function getCulet();

    /**
     * @param string $culet
     * @return $this
     */
    public function setCulet(string $culet);

    /**
     * @return string
     */
    public function getMiusa();

    /**
     * @param string $madeInUsa
     * @return $this
     */
    public function setMiusa(string $madeInUsa);

    /**
     * @return string
     */
    public function getShipping();

    /**
     * @param string $shipping
     * @return $this
     */
    public function setShipping(string $shipping);

    /**
     * @return float
     */
    public function getMsrp();

    /**
     * @param float $msrp
     * @return $this
     */
    public function setMsrp(float $msrp);
}
