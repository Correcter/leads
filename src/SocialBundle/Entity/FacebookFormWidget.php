<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SocialBundle\Repository\FacebookFormWidgetRepository")
 * @ORM\Table(name="fb_form_widgets")
 */
class FacebookFormWidget
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="bigint", name="id")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="form_id")
     */
    protected $formId;

    /**
     * @ORM\Column(type="string", name="widget_hash")
     */
    protected $widgetHash;

    /**
     * @ORM\Column(type="bigint", name="manager")
     */
    protected $manager;

    /**
     * @ORM\Column(type="string", name="api_key")
     */
    protected $apiKey;

    /**
     * @ORM\ManyToOne(targetEntity="FacebookLeadgenForm", inversedBy="widgets", cascade={"persist"})
     * @ORM\JoinColumn(name="form_id", referencedColumnName="form_id")
     */
    private $form;

    /**
     * @return int
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * @param string $widgetHash
     */
    public function setWidgetHash(string $widgetHash)
    {
        $this->widgetHash = $widgetHash;
    }

    /**
     * @return string
     */
    public function getWidgetHash(): string
    {
        return $this->widgetHash;
    }

    /**
     * @param string $manager
     */
    public function setManager(int $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return int
     */
    public function getManager(): int
    {
        return $this->manager;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return int
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return FacebookLeadgenForm
     */
    public function getForm(): FacebookLeadgenForm
    {
        return $this->form;
    }

    /**
     * @param FacebookLeadgenForm $form
     *
     * @return FacebookFormWidget
     */
    public function setForm(FacebookLeadgenForm $form)
    {
        $form->addWidget($this);

        $this->form = $form;

        return $this;
    }
}
