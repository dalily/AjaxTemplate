Cakephp ajax bake template
==========================

CakePHP Template for generate Ajax crud, saving you tons of time and having a leaner code base.

# Version notice

* This is an unstable repository and should be treated as an alpha.
* The master and develop branches only works for CakePHP 2.x

# Introduction

AjaxTemplate  was built to generate ajax crud gui in few seconds, it allow developers to have enough flexibility to use it for both rapid prototyping and production applications -- saving you time.

* AjaxTemplate relies on [jQuery](http://jquery.com), [Bootstrap 3.x](http://getbootstrap.com) and [Fuelux repeater](http://getfuelux.com) all dependencies are included, if you already have Jquery or bootstrap on your project you can easly remove theme from the generated view file to get in your way.
* AjaxTemplate will add the necessary actions to your controller no extra code or files will be generated.
* Less boilerplate code means less code to maintain, and less code to spend time unit testing.

# Installation

* Clone the AjaxTemplate plugin into your project plugin directory.
* Load the plugin by editing config/bootstrap.php `CakePlugin::load('AjaxTemplate')`;
* Enable json extensions parse by editing config/routes.php `Router::parseExtensions('json');`;
* Make sure that the RequestHandler component is loaded
```
class AppController extends Controller {
	public $components = array('RequestHandler');
}
```
* Start baking! `cake AjaxTemplate.template` 
* If you've never used the console, here's a great tutorial: [http://book.cakephp.org/2.0/en/console-and-shells/code-generation-with-bake.html](http://book.cakephp.org/2.0/en/console-and-shells/code-generation-with-bake.html)

# Bugs

If you happen to stumble upon a bug, please feel free to create a pull request with a fix
(optionally with a test), and a description of the bug and how it was resolved.

You can also create an issue with a description to raise awareness of the bug.

