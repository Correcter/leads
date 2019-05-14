<?php

namespace SocialBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vitaly Dergunov
 */
abstract class AbstractListener
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * AbstractListener constructor.
     *
     * @param LoggerInterface          $logger
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $dispatcher
     * @param \Twig_Environment        $twig
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher,
        \Twig_Environment $twig
    ) {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param null|int $userId
     *
     * @return mixed
     */
    public function getPagesByUserId(int $userId = null)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookPage::class)
            ->getPagesByUserId($userId);
    }

    /**
     * @return bool
     */
    protected function isDebug()
    {
        return true;
    }

    /**
     * @return mixed
     */
    protected function getSubscribedPages()
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookPage::class)
            ->getSubscribedPages();
    }

    /**
     * @param array $formIds
     *
     * @return mixed
     */
    protected function getSubscribedFormsEmails(array $formIds = [])
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookFormMail::class)
            ->getSubscribedFormsEmailsByFormIds($formIds);
    }

    /**
     * @param null|int $userId
     * @param null|int $pageId
     * @param bool     $subscribed
     *
     * @return mixed
     */
    protected function setSubscription(int $userId = null, int $pageId = null, int $subscribed = null)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookPage::class)
            ->setSubscription($userId, $pageId, $subscribed);
    }

    /**
     * @param int $userId
     *
     * @return mixed
     */
    protected function findOneById(int $userId)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookUser::class)
            ->findOneById($userId);
    }

    /**
     * @param int    $userId
     * @param string $accessToken
     *
     * @return mixed
     */
    protected function setUserTokenById(int $userId, string $accessToken)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookUser::class)
            ->setUserTokenById($userId, $accessToken);
    }

    /**
     * @param int $userId
     *
     * @return mixed
     */
    protected function getUserTokenById(int $userId)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookUser::class)
            ->getUserTokenById($userId);
    }

    /**
     * @param Request $request
     * @param null    $userId
     *
     * @return array
     */
    protected function insertAndReturnPages(Request $request): array
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookPage::class)
            ->insertAndReturnPages($request);
    }

    /**
     * @param int  $formId
     * @param bool $enabled
     *
     * @return mixed
     */
    protected function setFormEnabledStatus(int $formId, bool $enabled)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookLeadgenForm::class)
            ->setFormEnabledStatus($formId, $enabled);
    }

    /**
     * @param null|int $pageId
     *
     * @return mixed
     */
    protected function getEnabledFormsByPageId(int $pageId = null)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookLeadgenForm::class)
            ->getEnabledFormsByPageId($pageId);
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    protected function insertForm(array $params = [])
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookLeadgenForm::class)
            ->insertForm($params);
    }

    /**
     * @param null|int $formId
     *
     * @return mixed
     */
    protected function getFormMailsByFormId(int $formId = null)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookFormMail::class)
            ->getFormMailsByFormId($formId);
    }

    /**
     * @param null|int $pageId
     *
     * @return mixed
     */
    protected function getPageById(int $pageId = null)
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookPage::class)
            ->getPageById($pageId);
    }

    /**
     * @param array $mailsRequestData
     */
    protected function insertIntoFormMails(array $mailsRequestData = [])
    {
        return $this->entityManager
            ->getRepository(\SocialBundle\Entity\FacebookFormMail::class)
            ->insertIntoFormMails($mailsRequestData);
    }
}
