<?php

/*
 * This file is part of the memLight project,
 * check it out on GitHub: https://github.com/federico-terzi/memLight
 *
 * Copyright (C) Federico Terzi 2017
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to menage User Login and Logout
 * 
 * @author Federico Terzi
 */
class SecurityController extends Controller
{
	/**
	 * Render the login page
	 * 
	 * @Route("/login", name="login")
	 */
	public function loginAction(Request $request)
	{
		// Get authentication utils service
		$authenticationUtils = $this->get('security.authentication_utils');
	
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
	
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();
		
		// Render the login form page
		return $this->render('security/login.html.twig', array(
				'last_username' => $lastUsername,
				'error'         => $error,
		));
	}
	
	/**
	 * Render the Logout behaviour
	 * 
	 * @Route("/logout", name="logout")
	 */
	public function logoutAction()
	{
		// Redirect to homepage
		return $this->redirectToRoute("homepage");
	}
}