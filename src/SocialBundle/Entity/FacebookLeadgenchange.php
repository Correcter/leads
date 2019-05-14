<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ld_leadgen_change")
 */
class FacebookLeadgenchange
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="page_id")
     */
    private $pageId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="adgroup_id")
     */
    private $adgroupId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="ad_id")
     */
    private $adId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="form_id")
     */
    private $formId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="leadgen_id")
     */
    private $leadgenId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_time")
     */
    private $createdTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="field_name")
     */
    private $fieldName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $fieldName
     *
     * @return FacebookLeadGenChange
     */
    public function setFieldName(string $fieldName = null)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param int $pageId
     *
     * @return FacebookLeadGenChange
     */
    public function setPageId(int $pageId = null)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * @param int $adgroupId
     *
     * @return FacebookLeadGenChange
     */
    public function setAdgroupId(int $adgroupId = null)
    {
        $this->adgroupId = $adgroupId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdgroupId()
    {
        return $this->adgroupId;
    }

    /**
     * @param int $adId
     *
     * @return FacebookLeadGenChange
     */
    public function setAdId(int $adId = null)
    {
        $this->adId = $adId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdId()
    {
        return $this->adId;
    }

    /**
     * @param int $leadgenId
     *
     * @return FacebookLeadGenChange
     */
    public function setLeadgenId(int $leadgenId = null)
    {
        $this->leadgenId = $leadgenId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLeadgenId()
    {
        return $this->leadgenId;
    }

    /**
     * @param int $formId
     *
     * @return FacebookLeadGenChange
     */
    public function setFormId(int $formId = null)
    {
        $this->formId = $formId;

        return $this;
    }

    /**
     * @return int
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param \DateTime $createdTime
     *
     * @return FacebookLeadGenChange
     */
    public function setCreatedTime(\DateTime $createdTime = null)
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedTime()
    {
        return $this->createdTime;
    }
}
