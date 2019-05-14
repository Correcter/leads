<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SocialBundle\Repository\FacebookLeadsRepository")
 * @ORM\Table(name="fb_leads")
 */
class FacebookLeads
{
    /**
     * @ORM\ManyToOne(targetEntity="FacebookLeadgenForm", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinColumn(name="form_id", referencedColumnName="form_id")
     */
    protected $form;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="bigint", name="lead_id")
     */
    private $leadId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="form_id")
     */
    private $formId;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="field_data")
     */
    private $fieldData;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="created_time")
     */
    private $createdTime;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="sended")
     */
    private $sended;

    /**
     * FacebookLeads constructor.
     */
    public function __construct()
    {
        $this->createdTime = time();
        $this->sended = false;
    }

    /**
     * @param int
     *
     * @return FacebookLeads
     */
    public function setId(int $leadId)
    {
        $this->leadId = $leadId;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->leadId;
    }

    /**
     * @param int
     *
     * @return FacebookLeads
     */
    public function setFormId(int $formId)
    {
        $this->formId = $formId;

        return $this;
    }

    /**
     * @return int
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * @param \DateTime
     *
     * @return FacebookLeads
     */
    public function setCreatedTime(\DateTime $createdTime)
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedTime(): \DateTime
    {
        return $this->createdTime;
    }

    /**
     * @param string
     *
     * @return FacebookLeads
     */
    public function setFieldData(string $fieldData)
    {
        $this->fieldData = $fieldData;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldData(): string
    {
        return $this->fieldData;
    }

    /**
     * @param bool
     *
     * @return FacebookLeads
     */
    public function setSended(bool $sended)
    {
        $this->sended = $sended;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSended(): bool
    {
        return $this->sended;
    }

    /**
     * @param FacebookLeadgenForm $form
     *
     * @return FacebookLeads
     */
    public function setForm(FacebookLeadgenForm $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return FacebookLeadgenForm
     */
    public function getForm(): FacebookLeadgenForm
    {
        return $this->form;
    }

    /**
     * @param void
     */
    public function removeForm()
    {
        $this->form = null;
    }
}
