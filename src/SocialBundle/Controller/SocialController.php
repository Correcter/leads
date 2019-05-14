<?php

namespace SocialBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use SocialBundle\Event\SocialRequestEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;

/**
 * @author Vitaly Dergunov
 */
class SocialController extends AbstractController implements ServiceSubscriberInterface
{
    /**
     * @param Request                  $request
     * @param EventDispatcherInterface $dispatcher
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function socialAction(Request $request, EventDispatcherInterface $dispatcher)
    {
        $listenerType = $this->getParameter('listener.type');

        if (null === $listenerType) {
            return $this->json([
                'error' => 'Unknown social listener type',
            ]);
        }

        $socialRequestEvent = new SocialRequestEvent($request, $listenerType);

        $dispatcher->dispatch($request->attributes->get('event'), $socialRequestEvent);

        if ($socialRequestEvent->hasResponse()) {
            return $socialRequestEvent->getResponse();
        }

        return $this->json([
            'error' => 'No events where detected',
            Response::HTTP_NO_CONTENT,
        ]);
    }

    /**
     * @return array
     */
    public static function getSubscribedServices()
    {
        return [
            'router' => '?'.RouterInterface::class,
            'request_stack' => '?'.RequestStack::class,
            'http_kernel' => '?'.HttpKernelInterface::class,
            'security.authorization_checker' => '?'.AuthorizationCheckerInterface::class,
            'templating' => '?'.EngineInterface::class,
            'twig' => '?'.Environment::class,
            'doctrine' => '?'.ManagerRegistry::class,
            'form.factory' => '?'.FormFactoryInterface::class,
            'security.token_storage' => '?'.TokenStorageInterface::class,
            'security.csrf.token_manager' => '?'.CsrfTokenManagerInterface::class,
            'parameter_bag' => '?'.ContainerBagInterface::class,
        ];
    }
}
