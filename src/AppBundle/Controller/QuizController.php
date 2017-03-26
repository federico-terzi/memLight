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

use AppBundle\Entity\Course;
use AppBundle\Entity\Question;
use AppBundle\Entity\RecordedAnswer;
use AppBundle\Entity\SavedQuiz;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to manage the Quiz feature
 * 
 * @author Federico Terzi
 */
class QuizController extends Controller
{
	/**
	 * Render the Quiz with all questions for a specified course 
	 * 
	 * @Route("/course/{course_id}/quiz/all", name="quiz")
	 */
	public function quizAction($course_id)
	{
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Used later to determine wheather a User has already started a quiz
		$completeAlreadyStartedQuiz = false;
		
		// Check if the user is logged in
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			
			// Get the User instance
			$user = $this->get('security.token_storage')->getToken()->getUser();
			
			// Get the saved quiz of this course for the specified user
			// NOTE: if there is no saved quiz, it returns null
			$savedQuiz = $courseServices->getSavedQuiz($user, $course);
			
			// Check if the reminder is enabled in the user settings
			$isReminderEnabled = $this->get('setting_services')->getSettings($user)->getEnableReminder();
			
			// If there is a saved quiz and the reminder is enable
			if($savedQuiz && $isReminderEnabled)
			{
				// Set this variable to true ( Used to notify the user later )
				$completeAlreadyStartedQuiz = true;
			}
		}
		
		// Define the parameters for the quiz
		$quizParams = array('shuffle'=>true, 'recover'=>$completeAlreadyStartedQuiz);
		
		// Get all the questions for the current course
		$questions = $courseServices->getAllQuestionForCourse($course);
		
		// Pass the parameters and render the quiz
		return $this->render('quiz/quiz.html.twig', array('course'=>$course, 'questions' => $questions, 'quizParams' => $quizParams));
	}
	
	/**
	 * Render the Quiz with the questions left in the already started quiz.
	 * Load the Saved Quiz for the specified course and user and uses those
	 * questions to render the Quiz
	 * 
	 * @Route("/course/{course_id}/quiz/recover", name="recover_quiz")
	 */
	public function recoverQuizAction($course_id)
	{
		// Check if the user is logged in, if not, block access
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
		
		// Define the quiz parameters
		$quizParams = array('shuffle'=>true, 'recover'=>false);
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Get the User instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		// Get the saved quiz of this course for the specified user
		// NOTE: if there is no saved quiz, it returns null
		$savedQuiz = $courseServices->getSavedQuiz($user, $course);
		
		if(!$savedQuiz)
		{
			// No saved quiz for this course, return to course page
			return $this->redirectToRoute("course", array("course_id"=>$course_id));
		}
		
		// Split the string into an array of question IDs
		$questionArray = explode(";", $savedQuiz->getQuestions());
		
		// Get the questions specified by the array of IDs
		$questions = $courseServices->getSpecifiedQuestions($course, $questionArray);
		
		// Pass the parameters and render the quiz
		return $this->render('quiz/quiz.html.twig', array('course'=>$course, 'questions' => $questions, 'quizParams' => $quizParams));
	}
	
	/**
	 * Render a Quiz with the most mistaken questions by the current user
	 * 
	 * @Route("/course/{course_id}/quiz/mistaken", name="mistaken_quiz")
	 */
	public function mistakenQuizAction($course_id)
	{
		// Check if the user is logged in, if not, block access
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
		
		// Define the Quiz parameters
		$quizParams = array('shuffle'=>true, 'recover'=>false);
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Get the User instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		// Get the most mistaken questions by the user for a specific course
		$questions = $courseServices->getMistakenQuestionsWithoutCounts($user, $course_id);
		
		// Pass the parameters and render the quiz
		return $this->render('quiz/quiz.html.twig', array('course'=>$course, 'questions' => $questions, 'quizParams' => $quizParams));
	}
	
	/**
	 * Action used to register User answers during a Quiz ( for statistic purposes )
	 * 
	 * @Route("/course/{course_id}/register_answer/{question_number}/{is_correct}", name="register_answer")
	 */
	public function registerAnswerAction($course_id, $question_number, $is_correct)
	{
		// Check if the user is logged in, if not, return an error message
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			return new Response("ERROR_NOT_LOGGED_IN");
		}
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Convert 0 and 1 into a boolean value
		if($is_correct=="1")
		{
			$is_correct=true;
		}else{
			$is_correct=false;
		}
		
		// Get the User instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		// Get the Question with the specified question number and course
		$question = $courseServices->getQuestionFromNumber($course, $question_number);
		
		// If the question does not exist, throw an exception 
		if(!$question)
		{
			throw $this->createNotFoundException("QUESTION NOT FOUND!");
		}
		
		// Get the Doctrine Manager
		$em = $this->getDoctrine()->getManager();
		
		// Create a new RecordedAnswer object with the received data
		$recordedAnswer = new RecordedAnswer();
		$recordedAnswer->setCourse($course);
		$recordedAnswer->setUser($user);
		$recordedAnswer->setCorrectAnswer($is_correct);
		$recordedAnswer->setQuestion($question);
		
		// Persist to the database
		$em->persist($recordedAnswer);
		
		// Commit changes to the database
		$em->flush();
		
		// Return OK if everything succeded
		return new Response("OK");
	}
	
	/**
	 * Action used to save a quiz
	 * 
	 * @Route("/course/{course_id}/save_quiz", name="save_quiz")
	 */
	public function saveQuiz(Request $request, $course_id)
	{
		// Check if the user is logged in, if not, return an error message
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			return new Response("ERROR_NOT_LOGGED_IN");
		}
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Get the User instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		// Get the questions data from the POST request
		$questions = $request->request->get('questions', null);
		
		// If there are no questions, throw an error message
		if(!$questions)
		{
			throw $this->createNotFoundException("QUESTIONS ARE NOT VALID!");
		}
			
		// Get the saved quiz of this course for the specified user
		// NOTE: if there is no saved quiz, it returns null
		$savedQuiz = $courseServices->getSavedQuiz($user, $course);
		
		// Get the Doctrine manager
		$em = $this->getDoctrine()->getManager();
		
		// Check if the Quiz is finished
		if($questions!=="QUIZ_ENDED")
		{	
			// If there are no saved quiz for this course and user, create a new object
			if(!$savedQuiz)
			{
				$savedQuiz = new SavedQuiz();
				$savedQuiz->setCourse($course);
				$savedQuiz->setUser($user);
			}
			
			// Set the saved quiz questions obtained through the POST request
			$savedQuiz->setQuestions($questions);
			
			// Merge the changes to the database
			$em->merge($savedQuiz);
		}else{
			// If the quiz is finished and there is a saved quiz in the database
			if($savedQuiz)
			{
				// Remove the saved quiz
				$em->remove($savedQuiz);
			}
		}
		
		// Commit changes to the database
		$em->flush();
		
		// Return OK if everything succeded
		return new Response("OK");
	}
}
