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
use AppBundle\Entity\UserSettings;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\SettingsType;

/**
 * Controller used to menage User Settings
 * 
 * @author Federico Terzi
 */
class SettingsController extends Controller
{
	/**
	 * Render settings form and menage the submissions
	 * 
	 * @Route("/settings/", name="settings")
	 */
	public function settingsAction(Request $request)
	{
		// Check if the user is logged in, if not, block access
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
		
		// Get the User instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		// Get the message data from the GET request
		$message = $request->get("message");
		
		// Get the Setting Service
		$settingServices = $this->get('setting_services');
		
		// Get user settings
		$userSettings = $settingServices->getSettings($user);
		
		// If there are no settings, create a new UserSetting object
		if(!$userSettings)
		{
			$userSettings = new UserSettings();
		}
		
		// Get the Doctrine manager
		$em = $this->getDoctrine()->getManager();
		
		// Build the form
		$form = $this->createForm(SettingsType::class, $userSettings);
		
		// Handle the submit POST request
		$form->handleRequest($request);
		
		// When the form is submitted
		if ($form->isSubmitted() && $form->isValid()) {
			// Set the user
			$userSettings->setUser($user);
			
			//Persist the UserSettings to the database
			$em->merge($userSettings);
			$em->flush();
				
			// Redirect to settings page
			return $this->redirectToRoute('settings');
		}
		
		// Pass the form and render the page
		return $this->render(
				'settings/settings.html.twig',
				array('form' => $form->createView(), 'message' => $message)
				);
	}
	
	/**
	 * Action to delete user's recorded answers ( progress data )
	 * 
	 * @Route("/settings/delete_recorded_answers/", name="delete_recorded_answers")
	 */
	public function deleteRecordedAnswersAction()
	{
		// Check if the user is logged in, if not, block access
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
		
		// Get the User instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		// Delete all the recorded answers for the specified user
		$this->get('setting_services')->deleteAllRecordedAnswers($user);
	
		// Redirect to settings page
		return $this->redirectToRoute("settings", array('message'=>'Progress successfully deleted!'));
	}
}
