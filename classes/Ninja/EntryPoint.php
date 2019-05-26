<?php
//This file contains generic code for accessing websites
//This file changes what is displayed on the webpage based on $route, $method and $routes as defined in index.php
//It defines $title and $outut which are used by layout.html.php to display stuff to the webpage
//$routes is an array with all the possible URLs and $route is the actual page the user is on

//namespace is like a folder and gives classes unique names, in case another developed creates an EntryPoint class
namespace Ninja;

class EntryPoint {
	private $route;
	private $method;
	private $routes;
	
	//When an EntryPoint class is created, __construct tells it that 
	//$route is an input and it must be a string, and
	//$method is an input and it must be a string, and
	//$routes is an input and it must be of the type \Ninja\Routes
	public function __construct(string $route, string $method, \Ninja\Routes $routes) {
		$this->route = $route;
		$this->method = $method;
		$this->routes = $routes;
		$this->checkUrl();
	}
	
	//This method checks if the URL is correct
	//If $route is not in lowercase, 301 says this is a permanent redirect and redirects to the lowercase version
	// E.g. if someones visits index.php?action=ListJOKES, they will be redirected to index.php?action=listjokes
	//The permanent redirect is important for search engines not to include erroneous pages in their searches
	//Once redirected to index.php, it eventually comes back into this file, 
	//but passes the lowercase test and so does the callAction method below
	
	private function checkUrl() {
		if ($this->route !== strtolower($this->route)) {
			http_response_code(301);
			header('location: ' . strtolower($this->route));
		}
	}
	
	//This method includes files from the templates folder
	//The file it includes depends of the $templateFileName given
	//It also extracts (stores) variables that can be used in that file
	private function loadTemplate($templateFileName, $variables = []) {
		extract($variables);
		
		//ob_start starts a buffer that gets filled by the include file and then output to the website at the end
		ob_start();

		include __DIR__ . '/../../templates/' . $templateFileName;
		
		return ob_get_clean();
	}

	//This method defines $title and $output dependent on $routes and $authentication
	//$routes is created by getRoutes, which is defined in IjdbRoutes
	//$routes is basically the method (_GET or _POST) and the URL
	//$authentication is created by getAuthentication, with is defined in IjdbRoutes
	public function run() {
		//Define $routes as the output of getRoutes
		$routes = $this->routes->getRoutes();
		
		//Define $authentication as the output of getAuthentication
		$authentication = $this->routes->getAuthentication();
		
		//If login is set, and 
		//it's set to true, and 
		//the user is not logged in, then
		//redirect to the login error page
		
		if (isset($routes[$this->route]['login']) && 
			($routes[$this->route]['login'] == true) &&
			!$authentication->isLoggedIn()) {				
				header('location: /login/error');
				
				//This stops the current code path because this method does not return a template and title, so
				//when it goes back to loadTemplate below, there is nothing to process, which elicits an error
				//The code path has been taken by the header command above anyhow
				die();
		
		//Check for relevant permission of logged in user
		//checkPermission is defined in IjdbRoutes
		} else if (isset($routes[$this->route]['permissions']) && 
			!$this->routes->checkPermission($routes[$this->route]['permissions'])) {
				header('location: /permissions/error');
				die();
			
		//otherwise if the user is logged in, define $controller, $action, $page and $title using the $routes variables
		} else {
			$controller = $routes[$this->route][$this->method]['controller'];
			$action = $routes[$this->route][$this->method]['action'];
			$page = $controller->$action();
			$title = $page['title'];
			
			//If $page has defined variables, 
			//pass them to the loadTemplate function (defined above) along with $page['template']
			//$output is used in layout.html.php and sets what is put in the main body of the web page
			if (isset($page['variables'])) {
				$output = $this->loadTemplate($page['template'], $page['variables']);
			//Otherwise, just pass $page['template'] to loadTemplate	
			} else {
				$output = $this->loadTemplate($page['template']);
			}
			
			//Get the currently logged in user to enable the layout template to check permissions for displaying administer users and categories
			$user = $authentication->getUser();
		}
		
		//This file contains the layout information and uses $title and $output defined above
		//The input 'loggedIn' => $authentication->isLoggedIn() keeps track of whether a user is logged in
		//echo means these outputs are sent to the browser
		echo $this->loadTemplate('layout.html.php', [
			'loggedIn' => $authentication->isLoggedIn(),
			'output' => $output,
			'title' => $title,
			'user' => $user]);
	}
}
