<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="ld_vkontakte_leads")
 */
class VkontakteIncomingLead
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="bigint", nullable=false, name="id")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", nullable=false, name="lead_id")
     */
    private $leadId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", nullable=false, name="group_id")
     */
    private $groupId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", nullable=false, name="user_id")
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", nullable=false, name="form_id")
     */
    private $formId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false, name="form_name")
     */
    private $formName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="answers")
     */
    private $answers;

    /**
     * @ORM\Column(type="datetime", name="date_time")
     *
     * @var \DateTime
     */
    private $dateTime;

    /**
     * Recived lead constructor.
     */
    public function __construct()
    {
        $this->dateTime = new \DateTime();
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $leadId
     */
    public function setLeadId(int $leadId)
    {
        $this->leadId = $leadId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLeadId(): int
    {
        return $this->leadId;
    }

    /**
     * Set groupId.
     *
     * @param int $groupId
     */
    public function setGroupId(int $groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /*\
     * @return int
     */
    public function getGroupId(): int
    {
        return $this->groupId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $formId
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
     * @param string $formName
     */
    public function setFormName(string $formName)
    {
        $this->formName = $formName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormName(): string
    {
        return $this->formName;
    }

    /**
     * @param array $answers
     */
    public function setAnswers(string $answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnswers(): string
    {
        return $this->answers;
    }

    /**
     * @param \DateTime $dateTime
     */
    public function setDate(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->dateTime;
    }
}
