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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * This entity is used to store User information
 * 
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity("username")
 */
class User implements UserInterface, \Serializable
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=25, unique=true)
	 */
	private $username;

	/**
	 * @ORM\Column(type="string", length=64)
	 */
	private $password;
	
	/**
	 * @Assert\NotBlank()
	 * @Assert\Length(max=4096)
	 */
	private $plainPassword;

	/**
	 * @ORM\Column(name="is_active", type="boolean")
	 */
	private $isActive;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $roles;
	
	/**
	 * @ORM\OneToMany(targetEntity="Course", mappedBy="author", cascade={"remove"})
	 */
	private $coursesCreated;
	
	/**
	 * @ORM\OneToOne(targetEntity="UserSettings", mappedBy="user", cascade={"remove"})
	 */
	private $settings;
	
	/**
	 * @ORM\OneToMany(targetEntity="RecordedAnswer", mappedBy="user", cascade={"remove"})
	 */
	private $recordedAnswers;
	
	/**
	 * @ORM\OneToMany(targetEntity="SavedQuiz", mappedBy="user", cascade={"remove"})
	 */
	private $savedQuizzes;

	public function __construct()
	{
		$this->isActive = true;
		$this->roles = "ROLE_USER";
		// may not be needed, see section on salt below
		// $this->salt = md5(uniqid(null, true));
	}
	
	public function getDisplayName()
	{
        return $this->username;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getSalt()
	{
		// you *may* need a real salt depending on your encoder
		// see section on salt below
		return null;
	}

	public function getPassword()
	{
		return $this->password;
	}

	public function getRoles()
	{
		return explode(";",$this->roles);
	}

	public function eraseCredentials()
	{
	}

	/** @see \Serializable::serialize() */
	public function serialize()
	{
		return serialize(array(
				$this->id,
				$this->username,
				$this->password,
				// see section on salt below
				// $this->salt,
		));
	}

	/** @see \Serializable::unserialize() */
	public function unserialize($serialized)
	{
		list (
				$this->id,
				$this->username,
				$this->password,
				// see section on salt below
				// $this->salt
				) = unserialize($serialized);
	}

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set roles
     *
     * @param string $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string 
     */
    public function getFullName()
    {
        return $this->fullName;
    }
    
	public function getPlainPassword() {
		return $this->plainPassword;
	}
	
	public function setPlainPassword($plainPassword) {
		$this->plainPassword = $plainPassword;
		return $this;
	}
	

    /**
     * Add coursesCreated
     *
     * @param \AppBundle\Entity\Course $coursesCreated
     * @return User
     */
    public function addCoursesCreated(\AppBundle\Entity\Course $coursesCreated)
    {
        $this->coursesCreated[] = $coursesCreated;

        return $this;
    }

    /**
     * Remove coursesCreated
     *
     * @param \AppBundle\Entity\Course $coursesCreated
     */
    public function removeCoursesCreated(\AppBundle\Entity\Course $coursesCreated)
    {
        $this->coursesCreated->removeElement($coursesCreated);
    }

    /**
     * Get coursesCreated
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCoursesCreated()
    {
        return $this->coursesCreated;
    }

    /**
     * Set settings
     *
     * @param \AppBundle\Entity\UserSettings $settings
     * @return User
     */
    public function setSettings(\AppBundle\Entity\UserSettings $settings = null)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings
     *
     * @return \AppBundle\Entity\UserSettings 
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Add recordedAnswers
     *
     * @param \AppBundle\Entity\RecordedAnswer $recordedAnswers
     * @return User
     */
    public function addRecordedAnswer(\AppBundle\Entity\RecordedAnswer $recordedAnswers)
    {
        $this->recordedAnswers[] = $recordedAnswers;

        return $this;
    }

    /**
     * Remove recordedAnswers
     *
     * @param \AppBundle\Entity\RecordedAnswer $recordedAnswers
     */
    public function removeRecordedAnswer(\AppBundle\Entity\RecordedAnswer $recordedAnswers)
    {
        $this->recordedAnswers->removeElement($recordedAnswers);
    }

    /**
     * Get recordedAnswers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecordedAnswers()
    {
        return $this->recordedAnswers;
    }

    /**
     * Add savedQuizzes
     *
     * @param \AppBundle\Entity\SavedQuiz $savedQuizzes
     * @return User
     */
    public function addSavedQuiz(\AppBundle\Entity\SavedQuiz $savedQuizzes)
    {
        $this->savedQuizzes[] = $savedQuizzes;

        return $this;
    }

    /**
     * Remove savedQuizzes
     *
     * @param \AppBundle\Entity\SavedQuiz $savedQuizzes
     */
    public function removeSavedQuiz(\AppBundle\Entity\SavedQuiz $savedQuizzes)
    {
        $this->savedQuizzes->removeElement($savedQuizzes);
    }

    /**
     * Get savedQuizzes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSavedQuizzes()
    {
        return $this->savedQuizzes;
    }
}
