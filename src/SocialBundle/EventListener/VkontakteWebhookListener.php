<?php

namespace SocialBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use SocialBundle\Entity\VkontakteIncomingLead;
use SocialBundle\Event\SocialRequestEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vitaly Dergunov
 */
class VkontakteWebhookListener extends AbstractListener
{
    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * WebhookListener constructor.
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
     * @param SocialRequestEvent $vkontakteEvent
     */
    public function onVkLeadReceived(SocialRequestEvent $vkontakteEvent)
    {
        $vkontakteEvent->stopPropagation();

        $requestContent = json_decode($vkontakteEvent->getRequest()->getContent());

        if ($this->isConfirmation($requestContent)) {
            return $this->getConfigurationForGroupById($requestContent->group_id);
        }

        // if not lead return response
        if ('lead_forms_new' !== $requestContent->type) {
            $vkontakteEvent->setResponse(
                new Response($this->parameters->get('VkResponseOk'), Response::HTTP_OK)
            );

            return null;
        }

        $incomingLead = $requestContent->object;

        // calculate unique event identifier
        $vkLeadId = crc32($incomingLead->group_id.$incomingLead->user->id.$incomingLead->form_id.$incomingLead->lead_id);

        // if such event aready exists return response ok
        if (null === $this->entityManager
            ->getRepository(VkontakteIncomingLead::class)
            ->find($vkLeadId)) {
            // If no exception while dispatching event
            $this->entityManager->persist(
                (new VkontakteIncomingLead())
                    ->setId($vkLeadId)
                    ->setLeadId($incomingLead->lead_id)
                    ->setGroupId($incomingLead->group_id)
                    ->setUserId($incomingLead->user_id)
                    ->setFormId($incomingLead->form_id)
                    ->setFormName($incomingLead->form_name)
                    ->setAnswers($incomingLead->answers)
            );
            $this->entityManager->flush();

            $this->logger->info('Vk Lead was saved sucessfully');
        }

        $this->logger->info('Entity duplicated');

        $vkontakteEvent->setResponse(
            new Response($this->parameters->get('vk.response.ok'), Response::HTTP_OK)
        );
    }

    /**
     * @param resource|string The request body content or a resource to read the body stream
     *
     * @return bool
     */
    protected function isConfirmation(object $requestContent)
    {
        if (!is_numeric($requestContent->group_id)) {
            throw new \InvalidArgumentException('Argument must be numeric');
        }

        if (
            'confirmation' === $requestContent->type &&
            isset($this->parameters->get('vkCatchConfig')[$requestContent->group_id])
        ) {
            return $this->parameters->get('vkCatchConfig')[$requestContent->group_id];
        }

        return false;
    }

    /**
     * @param int $groupId
     *
     * @return array
     */
    protected function getConfigurationForGroupById(int $groupId)
    {
        if (isset($this->parameters->get('vkCatchConfig')[$groupId]['confirmation'])) {
            return $this->parameters->get('vkCatchConfig')[$groupId]['confirmation'];
        }

        return null;
    }
}
