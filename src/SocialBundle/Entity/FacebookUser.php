<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * FacebookUser.
 *
 * @ORM\Entity(repositoryClass="SocialBundle\Repository\FacebookUserRepository")
 * @ORM\Table(name="ld_facebook_user")
 */
class FacebookUser implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="facebook_id", type="bigint")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", nullable=true)
     */
    private $userName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastName;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", nullable=true)
     */
    private $accessToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="SocialBundle\Entity\FacebookPage", mappedBy="user")
     */
    private $pages;

    /**
     * FacebookUser constructor.
     */
    public function __construct()
    {
        $this->roles = ['ROLE_ADMIN'];
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param int $id
     *
     * @return FacebookUser
     */
    public function setId(int $id)
    {
        $this->id = $id;

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
     * @param string $userName
     *
     * @return FacebookUser
     */
    public function setUsername(string $userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param bool $enabled
     *
     * @return FacebookUser
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return FacebookUser
     */
    public function enable(): bool
    {
        return $this->setEnabled(true);
    }

    /**
     * @return FacebookUser
     */
    public function disable(): bool
    {
        return $this->setEnabled(false);
    }

    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->getEnabled();
    }

    /**
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @param string $role
     *
     * @return FacebookUser
     */
    public function addRole(string $role)
    {
        if ($this->hasRole($role)) {
            return $this;
        }

        $role = strtoupper($role);

        $this->roles[] = $role;

        return $this;
    }

    /**
     * @param string $role
     *
     * @return FacebookUser
     */
    public function removeRole(string $role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string $lastName
     *
     * @return FacebookUser
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $email
     *
     * @return FacebookUser
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $firstName
     *
     * @return FacebookUser
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Get pages.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set accessToken.
     *
     * @param string $accessToken
     *
     * @return FacebookUser
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return FacebookUser
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param FacebookPage $page
     *
     * @return FacebookUser
     */
    public function addPage(FacebookPage $page)
    {
        $page->setUser($this);

        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
        }

        return $this;
    }

    /**
     * @param FacebookPage
     */
    public function removePage(FacebookPage $page)
    {
        $this->pages->removeElement($page);
    }

    /**
     * Implements UserInterface.
     *
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * Implements UserInterface.
     *
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Implements UserInterface.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return true;
    }
}
