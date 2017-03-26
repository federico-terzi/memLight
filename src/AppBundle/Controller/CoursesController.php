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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to menage content related to Courses
 * 
 * @author Federico Terzi
 */
class CoursesController extends Controller
{
	/**
	 * Render the page that shows a list of all courses
	 * 
	 * @Route("/courses/", name="courses")
	 */
	public function coursesAction(Request $request)
	{
		return $this->render('courses/courses.html.twig', array());
	}
	
	/**
	 * Render the list of all the courses as a table
	 * 
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function coursesTableAction()
	{
		// Load the Course repository
		$repository = $this->getDoctrine()->getRepository("AppBundle:Course");
		
		// Fetch all the courses
		$courses = $repository->findAll();
		
		// Render the template
		return $this->render('courses/coursesTable.html.twig', array('courses' => $courses));
	}
	
	/**
	 * Shows the details of a specific course
	 * 
	 * @Route("/course/{course_id}/", name="course")
	 */
	public function coursePageAction($course_id)
	{
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Used later to determine if a user has already started a quiz
		$hasAlreadyStartedQuiz = false;
		
		// Check if the user is logged in, and if so, check if he has already started a quiz
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			
			// Get the current User
			$user = $this->get('security.token_storage')->getToken()->getUser();
			
			// Check if user has already started a quiz
			$hasAlreadyStartedQuiz = $courseServices->doesCourseHaveSavedQuizForUser($course, $user);
		}
		
		return $this->render('courses/course.html.twig', array('course'=>$course, 'already_started'=>$hasAlreadyStartedQuiz));
	}
	
	/**
	 * Render a page containing the list of questions for a specific course
	 * 
	 * @Route("/course/{course_id}/list", name="list_questions")
	 */
	public function questionListPageAction($course_id)
	{
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Render the page
		return $this->render('courses/questionListPage.html.twig', array('course'=>$course));
	}
	
	/**
	 * Render the list of questions of the Course specified by the $course variable.
	 * 
	 * @param Course $course
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function questionListAction($course)
	{
		// Load the Course Service
		$courseServices = $this->get("course_services");
		
		// Load all the questions for a specific course
		// If a course doesn't have any question, it returns null
		$questions = $courseServices->getAllQuestionForCourse($course);
		
		// Render the list
		return $this->render('courses/questionList.html.twig', array('questions' => $questions));
	}
}
