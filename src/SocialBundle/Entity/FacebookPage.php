<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SocialBundle\Repository\FacebookPageRepository")
 * @ORM\Table(name="fb_pages")
 */
class FacebookPage
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="bigint", name="page_id")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="user_id")
     */
    protected $userId;

    /**
     * @ORM\Column(type="string", name="page_name")
     */
    protected $pageName;

    /**
     * @ORM\OneToMany(targetEntity="SocialBundle\Entity\FacebookLeadgenForm", mappedBy="page")
     */
    protected $forms;

    /**
     * @ORM\ManyToOne(targetEntity="SocialBundle\Entity\FacebookUser", inversedBy="pages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="facebook_id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", name="access_token")
     */
    protected $accessToken;

    /**
     * @ORM\Column(type="boolean", name="subscribed")
     */
    protected $subscribed;

    /**
     * FacebookPage constructor.
     */
    public function __construct()
    {
        $this->forms = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param int
     */
    public function setId(int $pageId)
    {
        $this->id = $pageId;

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
     * @param int
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /**
     * @param string
     */
    public function setPageName(string $pageName)
    {
        $this->pageName = $pageName;

        return $this;
    }

    /**
     * @param FacebookLeadgenForm $forms
     *
     * @return FacebookPage
     */
    public function addForm(FacebookLeadgenForm $form)
    {
        if (!$this->forms->contains($form)) {
            $this->forms[] = $form;
        }

        $form->setPage($this);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getForms(): \Doctrine\Common\Collections\Collection
    {
        return $this->forms;
    }

    /**
     * @param \SocialBundle\Entity\FacebookUser $user
     *
     * @return FacebookPage
     */
    public function setUser(FacebookUser $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return FacebookUser
     */
    public function getUser(): FacebookUser
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $access_token
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @param bool $subscribed
     */
    public function setSubscribed(bool $subscribed)
    {
        $this->subscribed = $subscribed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSubscribed(): bool
    {
        return $this->subscribed;
    }

    /**
     * @param int $pageId
     *
     * @return FacebookPage
     */
    public function getFormByPageId(int $pageId)
    {
        foreach ($this->getForms() as $form) {
            if ($form->getPageId() === $pageId) {
                return $form;
            }
        }

        return null;
    }

    public function removeUser()
    {
        $this->user = null;
    }

    /**
     * @param FacebookLeadgenForm $form
     */
    public function removeForm(FacebookUser $form)
    {
        $this->forms->removeElement($form);
    }
}
