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

namespace AppBundle\Form;

use AppBundle\AppBundle;
use AppBundle\Entity\CourseChapter;
use AppBundle\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form used to Add/Edit Chapters to a specific Course
 * 
 * @author Federico Terzi
 */
class ChapterType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
		->add('name', TextType::class, array('label'=>'Chapter name', 'required'=>false));
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => CourseChapter::class,
		));
	}
}