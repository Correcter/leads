<?php

namespace SocialBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Facebook\Facebook;
use Psr\Log\LoggerInterface;
use SocialBundle\Entity\FacebookUser;
use SocialBundle\Event\SocialRequestEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Vitaly Dergunov
 */
class FacebookListener extends AbstractListener
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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * FacebookListener constructor.
     *
     * @param Facebook                 $facebook
     * @param TokenStorageInterface    $tokenStorage
     * @param ParameterBag             $parameters
     * @param LoggerInterface          $logger
     * @param \Twig_Environment        $twig
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Facebook $facebook,
        TokenStorageInterface $tokenStorage,
        ParameterBag $parameters,
        LoggerInterface $logger,
        \Twig_Environment $twig,
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($logger, $entityManager, $dispatcher, $twig);

        $this->facebook = $facebook;
        $this->parameters = $parameters;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onPut(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        $userId = $facebookEvent->getRequest()->get('user_id');
        $userData = $this->findOneById($userId);

        if (null === $userData) {
            $userData = new FacebookUser();
            $userData->setId($userId);
            $userData->setUsername($facebookEvent->getRequest()->get('name'));
            $this->entityManager->persist($userData);
            $this->entityManager->flush();
        }

        $facebookEvent->setResponse(
            new JsonResponse(
                $userData,
                Response::HTTP_OK
            )
        );
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onSetPages(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        $facebookEvent->setResponse(
            new JsonResponse(
                $this->insertAndReturnPages($facebookEvent->getRequest()),
                Response::HTTP_OK
            )
        );
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onGetPages(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->setResponse(
            new JsonResponse(
                $this->getSubscribedPages(),
                Response::HTTP_OK
            )
        );
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onGetSubscription(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        $facebookEvent->setResponse(
            new JsonResponse(
                $this->getPagesByUserId(
                    $facebookEvent->getRequest()->get('user_id')
                ),
                Response::HTTP_OK
            )
        );
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onChangeSubscription(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        try {
            $this->setSubscription(
                $facebookEvent->getRequest()->get('user_id'),
                $facebookEvent->getRequest()->get('page_id'),
                $facebookEvent->getRequest()->get('value')
            );

            $facebookEvent->setResponse(
                new JsonResponse(
                    'success',
                    Response::HTTP_OK
                )
            );
        } catch (\Exception $e) {
            $this->logger->addDebug(
                $e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL
            );

            $facebookEvent->setResponse(
                new JsonResponse([
                    'error' => [
                        'message' => $e->getMessage(),
                    ],
                ], Response::HTTP_ACCEPTED)
            );
        }
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onChangeFormStatus(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $this->setFormEnabledStatus(
            (int) $facebookEvent->getRequest()->get('form_id'),
            (int) $facebookEvent->getRequest()->get('enabled')
        );

        $facebookEvent->setResponse(
            new JsonResponse(
                'success',
                Response::HTTP_OK
            )
        );
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onGetForms(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        $leadForms = [];
        $formIds = [];

        $pageId = $facebookEvent->getRequest()->get('page_id');

        $this->facebook->setDefaultAccessToken(
            $this->getActualUserTokenById($facebookEvent->getRequest()->get('user_id'))
        );

        $facebookPageData =
            $this->facebook
                ->getClient()
                ->sendRequest(
                    $this->facebook->request('get', '/'.$pageId.'?fields=id,name,access_token')
            )->getDecodedBody();

        if (!isset($facebookPageData['access_token'])) {
            throw new \RuntimeException('Graph API did not return a page token');
        }

        $this->facebook->setDefaultAccessToken($facebookPageData['access_token']);

        try {
            $leadGenFormsData = $this->getEnabledFormsByPageId($pageId);

            foreach ($leadGenFormsData as $form) {
                $formIds[$form['formId']] = $form['formId'];
            }

            do {
                $leadFormsByPage =
                    $this->facebook
                        ->getClient()
                        ->sendRequest(
                            $this->facebook->request('get', '/'.$pageId.'/leadgen_forms?limit=1000')
                        )->getGraphEdge();

                foreach ($leadFormsByPage->asArray() as $leadForm) {
                    $leadForms[] = [
                        'id' => $leadForm['id'],
                        'name' => $leadForm['name'],
                        'status' => $leadForm['status'],
                        'mails' => $this->getFormMailsByFormId($leadForm['id']),
                    ];

                    if (isset($formIds[$leadForm['id']])) {
                        continue;
                    }

                    $this->insertForm([
                        'formId' => $leadForm['id'],
                        'pageId' => $pageId,
                        'formName' => $leadForm['name'],
                        'enabled' => true,
                    ]);
                }
            } while (null !== $leadFormsByPage->getNextPageRequest());

            $facebookEvent->setResponse(
                new JsonResponse([
                    'success' => [
                        'name' => $this->getPageById($pageId)->getPageName(),
                        'forms' => $leadForms,
                    ],
                ], Response::HTTP_OK)
            );
        } catch (\Exception $e) {
            $this->logger->addDebug(
                $e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL
            );

            $facebookEvent->setResponse(
                new JsonResponse([
                    'error' => $e->getMessage(),
                ], Response::HTTP_ACCEPTED)
            );
        }
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onAddMail(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        try {
            if (null !== $this->entityManager
                ->getRepository(\SocialBundle\Entity\FacebookFormMail::class)
                ->findOneBy([
                        'email' => $facebookEvent->getRequest()->get('data')['mail'],
                        'formId' => $facebookEvent->getRequest()->get('data')['form_id'],
                    ])) {
                $facebookEvent->setResponse(
                    new JsonResponse([
                        'error' => 'Email duplicate',
                    ], Response::HTTP_ACCEPTED)
                );

                return;
            }

            $this->insertIntoFormMails(
                $facebookEvent->getRequest()->get('data')
            );

            $facebookEvent->setResponse(
                new JsonResponse(
                    'success',
                    Response::HTTP_OK
                )
            );
        } catch (\Exception $e) {
            $this->logger->addDebug(
                $e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL
            );

            $facebookEvent->setResponse(
                new JsonResponse([
                    'error' => $e->getMessage(),
                ], Response::HTTP_ACCEPTED)
            );
        }
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onDeleteMail(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        try {
            $emailData = $this->entityManager
                ->getRepository(\SocialBundle\Entity\FacebookFormMail::class)
                ->findOneBy([
                    'email' => $facebookEvent->getRequest()->get('data')['mail'],
                    'formId' => $facebookEvent->getRequest()->get('data')['form_id'],
                ]);

            if (null !== $emailData) {
                $this->entityManager
                    ->remove(
                        $emailData
                    );
                $this->entityManager->flush();
            }

            $facebookEvent->setResponse(
                new JsonResponse(
                    'success',
                    Response::HTTP_OK
                )
            );
        } catch (\Exception $e) {
            $this->logger->addDebug(
                $e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL
            );

            $facebookEvent->setResponse(
                new JsonResponse([
                    'error' => $e->getMessage(),
                ], Response::HTTP_ACCEPTED)
            );
        }
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onGrabToken(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->stopPropagation();

        $this->facebook->setDefaultAccessToken(
            $this->parameters->get('facebookAccessToken')
        );

        try {
            $accessToken = $this->facebook->getJavaScriptHelper()->getAccessToken()->getValue();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            $accessToken = $this->getActualUserTokenById(
                $facebookEvent->getRequest()->get('user_id')
            );
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $facebookEvent->setResponse(
                new Response(
                    'Facebook SDK returned an error: '.$e->getMessage(),
                    Response::HTTP_BAD_REQUEST
                )
            );

            return;
        }

        if (!isset($accessToken)) {
            $facebookEvent->setResponse(
                new Response(
                    'No cookie set or no OAuth data could be obtained from cookie.',
                    Response::HTTP_BAD_REQUEST
                )
            );

            return;
        }

        $facebookData = $this->facebook
            ->sendRequest(
                    'get',
                    '/oauth/access_token',
                    [
                        'grant_type' => 'fb_exchange_token',
                        'client_id' => $this->parameters->get('facebookAppId'),
                        'client_secret' => $this->parameters->get('facebookAppSecret'),
                        'fb_exchange_token' => $accessToken,
                    ]
                )->getDecodedBody();

        if (!isset($facebookData['access_token'])) {
            $facebookEvent->setResponse(
                new Response(
                    'Access token was not returned from Graph ',
                    Response::HTTP_BAD_REQUEST
                )
            );
        }

        $this->setUserTokenById(
            $facebookEvent->getRequest()->get('user_id'),
            $facebookData['access_token']
        );

        $facebookEvent->setResponse(
            new Response(
                'success',
                Response::HTTP_OK
            )
        );
    }

    /**
     * @param SocialRequestEvent $facebookEvent
     */
    public function onConfidence(SocialRequestEvent $facebookEvent)
    {
        if ($facebookEvent->getListener() !== $this->parameters->get('currentListener')) {
            return;
        }

        $facebookEvent->setResponse(
            new Response('Confidence policy')
        );
    }

    /**
     * @param $userId
     *
     * @return mixed
     */
    protected function getActualUserTokenById(int $userId)
    {
        $userData = $this->getUserTokenById($userId);

        if (!$userData['accessToken']) {
            throw new \RuntimeException('User token is empty');
        }

        $this->facebook->setDefaultAccessToken(
            $this->parameters->get('facebookAppToken')
        );

        $debugTokenResponse = $this->facebook->sendRequest(
            'get',
            '/debug_token',
            [
                'input_token' => $userData['accessToken'],
            ]
        );

        $tokenData = $debugTokenResponse->getDecodedBody();

        if (false !== $tokenData['data']['is_valid']) {
            return $userData['accessToken'];
        }

        throw new \RuntimeException($tokenData['data']['error']['message']);
    }
}
