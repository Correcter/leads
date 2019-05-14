<?php

namespace SocialBundle\Repository;

class FacebookFormWidgetRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return \SocialBundle\Entity\FacebookFormWidget
     */
    public function getNotSendedFormIdWithWidgetCompare()
    {
        return
            $this->createQueryBuilder('wdg')
                ->select('wdg', 'ld')
                ->innerJoin('wdg.leads', 'ld')
                ->where('ld.sended=0')
                ->getQuery()
                ->getArrayResult();
    }
}
