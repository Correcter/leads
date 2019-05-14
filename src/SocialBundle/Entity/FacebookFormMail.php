<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SocialBundle\Repository\FacebookFormMailRepository")
 * @ORM\Table(name="fb_form_mails")
 */
class FacebookFormMail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id()
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="mail")
     */
    protected $email;

    /**
     * @ORM\Column(type="integer", name="form_id")
     */
    protected $formId;

    /**
     * @ORM\ManyToOne(targetEntity="FacebookLeadgenForm", inversedBy="formMails", cascade={"persist"})
     * @ORM\JoinColumn(name="form_id", referencedColumnName="form_id")
     */
    private $form;

    /**
     * @param int $formId
     *
     * @return FacebookFormMail
     */
    public function setFormId(int $formId = null)
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
     * @param string $email
     *
     * @return FacebookFormMail
     */
    public function setEmail(string $email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
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
     * @return FacebookFormMail
     */
    public function setForm(FacebookLeadgenForm $form)
    {
        $this->form = $form;

        return $this;
    }
}
