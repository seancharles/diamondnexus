<?php

namespace ForeverCompanies\StonesIntermediary\Model;

use ForeverCompanies\StonesIntermediary\Api\Data\StonesIntermediaryInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class StonesIntermediary extends AbstractModel implements IdentityInterface, StonesIntermediaryInterface
{
    const CACHE_TAG = 'forevercompanies_stones_intermediary';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [];
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\StonesIntermediary::class);
    }

    /**
     * @inheritDoc
     */
    public function getCertificateNumber()
    {
        return $this->getData(self::CERTIFICATE_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setCertificateNumber(string $number)
    {
        return $this->setData(self::CERTIFICATE_NUMBER, $number);
    }

    /**
     * @inheritDoc
     */
    public function getShapeCode()
    {
        return $this->getData(self::SHAPE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setShapeCode(string $code)
    {
        return $this->setData(self::SHAPE_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getShapeName()
    {
        return $this->getData(self::SHAPE_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setShapeName(string $name)
    {
        return $this->setData(self::SHAPE_NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getSupplier()
    {
        return $this->getData(self::SUPPLIER);
    }

    /**
     * @inheritDoc
     */
    public function setSupplier(string $supplier)
    {
        return $this->setData(self::SUPPLIER, $supplier);
    }

    /**
     * @inheritDoc
     */
    public function getLab()
    {
        return $this->getData(self::LAB);
    }

    /**
     * @inheritDoc
     */
    public function setLab(string $lab)
    {
        return $this->setData(self::LAB, $lab);
    }

    /**
     * @inheritDoc
     */
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * @inheritDoc
     */
    public function setWeight(float $weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /**
     * @inheritDoc
     */
    public function getColor()
    {
        return $this->getData(self::COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setColor(string $color)
    {
        return $this->setData(self::COLOR, $color);
    }

    /**
     * @inheritDoc
     */
    public function getClarity()
    {
        return $this->getData(self::CLARITY);
    }

    /**
     * @inheritDoc
     */
    public function setClarity(string $clarity)
    {
        return $this->setData(self::CLARITY, $clarity);
    }

    /**
     * @inheritDoc
     */
    public function getCutGrade()
    {
        return $this->getData(self::CUT_GRADE);
    }

    /**
     * @inheritDoc
     */
    public function setCutGrade(string $cut)
    {
        return $this->setData(self::CUT_GRADE, $cut);
    }

    /**
     * @inheritDoc
     */
    public function getCertificateUrl()
    {
        return $this->getData(self::CERTIFICATE_URL);
    }

    /**
     * @inheritDoc
     */
    public function setCertificateUrl(string $url)
    {
        return $this->setData(self::CERTIFICATE_URL, $url);
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * @inheritDoc
     */
    public function setImage(string $url)
    {
        return $this->setData(self::IMAGE, $url);
    }

    /**
     * @inheritDoc
     */
    public function getVideo()
    {
        return $this->getData(self::VIDEO);
    }

    /**
     * @inheritDoc
     */
    public function setVideo(string $url)
    {
        return $this->setData(self::VIDEO, $url);
    }

    /**
     * @inheritDoc
     */
    public function getRapaport()
    {
        return $this->getData(self::RAPAPORT);
    }

    /**
     * @inheritDoc
     */
    public function setRapaport(float $rapaport)
    {
        return $this->setData(self::RAPAPORT, $rapaport);
    }

    /**
     * @inheritDoc
     */
    public function getRapPercent()
    {
        return $this->getData(self::RAP_PERCENT);
    }

    /**
     * @inheritDoc
     */
    public function setRapPercent(float $percent)
    {
        return $this->setData(self::RAP_PERCENT, $percent);
    }

    /**
     * @inheritDoc
     */
    public function getCost()
    {
        return $this->getData(self::COST);
    }

    /**
     * @inheritDoc
     */
    public function setCost(float $cost)
    {
        return $this->setData(self::COST, $cost);
    }

    /**
     * @inheritDoc
     */
    public function getLastSeen()
    {
        return $this->getData(self::LAST_SEEN);
    }

    /**
     * @inheritDoc
     */
    public function setLastSeen(string $date)
    {
        return $this->setData(self::LAST_SEEN, $date);
    }

    /**
     * @inheritDoc
     */
    public function getLastImported()
    {
        return $this->getData(self::LAST_IMPORTED);
    }

    /**
     * @inheritDoc
     */
    public function setLastImported(string $date)
    {
        return $this->setData(self::LAST_IMPORTED, $date);
    }

    /**
     * @inheritDoc
     */
    public function getImported()
    {
        return $this->getData(self::IMPORTED);
    }

    /**
     * @inheritDoc
     */
    public function setImported(int $imported)
    {
        return $this->setData(self::IMPORTED, $imported);
    }

    /**
     * @inheritDoc
     */
    public function getFailedImport()
    {
        return $this->getData(self::FAILED_IMPORT);
    }

    /**
     * @inheritDoc
     */
    public function setFailedImport(int $failed)
    {
        return $this->setData(self::FAILED_IMPORT, $failed);
    }

    /**
     * @inheritDoc
     */
    public function getOnline()
    {
        return $this->getData(self::ONLINE);
    }

    /**
     * @inheritDoc
     */
    public function setOnline(string $online)
    {
        return $this->setData(self::ONLINE, $online);
    }

    /**
     * @inheritDoc
     */
    public function getLengthToWidth()
    {
        return $this->getData(self::LENGTH_TO_WIDTH);
    }

    /**
     * @inheritDoc
     */
    public function setLengthToWidth(float $length)
    {
        return $this->setData(self::LENGTH_TO_WIDTH, $length);
    }

    /**
     * @inheritDoc
     */
    public function getDepth()
    {
        return $this->getData(self::DEPTH);
    }

    /**
     * @inheritDoc
     */
    public function setDepth(float $depth)
    {
        return $this->setData(self::DEPTH, $depth);
    }

    /**
     * @inheritDoc
     */
    public function getTable()
    {
        return $this->getData(self::TABLE);
    }

    /**
     * @inheritDoc
     */
    public function setTable(float $table)
    {
        return $this->setData(self::TABLE, $table);
    }

    /**
     * @inheritDoc
     */
    public function getPolish()
    {
        return $this->getData(self::POLISH);
    }

    /**
     * @inheritDoc
     */
    public function setPolish(string $polish)
    {
        return $this->setData(self::POLISH, $polish);
    }

    /**
     * @inheritDoc
     */
    public function getSymmetry()
    {
        return $this->getData(self::SYMMETRY);
    }

    /**
     * @inheritDoc
     */
    public function setSymmetry(string $symmetry)
    {
        return $this->setData(self::SYMMETRY, $symmetry);
    }

    /**
     * @inheritDoc
     */
    public function getGridle()
    {
        return $this->getData(self::GRIDLE);
    }

    /**
     * @inheritDoc
     */
    public function setGridle(string $gridle)
    {
        return $this->setData(self::GRIDLE, $gridle);
    }

    /**
     * @inheritDoc
     */
    public function getCulet()
    {
        return $this->getData(self::CULET);
    }

    /**
     * @inheritDoc
     */
    public function setCulet(string $culet)
    {
        return $this->setData(self::CULET, $culet);
    }

    /**
     * @inheritDoc
     */
    public function getMiusa()
    {
        return $this->getData(self::MIUSA);
    }

    /**
     * @inheritDoc
     */
    public function setMiusa(string $madeInUsa)
    {
        return $this->setData(self::MIUSA, $madeInUsa);
    }

    /**
     * @inheritDoc
     */
    public function getShipping()
    {
        return $this->getData(self::SHIPPING);
    }

    /**
     * @inheritDoc
     */
    public function setShipping(string $shipping)
    {
        return $this->setData(self::SHIPPING, $shipping);
    }

    /**
     * @inheritDoc
     */
    public function getMsrp()
    {
        return $this->getData(self::MSRP);
    }

    /**
     * @inheritDoc
     */
    public function setMsrp(float $msrp)
    {
        return $this->setData(self::MSRP, $msrp);
    }
}
