<?php

namespace SocialBundle\Command;

/**
 * @author Vitaly Dergunov (<v.dergunov@icontext.ru>)
 */
abstract class AbstractCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /**
     * @return mixed
     */
    public function getSubscribedPages()
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookPage::class)
            ->getSubscribedPages();
    }

    /**
     * @param array $subscribedPages
     *
     * @return mixed
     */
    public function getSubscribedFormsEmails(array $subscribedPages = [])
    {
        $formIds = [];

        foreach ($subscribedPages as $pageData) {
            $formIds[] = $pageData['forms']['id'];
        }

        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookLeadgenForm::class)
            ->getSubscribedFormsEmailsByFormIds($formIds);
    }

    /**
     * @return mixed
     */
    public function getCallKeeperForms()
    {
        return
            $this->getDoctrine()
                ->getRepository(\SocialBundle\Entity\FacebookFormWidget::class)
                ->getNotSendedFormIdWithWidgetCompare();
    }

    /**
     * @return array \SocialBundle\Entity\FacebookLeadgenForm
     */
    protected function getNotSendedFormIdAndMails()
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookFormMail::class)
            ->getNotSendedFormIdAndMails();
    }

    /**
     * @param $formId
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    protected function getNotSendedLeadsDateRangeByFormId(int $formId)
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookLeads::class)
            ->getNotSendedLeadsDateRangeByFormId($formId);
    }

    /**
     * @param $formId
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    protected function getNewLeadsByFormId(int $formId)
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookLeads::class)
            ->getNewLeadsByFormId($formId);
    }

    /**
     * @param $formId
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    protected function setLeadSendedStatus(int $formId)
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookLeads::class)
            ->setLeadSendedStatus($formId);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return \Generator
     */
    protected function getEnabledFormsIds()
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookLeadgenForm::class)
            ->getEnabledFormsIds();
    }

    /**
     * @param $formId
     *
     * @return \SocialBundle\Entity\FacebookLeads
     */
    protected function getLastLeadByFormId(int $formId)
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookLeads::class)
            ->getLastLeadByFormId($formId);
    }

    /**
     * @param int $userId
     *
     * @return mixed
     */
    protected function getUserTokenById(int $userId)
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookUser::class)
            ->getUserTokenById($userId);
    }

    /**
     * @param array $leadData
     *
     * @return mixed
     */
    protected function insertDataIntoLeadsTable(array $leadData = [])
    {
        return $this->getManager()
            ->getRepository(\SocialBundle\Entity\FacebookLeads::class)
            ->insertDataIntoLeadsTable($leadData);
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    protected function getLogger()
    {
        return $this->getContainer()->get('console.logger');
    }

    /**
     * @return \Facebook\Facebook
     */
    protected function getFacebook()
    {
        return $this->getContainer()->get('facebook.service');
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected function getManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return string
     */
    protected function getRootDir(): string
    {
        return $this->getContainer()->get('kernel')->getRootDir();
    }

    /**
     * @return string
     */
    protected function getProjectDir(): string
    {
        return $this->getContainer()->get('kernel')->getProjectDir();
    }

    /**
     * @return string
     */
    protected function getEnvironment(): string
    {
        return $this->getContainer()->get('kernel')->getEnvironment();
    }

    /**
     * @return bool
     */
    protected function isDebug(): bool
    {
        return $this->getContainer()->get('kernel')->isDebug();
    }

    /**
     * @return mixed
     */
    protected function getActualAccessToken()
    {
        $this->getFacebook()->setDefaultAccessToken(
            $this->getContainer()->getParameter('facebook.app.token')
        );

        $facbookResponse = $this->getFacebook()->sendRequest(
            'get',
            '/debug_token',
            [
                'input_token' => $this->getContainer()->getParameter('facebook.access.token'),
            ]
        );

        $facebookData = $facbookResponse->getDecodedBody();

        if (false === $facebookData['data']['is_valid']) {
            $userData = $this->getUserTokenById($facebookData['data']['user_id']);

            if (!$userData) {
                throw new \RuntimeException('Please update the temporary token to parameter configuration');
            }

            return $userData['accessToken'];
        }

        return $this->getContainer()->getParameter('facebook.access.token');
    }
}
