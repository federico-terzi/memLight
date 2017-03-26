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


/**
 * Controller used to menage Statistics and Progress Data
 * 
 * @author Federico Terzi
 */
class StatsController extends Controller
{
	/**
	 * Render the Stats page
	 * 
	 * @Route("/stats/{course_id}", name="stats", defaults={"course_id" = "all"})
	 */
	public function statsAction($course_id)
	{
		// Check if the user is logged in, if not, block access
		$this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page!');
		
		// Conversion needed for the template
		// If course_id == "all" the user will see the data from all courses combined
		if ($course_id == "all")
		{
			$course_id = null;	
		}
		
		// Render the Stats page
		return $this->render(
				'stats/stats.html.twig', array('course_id' => $course_id)
				);
	}
	
	/**
	 * Render a dropdown with all courses
	 * 
	 * @param int $course_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function coursesDropdownAction($course_id)
	{
		// Get the Course repository
		$repository = $this->getDoctrine()->getRepository("AppBundle:Course");
		
		// Fetch all Courses
		$courses = $repository->findAll();
		
		// Pass the parameters and render the dropdown
		return $this->render('stats/coursesDropdown.html.twig', array('courses' => $courses, 'course_id'=> $course_id));
	}
	
	/**
	 * Render a table with the most mistaken questions by the user
	 * for a specified Course or for all combined
	 * 
	 * @param int $course_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function wrongAnswerTableAction($course_id = null)
	{
		// Get the User instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
	
		// Get the most mistaken answers
		$answers = $this->get('course_services')->getMistakenQuestions($user, $course_id);
		
		// Render the table
		return $this->render('stats/wrongAnswerTable.html.twig', array('answers' => $answers));
	}
	
	/**
	 * Render the array used by Google Chart to display progress data
	 * 
	 * @param int $course_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function successRatesAction($course_id)
	{
		// Get the user instance
		$user = $this->get('security.token_storage')->getToken()->getUser();
		
		// Get the success rates of the User for the specified course
		$rates = $this->get('course_services')->getSuccessRates($user, $course_id);
		
		// Render the rates
		return $this->render('stats/rates.html.twig', array('rates' => $rates));
		
	}
}
