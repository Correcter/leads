<?php

namespace SocialBundle\Repository;

class FacebookFormMailRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return \SocialBundle\Entity\FacebookFormMail
     */
    public function getFormMailsByFormId(int $formId = null)
    {
        return
            $this->createQueryBuilder('fm')
                ->select('fm')
                ->where('fm.formId=:formId')
                ->setParameter('formId', $formId)
                ->getQuery()
                ->getResult();
    }

    /**
     * @return \SocialBundle\Entity\FacebookFormMail
     */
    public function getNotSendedFormIdAndMails()
    {
        return
            $this->createQueryBuilder('fml')
                ->select('fml.email', 'fml.formId', 'ldf.formName')
                ->innerJoin('fml.forms', 'ldf')
                ->leftJoin('\\SocialBundle\\Entity\\Leads\\FacebookLeads', 'ld', 'WITH', 'ld.formId=fml.formId')
                ->where('ld.sended=0')
                ->groupBy('fml.formId')
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * @param array $params
     */
    public function insertIntoFormMails(array $params = [])
    {
        $formMail = new \SocialBundle\Entity\FacebookFormMail();

        $formMail->setForm(
            $this->getEntityManager()
                ->getRepository(\SocialBundle\Entity\FacebookLeadgenForm::class)
                ->find($params['form_id'])
        );

        $formMail->setEmail($params['mail']);

        $this->getEntityManager()->persist($formMail);
        $this->getEntityManager()->flush($formMail);
    }
}
