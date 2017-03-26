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
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\BrowserKit\Cookie;

class SecurityControllerTest extends WebTestCase
{
	private $client = null;
	
	public function setUp()
	{
		$this->client = static::createClient();
	}
	
	private function logIn()
	{
		$this->client = static::createClient(array(), array(
		    'PHP_AUTH_USER' => 'admin',
		    'PHP_AUTH_PW'   => 'admin',
		));
	}

	public function testLogin()
	{
		$this->logIn();

		$crawler = $this->client->request('GET', '/');

		$this->assertGreaterThan(
				0,
				$crawler->filter('html:contains("admin")')->count()
				);
	}

}
