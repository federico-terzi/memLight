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

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\UserSettings;

/**
 * Service used to menage User Settings
 * 
 * @author Federico Terzi
 */
class SettingServices {
	// Used to store the Doctrine instance
	private $doctrine;

	function __construct($doctrine) {
		// Inject doctrine ( check config.yml )
		$this->doctrine = $doctrine;
	}
	
	/**
	 * Create a new UserSettings instance for the specified user
	 * 
	 * @param User $user
	 * @return \AppBundle\Entity\UserSettings
	 */
	public function createSettingsForUser(User $user)
	{
		// Create a new UserSettings for the user
		$settings = new UserSettings();
		$settings->setUser($user);
			
		// Save it to the database
		$em = $this->doctrine->getManager();
		$em->persist($settings);
		$em->flush();
		
		// Return the created UserSettings instance
		return $settings;
	}
	
	/**
	 * Return the UserSettings for the specified user
	 * 
	 * @param User $user
	 * @return \AppBundle\Entity\UserSettings
	 */
	public function getSettings(User $user)
	{
		// Check if user has already a user setting
		$repository = $this->doctrine->getRepository("AppBundle:UserSettings");
		$settings = $repository->findOneBy(array('user'=>$user));
		
		// If the user doesn't have any user setting, create a new instance
		if(!$settings)
		{
			$settings = $this->createSettingsForUser($user);
		}
		
		// Return the UserSettings
		return $settings;
	}
	
	/**
	 * Delete all recorded answers for the specified user ( progress data )
	 * 
	 * @param User $user
	 */
	public function deleteAllRecordedAnswers(User $user)
	{
		// Get all the recorded answers for the specified User
		$repository = $this->doctrine->getRepository("AppBundle:RecordedAnswer");
		$answers = $repository->findBy(array('user'=>$user));
		
		// Get the Doctrine manager
		$em = $this->doctrine->getManager();
		
		// Loop through each answer and remove it
		foreach ($answers as $answer) {
			$em->remove($answer);
		}
		
		// Commit the changes to the database
		$em->flush();
	}

}