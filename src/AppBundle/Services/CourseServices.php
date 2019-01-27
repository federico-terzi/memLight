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

use AppBundle\Entity\CourseChapter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Entity\Course;
use AppBundle\Entity\Question;
use AppBundle\Entity\User;
use AppBundle\Entity\SavedQuiz;

/**
 * Service used to menage Courses
 * 
 * @author Federico Terzi
 */
class CourseServices {
	// Store the doctrine service
	private $doctrine;
	
	function __construct($doctrine) {
		// Inject doctrine ( check config.yml )
		$this->doctrine = $doctrine; 
	}
	
	/**
	 * Return all Questions for the specified Course
	 * If no questions are found, return NULL
	 * 
	 * @param Course $course
	 * @return Question[]|null
	 */
	public function getAllQuestionForCourse($course, $includeVersions = false, $chapter = null)
	{
		// Get doctrine manager
		$manager = $this->doctrine->getManager();
		
		if ($includeVersions==false)
		{
			// Find the most recent version of the questions for the specified course
			$query = $manager->createQuery('SELECT q
								    FROM AppBundle:Question q
								    WHERE q.course = :course
								    AND ( q.chapter = :chapter OR :chapter is NULL )
									AND q.version >= (SELECT MAX(q2.version) FROM AppBundle:Question q2
													  WHERE q2.course = q.course AND q2.questionNumber = q.questionNumber)
								    ORDER BY q.questionNumber ASC'
					)->setParameter('course', $course)
                     ->setParameter('chapter', $chapter);
		}else{
			// Find all the versions of the questions for the specified course
			$query = $manager->createQuery('SELECT q
								    FROM AppBundle:Question q
								    WHERE q.course = :course
								    AND ( q.chapter = :chapter OR :chapter is NULL )
								    ORDER BY q.questionNumber ASC'
					)->setParameter('course', $course)
                     ->setParameter('chapter', $chapter);
		}
		
		// Get the results
		$questions = $query->getResult();
		
		// If there are no questions return null
		if (count($questions)==0)
		{
			$questions = null;
		}
		
		// Return the questions or null if there are no questions for the course
		return $questions;
	}

    /**
     * Return all Chapters for the specified Course
     * If no chapters are found, return NULL
     *
     * @param Course $course
     * @return CourseChapter[]|null
     */
    public function getChaptersForCourse($course)
    {
        // Get doctrine manager
        $manager = $this->doctrine->getManager();

        // Find chapters for the specified course
        $query = $manager->createQuery('SELECT c
								    FROM AppBundle:CourseChapter c
								    WHERE c.course = :course
								    ORDER BY c.id ASC'
        )->setParameter('course', $course);

        // Get the results
        $chapters = $query->getResult();

        // If there are no chapters return null
        if (count($chapters)==0)
        {
            $chapters = null;
        }

        // Return the chapters or null if there are no chapters for the course
        return $chapters;
    }
	
	/**
	 * Return an array of questions for the specified course and array of question's IDs.
	 * If no questions are found, return NULL
	 * 
	 * @param Course $course
	 * @param string[] $questions
	 * @return Question[]|null
	 */
	public function getSpecifiedQuestions($course, $questions)
	{
		// Get the doctrine manager
		$manager = $this->doctrine->getManager();
		
		// Find the most recent version of the questions that are contained in the $questions array
		$query = $manager->createQuery('SELECT q
								    FROM AppBundle:Question q
								    WHERE q.course = :course
									AND q.version >= (SELECT MAX(q2.version) FROM AppBundle:Question q2
													  WHERE q2.course = q.course AND q2.questionNumber = q.questionNumber)
									AND q.questionNumber IN (:questions)
								    ORDER BY q.questionNumber ASC'
				)->setParameter('course', $course)->setParameter('questions', $questions);
		
		// Get the result
		$results = $query->getResult();

		// Now order them in the same order of the given saved quiz
        $orderedQuestions = array();
        foreach($questions as $questionNumber) {
            foreach($results as $result) {
                if ($result->getQuestionNumber()== $questionNumber) {
                    array_push($orderedQuestions, $result);
                    break;
                }
            }
        }

		// If there are no questions return null
		if (count($orderedQuestions)==0)
		{
			$orderedQuestions = null;
		}
		
		// Return the questions or null if no question was found
		return $orderedQuestions;
	}
	
	/**
	 * Return most mistaken questions by a user for a specific course
	 * If no questions are found, return null.
	 * You can decide how many questions to fetch changing the $maxResults parameter
	 * 
	 * @param User $user
	 * @param int $course_id
	 * @param int $maxResults
	 * @throws NotFoundHttpException
	 * @return Question[]|null
	 */
	public function getMistakenQuestions($user, $course_id = null, $maxResults = 20)
	{
		// Get the doctrine manager
		$em = $this->doctrine->getManager();
	
		// If the course_id is null, search through all courses
		if (is_null($course_id)){
			// Find the most mistaken questions by the user in all courses
			$query = $em->createQuery('SELECT Q, count(Q.questionNumber), count(Q.questionNumber) AS HIDDEN orderCount
										FROM AppBundle:RecordedAnswer R, AppBundle:Question Q
										WHERE R.question = Q AND R.user = :user AND R.correctAnswer = 0
										GROUP BY R.course, Q.questionNumber
										ORDER BY orderCount DESC');
		}else{
			// Get the Course with the specified ID
			$course = $this->getCourseByID($course_id);
			
			// Find the most mistaken questions by the user in a specific course
			// it also makes sure that the questions returned are the most recent
			$query = $em->createQuery('SELECT Q, count(Q.questionNumber), count(Q.questionNumber) AS HIDDEN orderCount
										FROM AppBundle:RecordedAnswer R, AppBundle:Question Q
										WHERE R.question = Q AND R.user = :user AND R.correctAnswer = 0
										AND R.course = :course
										AND Q.version >= (SELECT MAX(Q2.version) FROM AppBundle:Question Q2
													      WHERE Q2.course = Q.course AND Q2.questionNumber = Q.questionNumber)
										GROUP BY Q.questionNumber
										ORDER BY orderCount DESC');
			$query = $query->setParameter('course', $course);
		}
		
		// Set the additional parameters
		$query = $query->setParameter('user', $user)->setMaxResults($maxResults);
		
		// Get the results ( null if no questions are found )
		$questions = $query->getResult();
		
		return $questions;
	}
	
	/**
	 * Return an array of Questions without mistake count
	 * 
	 * @param User $user
	 * @param int $course_id
	 */
	public function getMistakenQuestionsWithoutCounts($user, $course_id)
	{
		// Get the most mistaken questions for user and course
		$questions_raw = $this->getMistakenQuestions($user, $course_id);
		
		// Create an empty array
		$questions = array();
		
		// Add each question individually discarding counts
		for ($i = 0; $i<count($questions_raw); $i++)
		{
			$questions[] = $questions_raw[$i][0];
		}
		
		// Return result
		return $questions;
	}
	
	/**
	 * Return success rates of the user for the specified course
	 * 
	 * @param User $user
	 * @param int $course_id
	 * @param number $maxResults
	 * @throws NotFoundHttpException
	 * @return Array
	 */
	public function getSuccessRates($user, $course_id = null, $maxResults = 20)
	{
		// Get the Doctrine manager
		$em = $this->doctrine->getManager();
	
		// Check if a course is specified
		if (is_null($course_id)){
			// No course is specified, load the data for all courses
			// Query the success score of each day as the rapport between correct answers and total answers
			$query = $em->createQuery('SELECT R, SUM(R.correctAnswer)/COUNT(R.correctAnswer), 
										DATE_DIFF(CURRENT_TIMESTAMP(),R.datetime),
										DATE_DIFF(CURRENT_TIMESTAMP(),R.datetime) AS HIDDEN difference
										FROM AppBundle:RecordedAnswer R
										WHERE R.user = :user
										GROUP BY R.course, difference
										ORDER BY difference ASC');
		}else{
			// Course is specified, load the corresponding Course object
			$course = $this->getCourseByID($course_id);
			
			// Query the success score of each day as the rapport between correct answers and total answers
			// For the specified Course
			$query = $em->createQuery('SELECT R, SUM(R.correctAnswer)/COUNT(R.correctAnswer), 
										DATE_DIFF(CURRENT_TIMESTAMP(),R.datetime),
										DATE_DIFF(CURRENT_TIMESTAMP(),R.datetime) AS HIDDEN difference
										FROM AppBundle:RecordedAnswer R
										WHERE R.user = :user AND R.course = :course
										GROUP BY difference
										ORDER BY difference ASC');
			// Set the Course
			$query = $query->setParameter('course', $course);
		}
		
		// Set additional parameters
		$query = $query->setParameter('user', $user)->setMaxResults($maxResults);
		
		// Query the database
		$rates = $query->getResult();
	
		// Return the rates
		return $rates;
	}
	
	/**
	 * Check if a user has already started a quiz by checking if a SavedQuiz exists
	 * 
	 * @param Course $course
	 * @param User $user
	 * @return boolean
	 */
	public function doesCourseHaveSavedQuizForUser($course, $user)
	{
		// Get the Doctrine manager
		$manager = $this->doctrine->getManager();
		
		// Query a SavedQuiz for the specified user and Course
		$query = $manager->createQuery('SELECT s
								    FROM AppBundle:SavedQuiz s
								    WHERE s.course = :course
									AND s.user = :user'
				)->setParameter('course', $course)->setParameter('user', $user);
		
		// Get one result or return null
		$savedQuiz = $query->getOneOrNullResult();
		
		// If there is a saved quiz return true, if not, return false
		if (!$savedQuiz)
		{
			return false;
		}else{
			return true;
		}	
	}
	
	/**
	 * Get the SavedQuiz for the specified user and course
	 * If there are no SavedQuizzes, return null
	 * 
	 * @param User $user
	 * @param Course $course
	 * @return SavedQuiz|null
	 */
	public function getSavedQuiz($user, $course)
	{
		// Load the SavedQuiz repository
		$repository = $this->doctrine->getRepository("AppBundle:SavedQuiz");
		
		// Fetch the desired SavedQuiz
		$savedQuiz = $repository->findOneBy(array('course'=>$course, 'user'=>$user));
		
		// Return the SavedQuiz, or null if there is not saved quiz
		return $savedQuiz;
	}
	
	/**
	 * Get the maximum question number for the specified Course
	 * 
	 * @param Course $course
	 * @return int
	 */
	public function getMaxQuestionNumber($course)
	{
		// Get the Doctrine manager
		$manager = $this->doctrine->getManager();
		
		// Query the maximum question number for a course
		$query = $manager->createQuery('SELECT MAX(q.questionNumber)
								    FROM AppBundle:Question q
								    WHERE q.course = :course'
									)->setParameter('course', $course);
		
		// Get the result
		$maxQuestionNumber = $query->getSingleScalarResult();
		
		// Return the result
		return $maxQuestionNumber;
	}
	
	/**
	 * Get the Question instance corresponding to the specified course and questionNumber
	 * NOTE: return the most recent version of the question
	 * 
	 * NOTE 2: if there are no questions with that parameters, return null
	 * 
	 * @param Course $course
	 * @param int $questionNumber
	 * @return Question|null
	 */
	public function getQuestionFromNumber($course, $questionNumber)
	{
		// Get the Doctrine manager
		$em = $this->doctrine->getManager();
		
		// Prepare the parameters
		$parameters = ['course'=>$course, 'number'=> $questionNumber];
		
		// Query the most recent version of a Question having the specified course and questionNumber
		$query = $em->createQuery('SELECT q
								    FROM AppBundle:Question q
								    WHERE q.course = :course
									AND q.questionNumber = :number
									AND q.version >= (SELECT MAX(q2.version) FROM AppBundle:Question q2
													  WHERE q2.course = q.course AND q2.questionNumber = q.questionNumber)'
								)->setParameters($parameters)->setMaxResults(1);
		
		// Get the question or null if no question is found
		$question = $query->getOneOrNullResult();
				
		// Return the question
		return $question;
	}
	
	/**
	 * Get the Course instance given the "course_id"
	 * 
	 * @param int $course_id
	 * @throws NotFoundHttpException
	 * @return Course
	 */
	public function getCourseByID($course_id)
	{
		// Get the Course repository
		$repository = $this->doctrine->getRepository("AppBundle:Course");
			
		// Get the Course with the specified id
		$course = $repository->find($course_id);
			
		// If no course is found with that id, throws an exception
		if(!$course)
		{
			throw new NotFoundHttpException("No Course found with ID: ".$course_id);
		}
		
		// Return the course
		return $course;
	}
	
}