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

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CourseServicesTest extends  KernelTestCase
{
	private $doctrine;
	private $courseServices;
	
	/**
	 * {@inheritDoc}
	 */
	protected function setUp()
	{
		self::bootKernel();
	
		$this->doctrine = static::$kernel->getContainer()
		->get('doctrine');
		
		$this->courseServices = static::$kernel->getContainer()->get("course_services");
	}
	
    public function testDoesCourseHaveSavedQuizForUser()
    {
    	$repository = $this->doctrine->getRepository("AppBundle:Course");
    	$course = $repository->find("2");
    	
    	$repository = $this->doctrine->getRepository("AppBundle:User");
    	$user = $repository->find("1");
    	
    	$this->assertEquals(true, $this->courseServices->doesCourseHaveSavedQuizForUser($course, $user));
    	 
    }
    
    public function testDoesCourseHaveSavedQuizForUserNotValid()
    {
    	$repository = $this->doctrine->getRepository("AppBundle:Course");
    	$course = $repository->find("1");
    	 
    	$repository = $this->doctrine->getRepository("AppBundle:User");
    	$user = $repository->find("-1");
    	 
    	$this->assertEquals(false, $this->courseServices->doesCourseHaveSavedQuizForUser($course, $user));
    }
    
    public function testGetAllQuestionForCourse()
    {
    	$repository = $this->doctrine->getRepository("AppBundle:Course");
    	$course = $repository->find("1");
    	
    	$questions = $this->courseServices->getAllQuestionForCourse($course);
    	
    	$this->assertEquals(2, count($questions));
    }
    
    public function testGetAllQuestionForCourseExpectNull()
    {
    	$repository = $this->doctrine->getRepository("AppBundle:Course");
    	$course = $repository->find("-1");
    	 
    	$questions = $this->courseServices->getAllQuestionForCourse($course);
    	
    	$this->assertNull($questions);
    }
    
    public function testGetSpecifiedQuestions()
    {
    	$repository = $this->doctrine->getRepository("AppBundle:Course");
    	$course = $repository->find("1");
    	
    	$question_list = array(1);
    	 
    	$questions = $this->courseServices->getSpecifiedQuestions($course, $question_list);
    	 
    	$this->assertEquals(1, count($questions));
    }
    
    public function testGetSpecifiedQuestionsExpectNull()
    {
    	$repository = $this->doctrine->getRepository("AppBundle:Course");
    	$course = $repository->find("-1");
    
    	$question_list = array(1000);
    	
    	$questions = $this->courseServices->getSpecifiedQuestions($course, $question_list);
    	 
    	$this->assertNull($questions);
    }
    
    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
    	parent::tearDown();
    
    	$this->doctrine->getManager()->close();
    	$this->doctrine = null; // avoid memory leaks
    	
    	$this->courseServices=null;
    }
}
