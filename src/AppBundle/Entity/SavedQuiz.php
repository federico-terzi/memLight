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
 * This entity is used to store a Quiz, so an User can continue it later
 * 
 * @ORM\Entity
 * @ORM\Table(name = "saved_quizes")
 * @author Federico Terzi
 *
 */
class SavedQuiz
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="savedQuizzes")
	 */
	private $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Course", inversedBy="savedQuizzes")
	 */
	private $course;

	/**
	 * @ORM\Column(type="text")
	 */
	private $questions;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $datetime;

	public function __construct()
	{
		$this->datetime = new \DateTime("now");
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
     * Set questions
     *
     * @param string $questions
     * @return SavedQuiz
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;

        return $this;
    }

    /**
     * Get questions
     *
     * @return string 
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return SavedQuiz
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return SavedQuiz
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

    /**
     * Set course
     *
     * @param \AppBundle\Entity\Course $course
     * @return SavedQuiz
     */
    public function setCourse(\AppBundle\Entity\Course $course = null)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return \AppBundle\Entity\Course 
     */
    public function getCourse()
    {
        return $this->course;
    }
}
