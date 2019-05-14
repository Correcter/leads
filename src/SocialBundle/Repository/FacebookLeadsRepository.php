<?php

namespace SocialBundle\Repository;

class FacebookLeadsRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return \SocialBundle\Entity\FacebookLeads
     */
    public function getLeads()
    {
        return $this->createQueryBuilder('ld')
            ->orderBy('ld.leadId', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param null|int $formId
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    public function getNotSendedLeadsDateRangeByFormId(int $formId = null)
    {
        return $this->createQueryBuilder('ld')
            ->select('MIN(ld.createdTime) AS min', 'MAX(ld.createdTime) AS max')
            ->where('ld.formId=:formId')
            ->andWhere('ld.sended=0')
            ->setParameter('formId', $formId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param null|int $formId
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    public function getNewLeadsByFormId(int $formId = null)
    {
        return $this->createQueryBuilder('ld')
            ->select('ld.fieldData', 'ld.createdTime')
            ->where('ld.formId=:formId')
            ->andWhere('ld.sended=0')
            ->setParameter('formId', $formId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param null|int $formId
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    public function getLastLeadByFormId(int $formId = null)
    {
        return $this->createQueryBuilder('ld')
            ->select('ld.leadId')
            ->where('ld.formId=:formId')
            ->setParameter('formId', $formId)
            ->orderBy('ld.createdTime', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int  $formId
     * @param bool $sended
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    public function setLeadSendedStatus(int $formId, bool $sended = false)
    {
        return
            $this->createQueryBuilder('ld')
                ->update()
                ->set('ld.sended', $sended)
                ->where('ld.formId=:formId')
                ->andWhere('ld.sended=0')
                ->setParameter('formId', $formId)
                ->getQuery()
                ->getResult();
    }

    /**
     * @param array $leadData
     */
    public function insertDataIntoLeadsTable(array $leadData = [])
    {
        $leadBlank = new \SocialBundle\Entity\FacebookLeads();

        $leadBlank->setId($leadData['leadId']);

        $leadBlank->setForm(
            $this->getEntityManager()
                ->getRepository(\SocialBundle\Entity\FacebookLeadgenForm::class)
                ->find($leadData['formId'])
        );

        $leadBlank->setFieldData($leadData['fieldData']);

        $this->getEntityManager()->persist($leadBlank);
        $this->getEntityManager()->flush($leadBlank);
    }
}
