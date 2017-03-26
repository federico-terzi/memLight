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
 * This entity is used to store information about the answer that an User gives.
 * They are recorded during a test and then processed to obtain progress data.
 * 
 * @ORM\Entity
 * @ORM\Table(name = "recorded_answers")
 * @author Federico Terzi
 *
 */
class RecordedAnswer
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="recordedAnswers")
	 */
	private $user;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Course", inversedBy="recordedAnswers")
	 */
	private $course;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Question", inversedBy="recordedAnswers")
	 */
	private $question;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	private $correctAnswer;
	
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
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return WrongAnswer
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
     * @param \AppBundle\Entity\memLightUser $user
     * @return WrongAnswer
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\memLightUser 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set course
     *
     * @param \AppBundle\Entity\course $course
     * @return WrongAnswer
     */
    public function setCourse(\AppBundle\Entity\Course $course = null)
    {
        $this->course = $course;

        return $this;
    }

    /**
     * Get course
     *
     * @return \AppBundle\Entity\course 
     */
    public function getCourse()
    {
        return $this->course;
    }

    /**
     * Set question
     *
     * @param \AppBundle\Entity\question $question
     * @return WrongAnswer
     */
    public function setQuestion(\AppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \AppBundle\Entity\question 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set correctAnswer
     *
     * @param boolean $correctAnswer
     * @return WrongAnswer
     */
    public function setCorrectAnswer($correctAnswer)
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    /**
     * Get correctAnswer
     *
     * @return boolean 
     */
    public function getCorrectAnswer()
    {
        return $this->correctAnswer;
    }
}
