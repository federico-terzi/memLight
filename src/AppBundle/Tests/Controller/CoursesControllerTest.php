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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CoursesControllerTest extends WebTestCase
{
	private function loginCrawler()
	{
		$client = static::createClient();
		
		$crawler = $client->request('GET', '/login');
		
		$form = $crawler->selectButton('Login')->form();
		$form['username'] = 'admin';
		$form['password'] = 'admin';
		
		$crawler = $client->submit($form);
		
		return $crawler;
	}
	
	public function testCoursesPage()
	{
		$client = static::createClient();

		$crawler = $client->request('GET', '/courses/');

		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		
		$this->assertGreaterThan(
				0,
				$crawler->filter('html:contains("Test Course 1")')->count()
				);
		
		$this->assertGreaterThan(
				0,
				$crawler->filter('html:contains("Physics Test Example")')->count()
				);
		
		$this->assertEquals(
				0,
				$crawler->filter('html:contains("This test does not exist!!")')->count()
				);
	}
	
	public function testCoursePage()
	{
		$client = static::createClient();
	
		$crawler = $client->request('GET', '/course/1/');
	
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
	
		$this->assertGreaterThan(
				0,
				$crawler->filter('html:contains("Test Course 1")')->count()
				);
	
		$this->assertGreaterThan(
				0,
				$crawler->filter('html:contains("This is the short description")')->count()
				);
		
		$this->assertGreaterThan(
				0,
				$crawler->filter('html:contains("This is the long description")')->count()
				);
	}
	
	public function testCoursePageNotLoggedIn()
	{
		$client = static::createClient();
	
		$crawler = $client->request('GET', '/course/2/');
	
		$this->assertEquals(
				0,
				$crawler->filter('html:contains("Continue the Quiz")')->count()
				);
	}
	
	public function testCoursePageContainsContinueLink()
	{
		$client = static::createClient(array(), array(
				'PHP_AUTH_USER' => 'admin',
				'PHP_AUTH_PW'   => 'admin',
		));
	
		$crawler = $client->request('GET', '/course/2/');
	
		$this->assertGreaterThan(
				0,
				$crawler->filter('html:contains("Continue the Quiz")')->count()
				);
	}
	
}
