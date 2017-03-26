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
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * This entity rapresents a Question and store all the information
 * 
 * NOTE: the "question_valid" constraint is used to make sure that
 * all questions have an unique (course, questionNumber, version) tuple.
 * 
 * @ORM\Entity
 * @ORM\Table(name = "questions", uniqueConstraints={@UniqueConstraint(name="question_valid", columns={"question_number","course_id","version"})})
 * @author Federico Terzi
 *
 */
class Question
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	private $questionNumber;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Course", inversedBy="questions")
	 */
	private $course;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	private $version = 0;
	
	/**
	 * @ORM\Column(type="string", length=20)
	 */
	private $questionType;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $questionText;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $questionUrl;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $answerText;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $answerUrl;
	
	/**
	 * @ORM\OneToMany(targetEntity="RecordedAnswer", mappedBy="question", cascade={"remove"})
	 */
	private $recordedAnswers;
	
	/**
	 * Check if the question is valid based by the questionType
	 * 
	 * Checks:
	 * 
	 * - "base" is composed only by the questionText and the answerText
	 * - "image_answer" must have an answerUrl defined
	 * - "custom" does not have any constraint
	 * 
	 * @return boolean
	 */
	public function isQuestionValid()
	{
		if ($this->questionType==="base")
		{
			return ($this->questionText!==null&&$this->questionUrl===null
				 	&&$this->answerText!==null&&$this->answerUrl===null);
		} elseif ($this->questionType==="image_answer") {
			return ($this->answerUrl!==null);
		} elseif ($this->questionType==="custom") {
			return true;
		}
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
     * Set questionNumber
     *
     * @param integer $questionNumber
     * @return Question
     */
    public function setQuestionNumber($questionNumber)
    {
        $this->questionNumber = $questionNumber;

        return $this;
    }

    /**
     * Get questionNumber
     *
     * @return integer 
     */
    public function getQuestionNumber()
    {
        return $this->questionNumber;
    }

    /**
     * Set version
     *
     * @param integer $version
     * @return Question
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return integer 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set questionType
     *
     * @param string $questionType
     * @return Question
     */
    public function setQuestionType($questionType)
    {
        $this->questionType = $questionType;

        return $this;
    }

    /**
     * Get questionType
     *
     * @return string 
     */
    public function getQuestionType()
    {
        return $this->questionType;
    }

    /**
     * Set questionText
     *
     * @param string $questionText
     * @return Question
     */
    public function setQuestionText($questionText)
    {
        $this->questionText = $questionText;

        return $this;
    }

    /**
     * Get questionText
     *
     * @return string 
     */
    public function getQuestionText()
    {
        return $this->questionText;
    }

    /**
     * Set questionUrl
     *
     * @param string $questionUrl
     * @return Question
     */
    public function setQuestionUrl($questionUrl)
    {
        $this->questionUrl = $questionUrl;

        return $this;
    }

    /**
     * Get questionUrl
     *
     * @return string 
     */
    public function getQuestionUrl()
    {
        return $this->questionUrl;
    }

    /**
     * Set answerText
     *
     * @param string $answerText
     * @return Question
     */
    public function setAnswerText($answerText)
    {
        $this->answerText = $answerText;

        return $this;
    }

    /**
     * Get answerText
     *
     * @return string 
     */
    public function getAnswerText()
    {
        return $this->answerText;
    }

    /**
     * Set answerUrl
     *
     * @param string $answerUrl
     * @return Question
     */
    public function setAnswerUrl($answerUrl)
    {
        $this->answerUrl = $answerUrl;

        return $this;
    }

    /**
     * Get answerUrl
     *
     * @return string 
     */
    public function getAnswerUrl()
    {
        return $this->answerUrl;
    }

    /**
     * Set course
     *
     * @param \AppBundle\Entity\course $course
     * @return Question
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
     * Constructor
     */
    public function __construct()
    {
        $this->recordedAnswers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add recordedAnswers
     *
     * @param \AppBundle\Entity\RecordedAnswer $recordedAnswers
     * @return Question
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
     * Clear recordedAnswers
     *
     */
    public function clearRecordedAnswers()
    {
    	$this->recordedAnswers->clear();
    }
}
