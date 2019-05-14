<?php

namespace SocialBundle\Repository;

use Symfony\Component\HttpFoundation\Request;

class FacebookPageRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return \SocialBundle\Entity\FacebookPage
     */
    public function getSubscribedPages()
    {
        return
            $this->createQueryBuilder('pg')
                ->select('pg', 'ldf', 'fm')
                ->leftJoin('pg.forms', 'ldf')
                ->leftJoin('ldf.formMails', 'fm')
                ->where('pg.subscribed=1')
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * @return \SocialBundle\Entity\FacebookPage
     */
    public function setSubscription(int $userId, int $pageId, int $subscribed)
    {
        return
            $this->createQueryBuilder('pg')
                ->update()
                ->set('pg.subscribed', (int) $subscribed)
                ->where('pg.id='.$pageId)
                ->andWhere('pg.userId='.$userId)
                ->getQuery()
                ->getResult();
    }

    /**
     * @return array
     */
    public function getPagesByUserId(int $userId = null)
    {
        return
            $this->createQueryBuilder('pg')
                ->select('pg')
                ->where('pg.userId=:userId')
                ->setParameter('userId', $userId)
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * @return array
     */
    public function getPageById(int $pageId = null)
    {
        return
            $this->createQueryBuilder('pg')
                ->select('pg')
                ->where('pg.id=:pageId')
                ->setParameter('pageId', $pageId)
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function insertAndReturnPages(Request $request): array
    {
        $facebookResult = [];
        $pageIds = [];

        $facebookPage = new \SocialBundle\Entity\FacebookPage();

        $em = $this->getEntityManager();

        $userId = $request->get('user_id');
        $pages = $this->getPagesByUserId($userId);

        foreach ($pages as $p) {
            $pageIds[$p['id']] = $p;
        }

        foreach ($data = $request->get('data', []) as $pageItem) {
            $facebookResult[$pageItem['id']] = [
                'id' => $pageItem['id'],
                'user_id ' => $userId,
                'name' => strip_tags(trim($pageItem['name'])),
                'access_token' => $pageItem['access_token'],
                'subscribed' => $pageIds[$pageItem['id']]['subscribed'] ?? 0,
            ];

            if (isset($pageIds[$pageItem['id']])) {
                continue;
            }

            $facebookPage->setId($pageItem['id']);

            $facebookPage->setUser(
                $this->getEntityManager()
                    ->getRepository(\SocialBundle\Entity\FacebookUser::class)
                    ->find($userId)
            );

            $facebookPage->setPageName(strip_tags(trim($pageItem['name'])));
            $facebookPage->setAccessToken($pageItem['access_token']);
            $facebookPage->setSubscribed(0);

            $em->persist($facebookPage);
            $em->flush();
        }

        return $facebookResult;
    }
}
