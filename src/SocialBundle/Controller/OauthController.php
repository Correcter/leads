<?php

namespace SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vitaly Dergunov
 */
class OauthController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        return $this->render('fb/views/index.html.twig');
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confidenceAction(Request $request)
    {
        return new JsonResponse([
            'confidence' => 'confidence',
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function redirectAction(Request $request)
    {
        return new JsonResponse([
            'redirect' => 'redirect',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function pageNotFoundAction()
    {
        return new JsonResponse('Page not found');
    }
}
