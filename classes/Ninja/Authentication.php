<?php
// This file handles authentication of users to ensure that only users with valid logins can access specific functionality on a website
// Sessions ensure users only login once to the website, rather than every time they want to make a change to the database

namespace Ninja;

class Authentication {
	private $users;
	private $usernameColumn;
	private $passwordColumn;
	
	// When an Authentication class is created, __construct tells it that 
	// $users is an input and it must be a DatabaseTable, and
	// $usernameColumn is an input that is the name of the column that stores the login names, and
	// $passwordColumn is an input that is the name of the column that stores the passwords
	public function __construct(DatabaseTable $users, $usernameColumn, $passwordColumn) {
		// Look for a session ID or start a new session if none is found
		session_start();
		
		// Set the variables
		$this->users = $users;
		$this->usernameColumn = $usernameColumn;
		$this->passwordColumn = $passwordColumn;
	}
	
	// This method enables users to login
	public function login($username, $password) {
		// use the find method defined in DatabaseTable to find the user in the username column
		// and convert the username to lower case
		$user = $this->users->find($this->usernameColumn, strtolower($username));
		
		// If $user has been set and the password matches the password in the database, begin a new session
		// The password is verfied using a built-in function password_verify, which
		// compares the password with the hashed (encrypted) version in the database
		// $user[0] is necessary because $user is a 2D array (with only 1 row!) and 
		// we need to ask for just the first (and only!) row
		if (!empty($user) && password_verify($password, $user[0]->{$this->passwordColumn})) {
		
			session_regenerate_id();
			
			// Set the username of the session to $username
			$_SESSION['username'] = $username;
			
			// Set the password of the session to the user's $password
			$_SESSION['password'] = $user[0]->{$this->passwordColumn};
			
			// Set the output of this method to true
			return true;
		
		// otherwise, set the output of this method to false
		} else {
			return false;
		}
	}
	
	// This method checks if a user is logged in before they carry out any restriction actions on the database
	public function isLoggedIn() {
		if (empty($_SESSION['username'])) {
			return false;
		}
		
		// use the find function in DatabaseTables to find the username and convert it to lowercase
		$user = $this->users->find($this->usernameColumn, strtolower($_SESSION['username']));
		
		// if their password in the database matches (=== equal to and the same type) the password stored in the session, 
		// return true, otherwise return false
		if (!empty($user) && $user[0]->{$this->passwordColumn} === $_SESSION['password']) {
			return true;
		} else {
			return false;
		}
	}
	
	// This method checks to see if the user is logged in
	// If logged in, it returns an array with the record representing that user
	// [0] is used after the find method to turn the array into a single record of the user
	public function getUser() {
		if ($this->isLoggedin()) {
			return $this->users->find($this->usernameColumn, strtolower($_SESSION['username']))[0];
		
		} else {
			return false;
		}
	}
}