<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SocialBundle\Repository\FacebookLeadgenFormRepository")
 * @ORM\Table(name="fb_leadgen_forms")
 */
class FacebookLeadgenForm
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="bigint", name="form_id")
     */
    protected $id;

    /**
     * @ORM\Column(type="bigint", name="page_id")
     */
    protected $pageId;

    /**
     * @ORM\Column(type="string", name="form_name")
     */
    protected $formName;

    /**
     * @ORM\Column(type="boolean", name="enabled")
     */
    protected $enabled;

    /**
     * @ORM\OneToMany(targetEntity="SocialBundle\Entity\FacebookFormWidget", mappedBy="form")
     */
    protected $widgets;

    /**
     * @ORM\ManyToOne(targetEntity="SocialBundle\Entity\FacebookPage", inversedBy="forms")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="page_id")
     */
    private $page;

    /**
     * @ORM\OneToMany(targetEntity="SocialBundle\Entity\FacebookLeads", mappedBy="form")
     */
    private $leads;

    /**
     * @ORM\OneToMany(targetEntity="SocialBundle\Entity\FacebookFormMail", mappedBy="form")
     */
    private $formMails;

    /**
     * FacebookLeadgenForm constructor.
     */
    public function __construct()
    {
        $this->widgets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->leads = new \Doctrine\Common\Collections\ArrayCollection();
        $this->formMails = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function setId(int $formId)
    {
        $this->id = $formId;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->pageId;
    }

    /**
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function setPageId(int $pageId)
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function setFormName(string $formName = null)
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
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function setEnabled(bool $enabled = false)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return \SocialBundle\Entity\FacebookPage
     */
    public function getPage(): FacebookPage
    {
        return $this->page;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFormMails(): \Doctrine\Common\Collections\Collection
    {
        return $this->formMails;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWidgets(): \Doctrine\Common\Collections\Collection
    {
        return $this->widgets;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeads(): \Doctrine\Common\Collections\Collection
    {
        return $this->leads;
    }

    /**
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function setPage(FacebookPage $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param FacebookFormMail $formMail
     *
     * @return FacebookLeadgenForm
     */
    public function addFormMail(FacebookFormMail $formMail)
    {
        if (!$this->formMails->contains($formMail)) {
            $this->formMails[] = $formMail;
        }

        $formMail->setForm($this);

        return $this;
    }

    /**
     * @param FacebookFormWidget $widget
     *
     * @return FacebookLeadgenForm
     */
    public function addWidget(FacebookFormWidget $widget)
    {
        $widget->setForm($this);

        if (!$this->widgets->contains($widget)) {
            $this->widgets[] = $widget;
        }

        return $this;
    }

    /**
     * @param \SocialBundle\Entity\FacebookLeads
     *
     * @return FacebookLeadgenForm
     */
    public function addLead(FacebookLeads $lead)
    {
        if (!$this->leads->contains($lead)) {
            $this->leads[] = $lead;
        }

        $lead->setForm($this);

        return $this;
    }

    /**
     * @param FacebookLeads $lead
     */
    public function removeLead(FacebookLeads $lead)
    {
        $this->leads->removeElement($lead);
    }

    /**
     * @param FacebookFormMail $formMail
     */
    public function removeFormMail(FacebookFormMail $formMail)
    {
        $this->formMails->removeElement($formMail);
    }

    /**
     * @param FacebookFormWidget
     */
    public function removeWidget(FacebookFormWidget $widget)
    {
        $this->widgets->removeElement($widget);
    }
}
