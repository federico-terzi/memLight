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

namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class saves the choosen locale in the session so the user can choose a
 * language.
 * This class is taken from Symfony official documentation
 */
class LocaleListener implements EventSubscriberInterface
{
	private $defaultLocale;

	public function __construct($defaultLocale = 'en')
	{
		$this->defaultLocale = $defaultLocale;
	}

	public function onKernelRequest(GetResponseEvent $event)
	{
		$request = $event->getRequest();
		if (!$request->hasPreviousSession()) {
			return;
		}

		// try to see if the locale has been set as a _locale routing parameter
		if ($locale = $request->attributes->get('_locale')) {
			$request->getSession()->set('_locale', $locale);
		} else {
			// if no explicit locale has been set on this request, use one from the session
			$request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
		}
	}

	public static function getSubscribedEvents()
	{
		return array(
				// must be registered after the default Locale listener
				KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
		);
	}
}