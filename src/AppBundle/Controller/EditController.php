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
use AppBundle\Form\CourseType;
use AppBundle\Form\QuestionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller used to Add/Edit Courses and Questions
 * 
 * @author Federico Terzi
 */
class EditController extends Controller
{
	/**
	 * Render the control panel of a Course, where you can:
	 * - Edit Course information
	 * - Add/Edit Questions
	 * - Delete the Course
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/course/{course_id}/edit", name="edit_course")
	 */
	public function editCourseAction($course_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Render the control panel
		return $this->render('edit/editCoursePage.html.twig', array('course'=>$course));

	}
	
	/**
	 * Render the list of questions for the specified Course
	 * 
	 * @param Course $course
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function editQuestionListAction($course)
	{
		// Get the Course Service
		$courseServices = $this->get("course_services");
		
		// Fetch all the questions for the specified Course
		$questions = $courseServices->getAllQuestionForCourse($course);
		
		// Pass the questions and render the list
		return $this->render('edit/editQuestionList.html.twig', array('questions' => $questions));
	}
	
	/**
	 * Action used to "Edit a Question" by providing a form where user can modify all the information
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/question/{question_id}/edit", name="edit_question")
	 */
	public function editQuestionAction(Request $request, $question_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Load the Question repository
		$repository = $this->getDoctrine()->getRepository("AppBundle:Question");
	
		// Fetch the specified question
		$question = $repository->find($question_id);
		
		// If there are no Questions with the specified ID, throw an exception
		if(!$question)
		{
			throw $this->createNotFoundException("QUESTION NOT FOUND WITH ID:".$question_id);
		}
		
		// Get the Course from the Question
		$course = $question->getCourse();
		
		// Store the old URLs of the images
		// Used in case someone submit the form without uploading a new image
		$oldAnswerUrl = $question->getAnswerUrl();
		$oldQuestionUrl = $question->getQuestionUrl();
		
		// Build the form
		$form = $this->createForm(QuestionType::class, $question);
		
		// Handle the submit request
		$form->handleRequest($request);
		
		// When the form is submitted
		if ($form->isSubmitted() && $form->isValid()) {
			
			// Get the version of the specified Question
			$version = $question->getVersion();
			
			// Clone the submitted question into a new variable
			$newQuestion = clone $question;
			
			// Increase the version number of the question
			$newQuestion->setVersion($version + 1);
			
			// Check if the user submitted a new Answer Image
			if(is_null($question->getAnswerUrl())){
				
				// If the user didn't submit a new Answer Image, it uses the old one
				$newQuestion->setAnswerUrl($oldAnswerUrl);
			}else{
				
				// If the user submitted a new answer image, gets the file
				$answerFile = $question->getAnswerUrl();
					
				// Generate a unique name for the file before saving it
				$fileName = md5(uniqid()).'.'.$answerFile->guessExtension();
					
				// Move the file to the Course directory where the images are stored
				$answerFile->move(
						$this->getParameter('courses_directory') . $course->getId(),
						$fileName
						);
					
				// Update the 'answerUrl' property to store the image file name
				$newQuestion->setAnswerUrl($fileName);
			}
			
			// Check if the user submitted a new Question Image
			if(is_null($question->getQuestionUrl())){
				
				// If the user didn't submit a new Question Image, it uses the old one
				$newQuestion->setQuestionUrl($oldQuestionUrl);
			}else{
				
				// If the user submitted a new question image, gets the file
				$questionFile = $question->getQuestionUrl();
					
				// Generate a unique name for the file before saving it
				$fileName = md5(uniqid()).'.'.$questionFile->guessExtension();
					
				// Move the file to the Course directory where the images are stored
				$questionFile->move(
						$this->getParameter('courses_directory') . $course->getId(),
						$fileName
						);
					
				// Update the 'questionUrl' property to store the image file name
				$newQuestion->setQuestionUrl($fileName);
			}
			
			// If the question is not valid, show an error message to the user
			if (!$newQuestion->isQuestionValid())
			{
				$form->addError(new FormError("Question is not valid!"));
				return $this->render(
						'edit/editQuestion.html.twig',
						array('form' => $form->createView())
						);
			}
			
			// Clear the associations of the old question
			$newQuestion->clearRecordedAnswers();
			
			// Get the Doctrine Manager
			$em = $this->getDoctrine()->getManager();
			
			// Detach the old question to avoid unwanted modifications
			$em->detach($question);
			
			// Persist the new question to the database
			$em->persist($newQuestion);
			
			// Commit to the database
			$em->flush();
			
			// Redirect to the Course control panel
			return $this->redirectToRoute('edit_course', array('course_id'=>$course->getId()));
		}
		
		// Pass the form and render the page
		return $this->render(
				'edit/editQuestion.html.twig',
				array('form' => $form->createView())
				);
	
	}
	
	/**
	 * Action used to "Add a Question" by providing a form where user can write all the information
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/course/{course_id}/add", name="add_question")
	 */
	public function addQuestionAction(Request $request, $course_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Create a new Question object
		$newQuestion = new Question();
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Build the form
		$form = $this->createForm(QuestionType::class, $newQuestion);
	
		// Handle the submit request
		$form->handleRequest($request);
		
		// When the form is submitted
		if ($form->isSubmitted() && $form->isValid()) {
			
			// Set the question Number for the new question
			// Get the maximum number for the current Course and add 1 to obtain the new number
			$newQuestion->setQuestionNumber($courseServices->getMaxQuestionNumber($course)+1);
			
			// Set the Course
			$newQuestion->setCourse($course);
			
			// Check if the user submitted a new Answer Image
			if(is_null($newQuestion->getAnswerUrl()) == false ){
				
				// If the user submitted a new answer image, gets the file
				$answerFile = $newQuestion->getAnswerUrl();
					
				// Generate a unique name for the file before saving it
				$fileName = md5(uniqid()).'.'.$answerFile->guessExtension();
					
				// Move the file to the Course directory where the images are stored
				$answerFile->move(
						$this->getParameter('courses_directory') . $course->getId(),
						$fileName
						);
					
				// Update the 'answerUrl' property to store the image file name
				$newQuestion->setAnswerUrl($fileName);
			}
			
			// Check if the user submitted a new Question Image
			if(is_null($newQuestion->getQuestionUrl()) == false){
				
				// If the user submitted a new question image, gets the file
				$questionFile = $newQuestion->getQuestionUrl();
					
				// Generate a unique name for the file before saving it
				$fileName = md5(uniqid()).'.'.$questionFile->guessExtension();
				
				// Move the file to the Course directory where the images are stored
				$questionFile->move(
						$this->getParameter('courses_directory') . $course->getId(),
						$fileName
						);
					
				// Update the 'questionUrl' property to store the image file name
				$newQuestion->setQuestionUrl($fileName);
			}
			
			// If the question is not valid, show an error message to the user
			if (!$newQuestion->isQuestionValid())
			{
				$form->addError(new FormError("Question is not valid!"));
				return $this->render(
						'edit/addQuestion.html.twig',
						array('form' => $form->createView())
						);
			}
			
			// Get the Doctrine Manager
			$em = $this->getDoctrine()->getManager();
			
			// Persist the new question to the database
			$em->persist($newQuestion);
			
			// Commit to the database
			$em->flush();
			
			// Redirect to the Course control panel
			return $this->redirectToRoute('edit_course', array('course_id'=>$course->getId()));
		}
		
		// Pass the form and render the page
		return $this->render(
				'edit/addQuestion.html.twig',
				array('form' => $form->createView())
				);
	
	}
	
	/**
	 * Action used to "Delete a Question"
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/question/{question_id}/remove", name="remove_question")
	 */
	public function removeQuestionAction(Request $request, $question_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Load the Question repository
		$repository = $this->getDoctrine()->getRepository("AppBundle:Question");
		
		// Fetch the specified question
		$question = $repository->find($question_id);
		
		// If there are no Questions with the specified ID, throw an exception
		if(!$question)
		{
			throw $this->createNotFoundException("QUESTION NOT FOUND WITH ID:".$question_id);
		}
		
		// Get the Course from the Question
		$course = $question->getCourse();
		
		// Generate the path of the current Course directory
		$dir_path = $this->getParameter('courses_directory') . $course->getId() . "/";
		
		// Create a Filesystem object
		$fs = new Filesystem();
		
		// Get all the versions of the specified question
		$questions = $repository->findBy(array('questionNumber'=>$question->getQuestionNumber(),'course'=>$question->getCourse()));
		
		// Get the Doctrine Manager
		$em = $this->getDoctrine()->getManager();
		
		// Loop through each question version
		foreach ($questions as $question) {
			
			// This section deletes the Question Image from the Course directory
			try {
				// Check if the path is a file
				if (!is_dir($dir_path . $question->getQuestionUrl())) {
 					
					// Remove the question image
					$fs->remove($dir_path . $question->getQuestionUrl());
				}
			} catch (IOExceptionInterface $e) {
				// Log the error
				$this->get('logger')->err("An error occurred while deleting your question image: ".$e->getPath());
			}
			
			// This section deletes the Answer Image from the Course directory
			try {
				// Check if the path is a file
				if (!is_dir($dir_path . $question->getAnswerUrl())) {
						
					// Remove the answer image
					$fs->remove($dir_path . $question->getAnswerUrl());
				}
			} catch (IOExceptionInterface $e) {
				// Log the error
				$this->get('logger')->err("An error occurred while deleting your answer image: ".$e->getPath());
			}
			
			// Remove the Question from the database
			$em->remove($question);
		}
		
		// Commit to database
		$em->flush();
	
		// Redirect to the Course control panel
		return $this->redirectToRoute('edit_course', array('course_id'=>$course->getId()));
	
	}
	
	/**
	 * Action used to "Edit Course information" by providing a form where user can modify all the information
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/course/{course_id}/edit/info", name="edit_course_info")
	 */
	public function editCourseInfoAction(Request $request, $course_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
	
		// Build the form
		$form = $this->createForm(CourseType::class, $course);
	
		// Handle the submit request
		$form->handleRequest($request);
		
		// When the form is submitted
		if ($form->isSubmitted() && $form->isValid()) {
			
			// Get Doctrine manager
			$em = $this->getDoctrine()->getManager();
			
			// Persist the modified course to the database
			$em->persist($course);
			
			// Commit to the database
			$em->flush();
	
			// Redirect to the Course control panel
			return $this->redirectToRoute('edit_course', array('course_id'=>$course->getId()));
		}
	
		// Pass the form and render the page
		return $this->render(
				'edit/editCourseInfo.html.twig',
				array('form' => $form->createView())
				);
	
	}
	
	/**
	 * Action used to "Add a Course". It provides a form where user can insert all the information
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/courses/new", name="add_course")
	 */
	public function addCourseAction(Request $request)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Create a new Course object
		$course = new Course();
	
		// Build the form
		$form = $this->createForm(CourseType::class, $course);
	
		// Handle the submit request
		$form->handleRequest($request);
		
		// When the form is submitted
		if ($form->isSubmitted() && $form->isValid()) {
			
			// Get the current User
			$user = $this->get('security.token_storage')->getToken()->getUser();
			
			// Set the User as the Course author
			$course->setAuthor($user);
			
			// Get Doctrine manager
			$em = $this->getDoctrine()->getManager();
			
			// Persist the Course to the database
			$em->persist($course);
			
			// Commit the changes to the database
			$em->flush();
			
			// Generate the path for the current course directory
			$dir_path = $this->getParameter('courses_directory') . $course->getId();
			
			// Logs the directory path
			$this->get('logger')->info("Adding Course directory in: ".$dir_path);
			
			// Create a new Filesystem object
			$fs = new Filesystem();
			
			// Try to create a new directory for the course
			try {
				// Create the directory
				$fs->mkdir($dir_path);
			} catch (IOExceptionInterface $e) {
				// Log the error
				$this->get('logger')->err("An error occurred while creating your course directory at ".$e->getPath());
			}
			
			// Redirect to the Course control panel
			return $this->redirectToRoute('edit_course', array('course_id'=>$course->getId()));
		}
	
		//Pass the form and render the page
		return $this->render(
				'edit/addCourse.html.twig',
				array('form' => $form->createView())
				);
	
	}
	
	/**
	 * Action used to "Delete a Course".
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/course/{course_id}/delete", name="delete_course")
	 */
	public function deleteCourseAction($course_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Generate the path for the current course directory
		$dir_path = $this->getParameter('courses_directory') . $course->getId() . "/";
		
		// Get the Doctrine manager
		$em = $this->getDoctrine()->getManager();
		
		// Remove the Course
		$em->remove($course);
		
		// Commit the changes to the database
		$em->flush();
		
		// Create a new Filesystem object
		$fs = new Filesystem();
		
		// Try to delete the course directory
		try {
			// Delete the course directory
			$fs->remove($dir_path);
		} catch (IOExceptionInterface $e) {
			// Log the error
			$this->get('logger')->err("An error occurred while deleting your directory at ".$e->getPath());
		}
		
		// Redirect to the Courses page
		return $this->redirectToRoute('courses');
	}
	
	/**
	 * Warn the user before deleting a course
	 * 
	 * NOTE: User must have ROLE_ADMIN permissions
	 * 
	 * @Route("/course/{course_id}/warning/delete", name="warning_delete_course")
	 */
	public function warningDeleteCourseAction($course_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
		
		// Load the CourseService
		$courseServices = $this->get("course_services");
		
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Render the page
		return $this->render('edit/warningDeleteCourse.html.twig', array('course'=>$course));
	}
	
	/**
	 * Export the Selected Course to zip
	 *
	 * NOTE: User must have ROLE_ADMIN permissions
	 *
	 * @Route("/course/{course_id}/export", name="export_course")
	 */
	public function exportCourseAction($course_id)
	{
		// Check if the user has ROLE_ADMIN permissions, if not, block the access
		$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
	
		// Load the CourseService
		$courseServices = $this->get("course_services");
	
		// Get the Course with the specified ID
		$course = $courseServices->getCourseByID($course_id);
		
		// Get all Questions for Course ( include all the question versions )
		$questions = $courseServices->getAllQuestionForCourse($course, true);
		
		// Get all the Course data into an array
		$output = array('course'=>($course),
						'questions'=>($questions),					
						);
		
		// Convert the array to json
		$json = json_encode($output);
		
		// Create a Filesystem object
		$fs = new Filesystem();
		
		// Generate the path of the current Course directory
		$dir_path = $this->getParameter('courses_directory') . $course->getId() . "/";
		
		// Filename for the output file
		$filename = $dir_path."/course.json";
		
		// Write the json to file
		$fs->dumpFile($filename, $json);
		
		$files = array($filename);
		
		foreach ($questions as $q) {
			if (!is_null($q->getQuestionUrl()))
			{
				$files[] = $dir_path . "/" . $q->getQuestionUrl();
			}
			if (!is_null($q->getAnswerUrl()))
			{
				$files[] = $dir_path . "/" . $q->getAnswerUrl();
			}
		}
		
		$zip = new \ZipArchive();
		$zipName = $this->getParameter('export_directory').'Course-'.$course_id."-".time().".zip";
		$zip->open($zipName,  \ZipArchive::CREATE);
		foreach ($files as $f) {
			$zip->addFromString(basename($f),  file_get_contents($f));
		}
		
		$zip->close();
		
		// Render the page
		return new Response($json);
	}
}
