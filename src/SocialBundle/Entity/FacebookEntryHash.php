<?php

namespace SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="fb_entries_hash")
 */
class FacebookEntryHash
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     */
    private $time;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    private $hash;

    /**
     * @param int $id
     *
     * @return FacebookEntryHash
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $time
     *
     * @return FacebookEntryHash
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string $hash
     *
     * @return FacebookEntryHash
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
