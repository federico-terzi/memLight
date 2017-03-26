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
 * This entity rapresents a Course and stores all the relevant information about it
 * 
 * @ORM\Entity
 * @ORM\Table(name = "courses")
 * @author Federico Terzi
 *
 */
class Course
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $name;
	
	/**
	 * @ORM\Column(type="text")
	 */
	private $description;
	
	/**
	 * @ORM\Column(type="text")
	 */
	private $longDescription;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="coursesCreated")
	 */
	private $author;
	
	/**
	 * @ORM\OneToMany(targetEntity="Question", mappedBy="course", cascade={"remove"})
	 */
	private $questions;
	
	/**
	 * @ORM\OneToMany(targetEntity="RecordedAnswer", mappedBy="course", cascade={"remove"})
	 */
	private $recordedAnswers;
	
	/**
	 * @ORM\OneToMany(targetEntity="SavedQuiz", mappedBy="course", cascade={"remove"})
	 */
	private $savedQuizzes;
	
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
     * Set name
     *
     * @param string $name
     * @return Course
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Course
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set author
     *
     * @param \AppBundle\Entity\memLightUser $author
     * @return Course
     */
    public function setAuthor(\AppBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \AppBundle\Entity\memLightUser 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set longDescription
     *
     * @param string $longDescription
     * @return Course
     */
    public function setLongDescription($longDescription)
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * Get longDescription
     *
     * @return string 
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->recordedAnswers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->savedQuizzes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add questions
     *
     * @param \AppBundle\Entity\Question $questions
     * @return Course
     */
    public function addQuestion(\AppBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;

        return $this;
    }

    /**
     * Remove questions
     *
     * @param \AppBundle\Entity\Question $questions
     */
    public function removeQuestion(\AppBundle\Entity\Question $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Add recordedAnswers
     *
     * @param \AppBundle\Entity\RecordedAnswer $recordedAnswers
     * @return Course
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
     * @return Course
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
