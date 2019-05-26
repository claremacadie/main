<?php
// This file creates the interface 'Routes'
// This describes the methods that a class must contain when being created
// This enables type hinting to ensure that classes are input with the correct type of methods 
// Additionally, the getRoute method must return an array, and
// the getAuthentication method must return an Authentication object
namespace Ninja;

interface Routes
{
	public function getRoutes(): array;
	public function getAuthentication(): \Ninja\Authentication;
	public function checkPermission($permission): bool;
	}