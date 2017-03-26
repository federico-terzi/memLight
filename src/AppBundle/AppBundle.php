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

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
	/**
	 * Types of questions admitted:
	 * 
	 * - "base" is composed only by the question text and the answer text
	 * - "image_answer" must have an image answer
	 * - "custom" does not have any constraint
	 * 
	 * @var array
	 */
	public static $questionTypes = array("Image Answer" =>"image_answer" ,"Base Answer" =>"base", "Custom Answer" => "custom");
}
