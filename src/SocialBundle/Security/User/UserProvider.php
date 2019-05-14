<?php

namespace SocialBundle\Security\User;

use Doctrine\Common\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use SocialBundle\Entity\FacebookUser;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Vitaly Dergunov (<v.dergunov@icontext.ru>)
 */
class UserProvider extends EntityUserProvider
{
    /**
     * UserProvider constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacebookUser::class, ['facebook' => 'id']);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($this->getClient()->fetchUserFromToken($credentials)->getId());
    }

    /**
     * @param string $userName
     *
     * @return \SocialBundle\Entity\FacebookUser
     */
    public function loadUserByUsername($userName)
    {
        $user = $this->findUser(['username' => $userName]);
        if (!$user) {
            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $userName));
        }

        return $user;
    }

    /**
     * @param UserResponseInterface $response
     *
     * @return \SocialBundle\Entity\FacebookUser
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userId = $response->getUserName();

        $property = $this->getProperty($response);

        $user = $this
            ->findUser([$property => $userId]);

        if (!$user) {
            $user = $this
                ->createUser()
                ->setCreatedAt(new \DateTime('NOW'));
        }

        $user
            ->setFirstName($response->getFirstName())
            ->setLastName($response->getLastName())
            ->setUsername($response->getRealName())
            ->setEmail($response->getEmail())
            ->setAccessToken($response->getAccessToken());

        $service = $response->getResourceOwner()->getName();

        switch ($service) {
        case 'facebook':
            $user->setId($userId);

            break;
        default:
            throw new \UnexpectedValueException(
                sprintf('Unexpected service "%s"', $service)
            );
        }

        $this->updateUser($user);

        return $user;
    }

    /**
     * @return UserInterface
     */
    protected function createUser()
    {
        return new $this->class();
    }

    /**
     * @param UserInterface $user
     */
    protected function updateUser(UserInterface $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * Gets the property for the response.
     *
     * @param UserResponseInterface $response
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getProperty(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        return $this->properties[$resourceOwnerName];
    }
}
