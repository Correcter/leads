<?php

namespace SocialBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Facebook\Facebook;
use Psr\Log\LoggerInterface;
use SocialBundle\Event\SocialRequestEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vitaly Dergunov
 */
class FacebookWebhookListener extends AbstractListener
{
    /**
     * @var \Facebook\Facebook
     */
    private $facebook;

    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * WebhookListener constructor.
     *
     * @param Facebook                 $facebook
     * @param ParameterBag             $parameters
     * @param LoggerInterface          $logger
     * @param \Twig_Environment        $twig
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Facebook $facebook,
        ParameterBag $parameters,
        LoggerInterface $logger,
        \Twig_Environment $twig,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($logger, $entityManager, $dispatcher, $twig);

        $this->parameters = $parameters;
        $this->facebook = $facebook;
    }

    /**
     * @param SocialRequestEvent $webhookEvent
     */
    public function onFacebookWebhook(SocialRequestEvent $webhookEvent)
    {
        $webhookEvent->stopPropagation();

        if ($this->isVerifyFavebookWebhookAdress($webhookEvent)) {
            return;
        }

        $webhookRequest = $webhookEvent->getRequest();

        if (false === strpos($webhookRequest->headers->get('Content-Type'), 'application/json')) {
            $webhookEvent->setResponse(
                new Response('The request does not contain valid JSON', Response::HTTP_NO_CONTENT)
            );

            return;
        }

        $facebookData = $webhookRequest->getContent();

        if (!$facebookData) {
            $webhookEvent->setResponse(
                new Response('The request is empty', Response::HTTP_NO_CONTENT)
            );

            return;
        }

        list($method, $signature) = explode('=', $webhookRequest->headers->get('x-hub-signature'));

        if ($signature === hash_hmac($method, $facebookData, $this->parameters->get('facebookAppSecret'))) {
            $this->logger->info('Signature verification SUCCESS');

            $this->saveFacebookLeadChangesIfNotExsists(
                json_decode($facebookData, true, 512, JSON_BIGINT_AS_STRING)
            );
        } else {
            $this->logger->alert('Signature verification ERROR');

            $webhookEvent->setResponse(
                new Response('Unauthorized request', Response::HTTP_UNAUTHORIZED)
            );

            return;
        }

        $webhookEvent->setResponse(
            new Response('Cool beans!', Response::HTTP_OK)
        );
    }

    /**
     * @param SocialRequestEvent $webhookEvent
     *
     * @return bool
     */
    protected function isVerifyFavebookWebhookAdress(SocialRequestEvent $webhookEvent)
    {
        $webhookRequest = $webhookEvent->getRequest();

        if ($webhookRequest->query->has('hub_challenge') &&
            $webhookRequest->query->has('hub_verify_token')) {
            $verifyToken = $webhookRequest->get('hub_verify_token');
            $challenge = $webhookRequest->get('hub_challenge');

            $this->logger->debug('Recived veirfy token : '.$verifyToken);

            $this->logger->debug('Recived challenge : '.$challenge);

            if ($this->parameters->get('facebookVerifyToken') === $verifyToken) {
                $webhookEvent->setResponse(
                    new Response($challenge)
                );

                return true;
            }

            $webhookEvent->setResponse(
                new Response('Unauthorized request', Response::HTTP_UNAUTHORIZED)
            );

            return false;
        }

        return false;
    }

    /**
     * @param array $facebookData
     */
    protected function saveFacebookLeadChangesIfNotExsists(array $facebookData = [])
    {
        foreach ($facebookData['entry'] as $entryData) {
            if (!isset($entryData['changes'])) {
                continue;
            }

            foreach ($entryData['changes'] as $entryFields) {
                $leadValues = &$entryFields['value'];

                unset($leadValues['created_time']);

                if (!$this
                    ->entityManager
                    ->getRepository(\SocialBundle\Entity\FacebookEntryHash::class)
                    ->findOneBy(
                        [
                            'id' => $entryData['id'],
                            'hash' => $hash = md5(json_encode($entryFields)),
                        ]
                    )
                ) {
                    $leadGenChange = new \SocialBundle\Entity\FacebookLeadgenchange();

                    $leadGenChange->setFormId($leadValues['form_id'])
                        ->setFieldName($entryFields['field'])
                        ->setAdgroupId($leadValues['adgroup_id'])
                        ->setAdId($leadValues['ad_id'])
                        ->setLeadgenId($leadValues['leadgen_id'])
                        ->setPageId($leadValues['page_id'])
                        ->setCreatedTime(new \DateTime());

                    $this->entityManager->persist($leadGenChange);

                    $hashObject = new \SocialBundle\Entity\FacebookEntryHash();

                    $hashObject
                        ->setId($entryData['id'])
                        ->setHash($hash)
                        ->setTime($entryData['time']);

                    $this->entityManager->persist($hashObject);
                    $this->entityManager->flush();
                } else {
                    $this->logger->info('Entry duplicate');
                }
            }
        }

        $this->logger->info('All changes are saved');
    }
}
