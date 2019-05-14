<?php

namespace SocialBundle\Repository;

class FacebookUserRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param int $userId
     *
     * @return mixed
     */
    public function getUserTokenById(int $userId)
    {
        return
            $this->createQueryBuilder('fbu')
                ->select('fbu.accessToken')
                ->where('fbu.id=:userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * @param $userId
     *
     * @return array
     */
    public function findOneById(int $userId)
    {
        return
            $this->createQueryBuilder('u')
                ->select('u')
                ->where('u.id=:userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }

    /**
     * @param int $userId
     * @param $accessToken
     *
     * @return mixed
     */
    public function setUserTokenById(int $userId, string $accessToken)
    {
        $em = $this->getEntityManager();

        $facebookUser =
            $em
                ->getRepository(\SocialBundle\Entity\FacebookUser::class)
                ->find($userId)
                ->setAccessToken($accessToken);

        $em->persist($facebookUser);
        $em->flush();
    }
}
