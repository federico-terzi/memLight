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

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This entity is used to store User Settings
 * 
 * @ORM\Entity
 * @ORM\Table(name = "user_settings")
 * @author Federico Terzi
 *
 */
class UserSettings
{

	/**
	 * @ORM\Id
	 * @ORM\OneToOne(targetEntity="User", inversedBy="settings")
	 */
	private $user;
	
	/**
	 * If this is set to TRUE, the quiz will remind the user to continue a quiz
	 * 
	 * @ORM\Column(type="boolean")
	 */
	private $enable_reminder;

	public function __construct()
	{
		$this->enable_reminder = true;
	}

    /**
     * Set enable_reminder
     *
     * @param boolean $enableReminder
     * @return UserSettings
     */
    public function setEnableReminder($enableReminder)
    {
        $this->enable_reminder = $enableReminder;

        return $this;
    }

    /**
     * Get enable_reminder
     *
     * @return boolean 
     */
    public function getEnableReminder()
    {
        return $this->enable_reminder;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return UserSettings
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
