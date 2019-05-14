<?php

namespace SocialBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use SocialBundle\Entity\VkontakteIncomingLead;
use SocialBundle\Event\SocialRequestEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vitaly Dergunov
 */
class VkontakteListener extends AbstractListener
{
    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * VkontakteListener constructor.
     *
     * @param ParameterBag             $parameters
     * @param LoggerInterface          $logger
     * @param \Twig_Environment        $twig
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ParameterBag $parameters,
        LoggerInterface $logger,
        \Twig_Environment $twig,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($logger, $entityManager, $dispatcher, $twig);

        $this->parameters = $parameters;
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onLeads(SocialRequestEvent $facebookEvent)
    {
        $facebookEvent->stopPropagation();

        $leadsResponse = [];

        $leadResult = $this
            ->entityManager
            ->getRepository(VkontakteIncomingLead::class)
            ->findBy([], [
                'leadId' => 'ASC',
            ], 100, (int) $facebookEvent->getRequest()->get('limit'));

        foreach ($leadResult as $lead) {
            $leadsResponse[] = [
                'leadId' => $lead->getLeadId(),
                'userId' => $lead->getUserId(),
                'formName' => $lead->getFormName(),
                'answers' => json_decode($lead->getAnswers(), true),
                'dateTime' => $lead->getDate(),
            ];
        }

        $facebookEvent->setResponse(
            new JsonResponse(
                $leadsResponse,
                Response::HTTP_OK
            )
        );
    }
}
