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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to menage semi-static pages like the homepage and the about page.
 * 
 * @author Federico Terzi
 */
class DefaultController extends Controller
{
    /**
     * Render the Home page
     * 
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
    	return $this->render("homepage.html.twig");
    }
    
    /**
     * Render the About page
     * 
     * @Route("/about/", name="about")
     */
    public function aboutAction()
    {
    	return $this->render("about.html.twig");
    }
}
