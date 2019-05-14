<?php

namespace SocialBundle\Repository;

class FacebookLeadgenFormRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param mixed $accessToken
     *
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function setFormEnabledStatus(int $formId, bool $enabled = false)
    {
        return
            $this->createQueryBuilder('frm')
                ->update()
                ->set('frm.enabled', $enabled)
                ->where('fbu.id=:formId')
                ->setParameter('formId', $formId)
                ->getQuery()
                ->getResult();
    }

    /**
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function getEnabledFormsIds()
    {
        return
            $this->createQueryBuilder('ldf')
                ->select('ldf.id as formId')
                ->where('ldf.enabled=1')
                ->orderBy('ldf.id', 'ASC')
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * @return \SocialBundle\Entity\FacebookLeadgenForm
     */
    public function getEnabledFormsByPageId(int $pageId)
    {
        return
            $this->createQueryBuilder('ldf')
                ->select('ldf.id as formId')
                ->where('ldf.pageId=:pageId')
                ->setParameter('pageId', $pageId)
                ->orderBy('ldf.id', 'ASC')
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * @param array $params
     */
    public function insertForm(array $params = [])
    {
        $formBlank = new \SocialBundle\Entity\FacebookLeadgenForm();

        $formBlank->setId($params['formId']);

        $formBlank->setPage(
            $this->getEntityManager()
                ->getRepository(\SocialBundle\Entity\FacebookPage::class)
                ->find($params['pageId'])
        );

        $formBlank->setFormName($params['formName']);
        $formBlank->setEnabled($params['enabled']);

        $this->getEntityManager()->persist($formBlank);
        $this->getEntityManager()->flush($formBlank);
    }
}
