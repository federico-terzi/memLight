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
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form used to Add/Edit Questions to a specific Course
 * 
 * @author Federico Terzi
 */
class QuestionType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
		->add('questionType', ChoiceType::class, array('label'=>'Question Type', 'choices' => AppBundle::$questionTypes, 'choices_as_values'=>true))
		->add('chapter', EntityType::class, array('label'=>'Chapter',
            'class' => CourseChapter::class,
            'query_builder' => function (EntityRepository $repo) use ($options) {
                return $repo->createQueryBuilder('c')
                    ->where('c.course = :course')
                    ->setParameter('course', $options["course"]);
            }, 'required'=>false))
		->add('questionText', TextType::class, array('label'=>'Question Text', 'required'=>false))
		->add('questionUrl', FileType::class, array('label'=>'Question URL', 'required'=>false, 'data_class'=>null))
		->add('answerText', TextType::class, array('label'=>'Answer Text', 'required'=>false))
		->add('answerUrl', FileType::class, array('label'=>'Answer Url', 'required'=>false, 'data_class'=>null));
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => Question::class,
		));

		$resolver->setRequired(array('course'));
	}
}