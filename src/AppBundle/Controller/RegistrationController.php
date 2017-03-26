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

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Controller used to menage User Registration
 * 
 * @author Federico Terzi
 */
class RegistrationController extends Controller
{
	/**
	 * Render the form and menage User Registration
	 * 
	 * @Route("/register", name="user_registration")
	 */
	public function registerAction(Request $request)
	{
		// Create a new User object
		$user = new User();
		
		// Build the Form
		$form = $this->createForm(UserType::class, $user);

		// Handle the submit POST request
		$form->handleRequest($request);
		
		// When the form is submitted
		if ($form->isSubmitted() && $form->isValid()) {

			// Encode the password
			$password = $this->get('security.password_encoder')
			->encodePassword($user, $user->getPlainPassword());
			$user->setPassword($password);

			// Get the Doctrine manager
			$em = $this->getDoctrine()->getManager();
			
			// Persist the new user to the database
			$em->persist($user);
			$em->flush();
			
			// Get the setting service
			$settingServices = $this->get("setting_services");
			
			// Create a new setting for the user
			$settingServices->createSettingsForUser($user);
			
			// Set the User token, so the user doesn't have to login
			$token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
			$this->get('security.token_storage')->setToken($token);
			$this->get('session')->set('_security_main', serialize($token));
			
			// Redirect to the homepage
			return $this->redirectToRoute('homepage');
		}

		// Pass the form and render the page
		return $this->render(
				'security/register.html.twig',
				array('form' => $form->createView())
				);
	}
}